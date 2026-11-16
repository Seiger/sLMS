<?php

namespace Seiger\sLMS\Tables;

use EvoUI\Contracts\ModuleTableProvider;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Seiger\sLMS\Models\Course;
use Seiger\sLMS\Models\Lesson;
use Seiger\sLMS\Models\Module;
use Seiger\sLMS\Support\ManagerAccess;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Supply Evo UI with the ordered modules of a course or lessons of a module.
 *
 * @since 0.1.0
 */
class CurriculumTableData implements ModuleTableProvider
{
    private const MAX_POSITION = 2147483647;

    private bool $lessons;

    /**
     * Bind the provider to the parent selected in the catalogue panel.
     *
     * @param array<string, mixed> $context
     * @param array<string, mixed> $config
     * @since 0.1.0
     */
    public function __construct(private array $context, array $config)
    {
        $this->lessons = $config['key'] === 'slms.lessons';
    }

    /**
     * Count the records belonging to the current course or module.
     *
     * @since 0.1.0
     */
    public function total(): int
    {
        return $this->records($this->parent())->count();
    }

    /**
     * Read one page in learning order, without selecting lesson bodies.
     *
     * @return list<array<string, mixed>>
     * @since 0.1.0
     */
    public function rows(int $page, int $perPage): array
    {
        return $this->records($this->parent())
            ->forPage(max(1, $page), min(100, max(1, $perPage)))
            ->get(['id', 'title', 'slug', 'published', 'position'])
            ->map(static fn (Module|Lesson $record): array => [
                'id' => (int) $record->getKey(),
                'title' => $record->title,
                'slug' => $record->slug,
                'position' => $record->position,
                'status' => [
                    'label' => __('sLMS::global.courses.' . ($record->published ? 'published' : 'draft')),
                    'color' => $record->published ? '#16a34a' : null,
                ],
            ])
            ->all();
    }

    /**
     * Keep the ordered table unfiltered so adjacent rows belong to the same list.
     *
     * @since 0.1.0
     */
    public function filterGroups(): array
    {
        return [];
    }

    /**
     * Show positions as text to managers who have read-only access.
     *
     * @param list<array<string, mixed>> $columns
     * @return list<array<string, mixed>>
     * @since 0.1.0
     */
    public function columns(array $columns): array
    {
        if (!ManagerAccess::allows(write: true)) {
            foreach ($columns as &$column) {
                if ($column['key'] === 'position') {
                    $column['type'] = 'text';
                }
            }
        }

        return $columns;
    }

    /**
     * Disable creation for managers who have read-only access.
     *
     * @return array{disabled: bool}
     * @since 0.1.0
     */
    public function managementAttributes(): array
    {
        return ['disabled' => !ManagerAccess::allows(write: true)];
    }

    /**
     * Keep lesson navigation available even without write access.
     *
     * @param list<array<string, mixed>> $actions
     * @return list<array<string, mixed>>
     * @since 0.1.0
     */
    public function rowActions(array $actions): array
    {
        return ManagerAccess::allows(write: true)
            ? $actions
            : array_values(array_filter($actions, static fn (array $action): bool => $action['key'] === 'lessons'));
    }

    /**
     * Supply blank fields for a new draft module or lesson.
     *
     * @return array<string, mixed>
     * @since 0.1.0
     */
    public function modalDefaults(): array
    {
        ManagerAccess::authorize(write: true);

        return ['title' => '', 'slug' => '', $this->bodyField() => '', 'published' => false];
    }

    /**
     * Look up the record within its current parent and load its editable fields.
     *
     * @return array<string, mixed>
     * @since 0.1.0
     */
    public function modalData(int $id): array
    {
        ManagerAccess::authorize(write: true);

        return $this->records($this->parent())->findOrFail($id)->only(['title', 'slug', $this->bodyField(), 'published']);
    }

    /**
     * Save validated data and append new records to the end of the current list.
     *
     * @param array<string, mixed> $data
     * @since 0.1.0
     */
    public function saveModal(array $data, ?int $id = null, string $mode = 'create'): int
    {
        if (!(($mode === 'create' && $id === null) || ($mode === 'edit' && $id > 0))) {
            throw new HttpException(422);
        }

        return $this->withLockedParent(function (HasMany $records) use ($data, $id): int {
            $record = $id === null ? $records->make() : $records->findOrFail($id);
            $attributes = $this->validate($data, $record, $records);

            if ($id === null) {
                $position = (int) (clone $records)->reorder()->max('position');
                if ($position >= self::MAX_POSITION) {
                    $rows = $records->get(['id', 'position'])->keyBy('id');
                    $this->savePositions($rows->keys()->all(), $rows->all());
                    $position = $rows->count();
                }
                $record->position = $position + 1;
            }

            try {
                $record->fill($attributes)->save();
            } catch (UniqueConstraintViolationException) {
                throw ValidationException::withMessages(['modalData.slug' => __('sLMS::global.curriculum.slug_taken')]);
            }

            return (int) $record->getKey();
        });
    }

    /**
     * Move one record by a single position in its own list.
     *
     * @since 0.1.0
     */
    public function moveRow(int $id, string $direction): void
    {
        if (!in_array($direction, ['up', 'down'], true)) {
            throw new HttpException(422);
        }
        $this->changeOrder($id, null, $direction === 'down');
    }

