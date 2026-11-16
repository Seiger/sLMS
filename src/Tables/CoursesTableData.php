<?php

namespace Seiger\sLMS\Tables;

use EvolutionCMS\Models\SiteContent;
use EvoUI\Contracts\ModuleTableProvider;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Seiger\sLMS\Models\Course;
use Seiger\sLMS\Support\ManagerAccess;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Supplies course rows and saves the shared Evo UI create and edit form.
 *
 * @since 0.1.0
 */
class CoursesTableData implements ModuleTableProvider
{
    /**
     * Receive the current search and sort state from the Evo UI table.
     *
     * @param array<string, mixed> $state
     * @since 0.1.0
     */
    public function __construct(protected array $state = [])
    {
    }

    /**
     * Count the courses that match the manager's search.
     *
     * @since 0.1.0
     */
    public function total(): int
    {
        ManagerAccess::authorize();

        return $this->query()->count();
    }

    /**
     * Return one ordered page of courses with the data needed by Evo UI.
     *
     * @return list<array<string, mixed>>
     * @since 0.1.0
     */
    public function rows(int $page, int $perPage): array
    {
        ManagerAccess::authorize();

        $sort = match ($this->state['sort'] ?? null) {
            'title' => 'title',
            'slug' => 'slug',
            'status' => 'published',
            default => 'id',
        };
        $direction = ($this->state['direction'] ?? null) === 'asc' ? 'asc' : 'desc';

        return $this->query()
            ->orderBy($sort, $direction)
            ->when($sort !== 'id', fn (Builder $query) => $query->orderBy('id'))
            ->forPage(max(1, $page), min(100, max(1, $perPage)))
            ->get(['id', 'title', 'slug', 'published'])
            ->map(static fn (Course $course): array => [
                'id' => (int) $course->getKey(),
                'title' => $course->title,
                'slug' => $course->slug,
                'status' => [
                    'label' => __("sLMS::global.courses." . ($course->published ? 'published' : 'draft')),
                    'color' => $course->published ? '#16a34a' : null,
                ],
            ])
            ->all();
    }

    /**
     * Use the publication filter options defined in the Evo UI preset.
     *
     * @return list<array<string, mixed>>
     * @since 0.1.0
     */
    public function filterGroups(): array
    {
        return [];
    }

    /**
     * Supply the starting values for a new draft course.
     *
     * @return array{title: string, slug: string, description: string, resource_id: null, published: false}
     * @since 0.1.0
     */
    public function modalDefaults(): array
    {
        ManagerAccess::authorize(write: true);

        return ['title' => '', 'slug' => '', 'description' => '', 'resource_id' => null, 'published' => false];
    }

    /**
     * Load the saved fields into the editor for the requested course.
     *
     * @return array<string, mixed>
     * @since 0.1.0
     */
    public function modalData(int $id): array
    {
        ManagerAccess::authorize(write: true);

        return Course::query()->findOrFail($id)->only(['title', 'slug', 'description', 'resource_id', 'published']);
    }

    /**
     * Disable toolbar write actions for managers who can only read courses.
     *
     * @return array{disabled: bool}
     * @since 0.1.0
     */
    public function managementAttributes(): array
    {
        return ['disabled' => !ManagerAccess::allows(write: true)];
    }

    /**
     * Display the edit action only to managers with the write permission.
     *
     * @param list<array<string, mixed>> $actions
     * @return list<array<string, mixed>>
     * @since 0.1.0
     */
    public function rowActions(array $actions): array
    {
        return ManagerAccess::allows(write: true) ? $actions : [];
    }

    /**
     * Validate the submitted fields and create or update one course.
     *
     * Validation errors use Evo UI's modal field keys so the editor stays open.
     *
     * @param array<string, mixed> $data
     * @since 0.1.0
     */
    public function saveModal(array $data, ?int $recordId = null, string $mode = 'create'): int
    {
        ManagerAccess::authorize(write: true);
        if (!(($mode === 'create' && $recordId === null) || ($mode === 'edit' && $recordId > 0))) {
            throw new HttpException(422);
        }

        $course = $mode === 'edit' ? Course::query()->findOrFail($recordId) : new Course();
        foreach (['title', 'slug', 'description', 'resource_id'] as $field) {
            if (is_string($data[$field] ?? null)) {
                $data[$field] = trim($data[$field]);
                if (in_array($field, ['description', 'resource_id'], true) && $data[$field] === '') {
                    $data[$field] = null;
                }
            }
        }

        if (($data['slug'] ?? '') === '' && is_string($data['title'] ?? null)) {
            $data['slug'] = $course->exists ? $course->slug : rtrim(substr(Str::slug($data['title']), 0, 191), '-');
        }

        $slug = Rule::unique($course->getTable(), 'slug');
        if ($course->exists) {
            $slug->ignore($course);
        }

        $attributes = Validator::make(['modalData' => $data], [
            'modalData.title' => ['required', 'string', 'max:255'],
            'modalData.slug' => ['required', 'string', 'max:191', 'regex:/\A[a-z0-9]+(?:-[a-z0-9]+)*\z/', $slug],
            'modalData.description' => ['nullable', 'string', 'max:16000'],
            'modalData.resource_id' => [
                'nullable', 'integer', 'min:1', Rule::exists((new SiteContent())->getTable(), 'id')->where('deleted', 0),
            ],
            'modalData.published' => ['required', 'boolean'],
        ], [
            'modalData.slug.regex' => __('sLMS::global.courses.slug_help'),
            'modalData.slug.unique' => __('sLMS::global.courses.slug_taken'),
            'modalData.resource_id.exists' => __('sLMS::global.courses.resource_missing'),
        ], [
            'modalData.title' => __('sLMS::global.courses.title'),
            'modalData.slug' => __('sLMS::global.courses.slug'),
            'modalData.description' => __('sLMS::global.courses.description'),
            'modalData.resource_id' => __('sLMS::global.courses.resource'),
            'modalData.published' => __('sLMS::global.courses.published'),
        ])->validate()['modalData'];

        try {
            $course->fill($attributes)->save();
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['modalData.slug' => __('sLMS::global.courses.slug_taken')]);
        }

        return (int) $course->getKey();
    }

    /**
     * Apply the same search and publication filter to counts and rows.
     *
     * @return Builder<Course>
     * @since 0.1.0
     */
    protected function query(): Builder
    {
        $search = mb_substr(trim((string) ($this->state['search'] ?? '')), 0, 255);
        $publication = $this->state['filters']['availability'] ?? 'all';

        return Course::query()
            ->when(in_array($publication, ['published', 'draft'], true), fn (Builder $query) => $query
                ->where('published', $publication === 'published'))
            ->when($search !== '', fn (Builder $query) => $query->where(fn (Builder $query) => $query
                ->where('title', 'like', '%' . $search . '%')
                ->orWhere('slug', 'like', '%' . $search . '%')));
    }
}
