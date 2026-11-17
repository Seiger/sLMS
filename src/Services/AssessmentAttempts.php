<?php

namespace Seiger\sLMS\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;
use Seiger\sLMS\Models\AssessmentAttempt;
use Seiger\sLMS\Models\AssessmentOption;
use Seiger\sLMS\Models\AssessmentQuestion;
use Seiger\sLMS\Models\Module;
use Seiger\sLMS\Support\AssessmentAccess;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Start, save and grade attempts against a frozen assessment definition.
 *
 * @since 0.1.0
 */
class AssessmentAttempts
{
    /**
     * Use the site's assessment access policy on each operation.
     *
     * @since 0.1.0
     */
    public function __construct(private AssessmentAccess $access)
    {
    }

    /**
     * Get the published module available to this learner.
     *
     * @since 0.1.0
     */
    public function module(int $moduleId, int $webUserId): Module
    {
        $module = Module::query()->with('course')->findOrFail($moduleId);
        $this->access->authorize($module, $webUserId);

        return $module;
    }

    /**
     * Resume the learner's open attempt or capture the current assessment.
     *
     * @since 0.1.0
     */
    public function start(int $moduleId, int $webUserId): AssessmentAttempt
    {
        $modules = Module::query();

        return $modules->getModel()->getConnection()->transaction(function () use ($modules, $moduleId, $webUserId): AssessmentAttempt {
            // The manager's assessment editor takes the same module lock.
            $module = (clone $modules)->with('course')->lockForUpdate()->findOrFail($moduleId);
            $this->access->authorize($module, $webUserId);
            $assessment = $module->assessment()->firstOrFail();

            $open = AssessmentAttempt::query()
                ->where('web_user_id', $webUserId)
                ->whereNull('submitted_at')
                ->whereHas('version', fn (Builder $query) => $query->where('assessment_id', $assessment->getKey()))
                ->latest('id')
                ->first();
            if ($open !== null) {
                return $open;
            }

            $definition = $assessment->questions()->with('options')->get()
                ->map(static fn (AssessmentQuestion $question): array => [
                    'id' => (int) $question->getKey(),
                    'prompt' => $question->prompt,
                    'options' => $question->options->map(static fn (AssessmentOption $option): array => [
                        'id' => (int) $option->getKey(),
                        'text' => $option->text,
                        'is_correct' => $option->is_correct,
                    ])->all(),
                ])
                ->all();

            if ($definition === []) {
                throw new HttpException(404);
            }
            foreach ($definition as $question) {
                if (count($question['options']) < 2 || !in_array(true, array_column($question['options'], 'is_correct'), true)) {
                    throw new HttpException(409, __('sLMS::global.attempt.unavailable'));
                }
            }

            $configuredPercent = config('slms.attempts.pass_percent', 70);
            $passPercent = is_int($configuredPercent) || is_string($configuredPercent) ? filter_var($configuredPercent, FILTER_VALIDATE_INT, [
                'options' => ['min_range' => 0, 'max_range' => 100],
            ]) : false;
            if ($passPercent === false) {
                throw new \InvalidArgumentException('slms.attempts.pass_percent must be an integer between 0 and 100.');
            }

            $fingerprint = hash('sha256', json_encode([$passPercent, $definition], JSON_THROW_ON_ERROR));
            $version = $assessment->versions()->firstOrCreate(['fingerprint' => $fingerprint], [
                'definition' => $definition,
                'pass_percent' => $passPercent,
            ]);

            return $version->attempts()->create([
                'web_user_id' => $webUserId,
                'answers' => [],
                'total_questions' => count($definition),
            ]);
        }, 3);
    }

    /**
     * Fetch an attempt owned by a learner who still has access to the module.
     *
     * @since 0.1.0
     */
    public function find(int $attemptId, int $webUserId): AssessmentAttempt
    {
        return $this->load($attemptId, $webUserId);
    }

