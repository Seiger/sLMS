<?php

namespace Seiger\sLMS\Tables;

use EvoUI\Contracts\ModuleTableProvider;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Seiger\sLMS\Models\AssessmentOption;
use Seiger\sLMS\Models\AssessmentQuestion;
use Seiger\sLMS\Models\Module;
use Seiger\sLMS\Support\ManagerAccess;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Edit questions and answer options in the current module's assessment.
 *
 * @since 0.1.0
 */
class AssessmentTableData implements ModuleTableProvider
{
    /**
     * Receive the module context from the Evo UI table.
     *
     * @param array{module: int} $context
     * @since 0.1.0
     */
    public function __construct(private array $context)
    {
    }

    /**
     * Count questions in the current module.
     *
     * @since 0.1.0
     */
    public function total(): int
    {
        return $this->questions()->count();
    }

    /**
     * List questions and option counts in their stored order.
     *
     * @return list<array{id: int, position: int, prompt: string, options_count: int}>
     * @since 0.1.0
     */
    public function rows(int $page, int $perPage): array
    {
        return $this->questions()
            ->select(['id', 'position', 'prompt'])
            ->withCount('options')
            ->orderBy('position')
            ->orderBy('id')
            ->forPage(max(1, $page), min(100, max(1, $perPage)))
            ->get()
            ->map(static fn (AssessmentQuestion $question): array => [
                'id' => (int) $question->getKey(),
                'position' => $question->position,
                'prompt' => $question->prompt,
                'options_count' => (int) $question->options_count,
            ])
            ->all();
    }

    /**
     * Show all questions from the current assessment without additional filters.
     *
     * @return list<array<string, mixed>>
     * @since 0.1.0
     */
    public function filterGroups(): array
    {
        return [];
    }

    /**
     * Disable the creation button for managers with read-only access.
     *
     * @return array{disabled: bool}
     * @since 0.1.0
     */
    public function managementAttributes(): array
    {
        return ['disabled' => !ManagerAccess::allows(write: true)];
    }

    /**
     * Only managers with write access can open the editor and see the answer key.
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
     * Start a question with two blank answer options.
     *
     * @return array{prompt: string, options: list<array{text: string, is_correct: bool}>}
     * @since 0.1.0
     */
    public function modalDefaults(): array
    {
        ManagerAccess::authorize(write: true);
        $option = ['text' => '', 'is_correct' => false];

        return ['prompt' => '', 'options' => [$option, $option]];
    }

    /**
     * Load the question, revision and option IDs needed to edit the answer key.
     *
     * @return array<string, mixed>
     * @since 0.1.0
     */
    public function modalData(int $id): array
    {
        ManagerAccess::authorize(write: true);
        $question = $this->questions()->with('options')->findOrFail($id);

        return [
            'prompt' => $question->prompt,
            'revision' => $question->revision,
            'options' => $question->options
                ->map(static fn (AssessmentOption $option): array => $option->only(['id', 'text', 'is_correct']))
                ->all(),
        ];
    }

    /**
     * Save the prompt and the entire answer list in one transaction.
     *
     * @param array<string, mixed> $data
     * @since 0.1.0
     */
    public function saveModal(array $data, ?int $id = null, string $mode = 'create'): int
    {
        ManagerAccess::authorize(write: true);
        if (!(($mode === 'create' && $id === null) || ($mode === 'edit' && $id > 0))) {
            throw new HttpException(422);
        }

        $validated = $this->validate($data, $id !== null);
        $modules = Module::query();

        return $modules->getModel()->getConnection()->transaction(function () use ($modules, $id, $validated): int {
            $module = (clone $modules)->lockForUpdate()->findOrFail($this->context['module']);
            $question = $id === null ? null : $this->questions()->findOrFail($id);
            if ($question && $question->revision !== (int) $validated['revision']) {
                throw ValidationException::withMessages(['modalData.prompt' => __('sLMS::global.assessment.stale')]);
            }

            $existingOptions = $question?->options()->get()->keyBy('id') ?? collect();
            $options = $validated['options'];
            $existingIds = array_map('intval', array_filter(array_column($options, 'id'), static fn ($id) => $id !== null));
            if (count($existingIds) !== count(array_unique($existingIds)) || array_diff($existingIds, $existingOptions->keys()->all())) {
                throw ValidationException::withMessages(['modalData.options' => __('sLMS::global.assessment.invalid_options')]);
            }

            if ($question === null) {
                $assessment = $module->assessment()->firstOrCreate();
                $position = (int) $assessment->questions()->reorder()->max('position') + 1;
                $question = $assessment->questions()->create(['prompt' => trim($validated['prompt']), 'position' => $position]);
            } else {
                $question->prompt = trim($validated['prompt']);
                $question->revision++;
                $question->save();
            }

            $retained = [];
            foreach ($options as $index => $fields) {
                $option = isset($fields['id']) ? $existingOptions->get((int) $fields['id']) : $question->options()->make();
                $option->fill([
                    'text' => trim($fields['text']),
                    'is_correct' => (bool) $fields['is_correct'],
                    'position' => $index + 1,
                ])->save();
                $retained[] = $option->getKey();
            }
            $question->options()->reorder()->whereNotIn('id', $retained)->delete();

            return (int) $question->getKey();
        }, 3);
    }

    /**
     * Resolve questions within the current module only.
     *
     * @return Builder<AssessmentQuestion>
     * @since 0.1.0
     */
    private function questions(): Builder
    {
        ManagerAccess::authorize();

        return AssessmentQuestion::query()->whereHas('assessment', fn (Builder $query) => $query
            ->where('module_id', $this->context['module']));
    }

    /**
     * Require a prompt, two answers and at least one correct answer.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     * @since 0.1.0
     */
    private function validate(array $data, bool $editing): array
    {
        $validated = Validator::make(['modalData' => $data], [
            'modalData.prompt' => ['required', 'string', 'max:16000'],
            'modalData.revision' => $editing ? ['required', 'integer', 'min:1'] : ['exclude'],
            'modalData.options' => ['required', 'array', 'list', 'min:2'],
            'modalData.options.*' => ['array:id,text,is_correct'],
            'modalData.options.*.id' => ['nullable', 'integer', 'min:1'],
            'modalData.options.*.text' => ['required', 'string', 'max:4000'],
            'modalData.options.*.is_correct' => ['required', 'boolean'],
        ], [
            'modalData.options.required' => __('sLMS::global.assessment.options_min'),
            'modalData.options.min' => __('sLMS::global.assessment.options_min'),
        ], [
            'modalData.prompt' => __('sLMS::global.assessment.prompt'),
            'modalData.options' => __('sLMS::global.assessment.options'),
            'modalData.options.*.text' => __('sLMS::global.assessment.option_text'),
            'modalData.options.*.is_correct' => __('sLMS::global.assessment.correct'),
        ])->validate()['modalData'];

        if (!in_array(true, array_map('boolval', array_column($validated['options'], 'is_correct')), true)) {
            throw ValidationException::withMessages(['modalData.options' => __('sLMS::global.assessment.correct_required')]);
        }

        return $validated;
    }
}