    /**
     * Place a record next to another record belonging to the same parent.
     *
     * @since 0.1.0
     */
    public function reorderRow(int $id, int $targetId, string $placement): void
    {
        if (!in_array($placement, ['before', 'after'], true)) {
            throw new HttpException(422);
        }
        if ($id !== $targetId) {
            $this->changeOrder($id, $targetId, $placement === 'after');
        }
    }

    /**
     * Resolve the current parent for an authorized manager.
     *
     * @since 0.1.0
     */
    private function parent(): Course|Module
    {
        ManagerAccess::authorize();
        $parent = $this->lessons ? Module::class : Course::class;

        return $parent::query()->findOrFail($this->context['parent_id'] ?? 0);
    }

    /**
     * Use the same position and ID ordering as the public model relationships.
     *
     * @since 0.1.0
     */
    private function records(Course|Module $parent): HasMany
    {
        return $parent instanceof Course ? $parent->modules() : $parent->lessons();
    }

    /**
     * Serialize changes within a parent and retry transaction deadlocks.
     *
     * @template TResult
     * @param callable(HasMany): TResult $operation
     * @return TResult
     * @since 0.1.0
     */
    private function withLockedParent(callable $operation): mixed
    {
        ManagerAccess::authorize(write: true);
        $parent = $this->lessons ? Module::class : Course::class;
        $query = $parent::query();

        return $query->getModel()->getConnection()->transaction(function () use ($query, $operation): mixed {
            $parent = (clone $query)->lockForUpdate()->findOrFail($this->context['parent_id'] ?? 0);

            return $operation($this->records($parent));
        }, 3);
    }

    /**
     * Validate the Evo UI form, including slug uniqueness within its parent.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     * @since 0.1.0
     */
    private function validate(array $data, Module|Lesson $record, HasMany $records): array
    {
        $body = $this->bodyField();
        foreach (['title', 'slug'] as $field) {
            if (is_string($data[$field] ?? null)) {
                $data[$field] = trim($data[$field]);
            }
        }

        if (($data['slug'] ?? '') === '' && is_string($data['title'] ?? null)) {
            $data['slug'] = $record->exists ? $record->slug : rtrim(substr(Str::slug($data['title']), 0, 191), '-');
        }

        $slug = Rule::unique($record::class, 'slug')->where($records->getForeignKeyName(), $records->getParent()->getKey());
        if ($record->exists) {
            $slug->ignore($record);
        }

        return Validator::make(['modalData' => $data], [
            'modalData.title' => ['required', 'string', 'max:255'],
            'modalData.slug' => ['required', 'string', 'max:191', 'regex:/\A[a-z0-9]+(?:-[a-z0-9]+)*\z/', $slug],
            'modalData.' . $body => $this->lessons ? ['nullable', 'string'] : ['nullable', 'string', 'max:16000'],
            'modalData.published' => ['required', 'boolean'],
        ], [
            'modalData.slug.regex' => __('sLMS::global.courses.slug_help'),
            'modalData.slug.unique' => __('sLMS::global.curriculum.slug_taken'),
        ], [
            'modalData.title' => __('sLMS::global.courses.title'),
            'modalData.slug' => __('sLMS::global.courses.slug'),
            'modalData.' . $body => __($this->lessons ? 'sLMS::global.curriculum.content' : 'sLMS::global.courses.description'),
            'modalData.published' => __('sLMS::global.courses.published'),
        ])->validate()['modalData'];
    }

    /**
     * Identify the editable body field for the table's record type.
     *
     * @since 0.1.0
     */
    private function bodyField(): string
    {
        return $this->lessons ? 'content' : 'description';
    }

    /**
     * Reorder the complete sibling list, including records on other pages.
     *
     * @since 0.1.0
     */
    private function changeOrder(int $id, ?int $targetId, bool $after): void
    {
        $this->withLockedParent(function (HasMany $records) use ($id, $targetId, $after): void {
            $rows = $records->get(['id', 'position'])->keyBy('id');
            $ids = $rows->keys()->all();
            $index = array_search($id, $ids, true);
            if ($index === false) {
                throw new NotFoundHttpException();
            }
            array_splice($ids, $index, 1);

            if ($targetId === null) {
                $destination = $index + ($after ? 1 : -1);
            } else {
                $target = array_search($targetId, $ids, true);
                if ($target === false) {
                    throw new NotFoundHttpException();
                }
                $destination = $target + (int) $after;
            }

            if ($destination < 0 || $destination > count($ids) || $destination === $index) {
                return;
            }

            array_splice($ids, $destination, 0, [$id]);
            $this->savePositions($ids, $rows->all());
        });
    }

    /**
     * Persist contiguous positions and leave unchanged records untouched.
     *
     * @param list<int> $ids
     * @param array<int, Module|Lesson> $rows
     * @since 0.1.0
     */
    private function savePositions(array $ids, array $rows): void
    {
        foreach ($ids as $index => $id) {
            $record = $rows[$id];
            $record->position = $index + 1;
            if ($record->isDirty('position')) {
                $record->save();
            }
        }
    }
}