    /**
     * Replace draft selections or grade and close the attempt once.
     *
     * @param array<int|string, mixed> $answers
     * @since 0.1.0
     */
    public function save(int $attemptId, int $webUserId, array $answers, bool $submit = false): AssessmentAttempt
    {
        return (new AssessmentAttempt())->getConnection()->transaction(function () use ($attemptId, $webUserId, $answers, $submit): AssessmentAttempt {
            $attempt = $this->load($attemptId, $webUserId, lock: true);
            if ($attempt->submitted_at !== null) {
                return $attempt;
            }

            $version = $attempt->version;
            $definition = $version->definition;
            $selections = $this->normalize($answers, $definition);
            $attempt->answers = $selections;

            if ($submit) {
                $score = 0;
                foreach ($definition as $question) {
                    $correct = array_column(array_filter($question['options'], static fn (array $option): bool => $option['is_correct']), 'id');
                    sort($correct, SORT_NUMERIC);
                    $score += (int) (($selections[$question['id']] ?? []) === $correct);
                }
                $total = $attempt->total_questions;
                $attempt->score = $score;
                $attempt->score_percent = intdiv($score * 100 + $total - 1, $total);
                $attempt->is_successful = $attempt->score_percent >= $version->pass_percent;
                $attempt->submitted_at = now();
            }

            $attempt->save();

            return $attempt;
        }, 3);
    }

    /**
     * Project the frozen questions for the learner without correctness flags.
     *
     * @return list<array{id: int, prompt: string, options: list<array{id: int, text: string}>}>
     * @since 0.1.0
     */
    public function learnerQuestions(AssessmentAttempt $attempt): array
    {
        return array_map(static fn (array $question): array => [
            'id' => $question['id'],
            'prompt' => $question['prompt'],
            'options' => array_map(static fn (array $option): array => [
                'id' => $option['id'],
                'text' => $option['text'],
            ], $question['options']),
        ], $attempt->version->definition);
    }

    /**
     * Scope the lookup to its owner and optionally lock it during a write.
     *
     * @since 0.1.0
     */
    private function load(int $attemptId, int $webUserId, bool $lock = false): AssessmentAttempt
    {
        $attempt = AssessmentAttempt::query()
            ->with('version.assessment.module.course')
            ->where('web_user_id', $webUserId)
            ->when($lock, fn (Builder $query) => $query->lockForUpdate())
            ->findOrFail($attemptId);
        $this->access->authorize($attempt->version->assessment->module, $webUserId);

        return $attempt;
    }

    /**
     * Validate question and option membership and canonicalize ID sets.
     *
     * @param array<int|string, mixed> $answers
     * @param list<array<string, mixed>> $definition
     * @return array<int, list<int>>
     * @since 0.1.0
     */
    private function normalize(array $answers, array $definition): array
    {
        $questions = array_column($definition, 'options', 'id');
        $normalized = [];
        foreach ($answers as $questionId => $selections) {
            if (!isset($questions[$questionId]) || !is_array($selections) || !array_is_list($selections)) {
                throw ValidationException::withMessages(['answers' => __('sLMS::global.attempt.invalid_answers')]);
            }

            $allowed = array_fill_keys(array_column($questions[$questionId], 'id'), true);
            $selected = [];
            foreach ($selections as $value) {
                $id = is_int($value) || is_string($value) ? filter_var($value, FILTER_VALIDATE_INT) : false;
                if ($id === false || !isset($allowed[$id]) || isset($selected[$id])) {
                    throw ValidationException::withMessages(['answers' => __('sLMS::global.attempt.invalid_answers')]);
                }
                $selected[$id] = true;
            }

            if ($selected !== []) {
                $ids = array_keys($selected);
                sort($ids, SORT_NUMERIC);
                $normalized[$questionId] = $ids;
            }
        }

        ksort($normalized, SORT_NUMERIC);

        return $normalized;
    }
}
