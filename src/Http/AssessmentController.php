<?php

namespace Seiger\sLMS\Http;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Seiger\sLMS\Models\AssessmentAttempt;
use Seiger\sLMS\Services\AssessmentAttempts;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Render the learner test and handle attempt form submissions.
 *
 * @since 0.1.0
 */
class AssessmentController
{
    /**
     * Use the same attempt workflow for all learner routes.
     *
     * @since 0.1.0
     */
    public function __construct(private AssessmentAttempts $attempts)
    {
    }

    /**
     * Show the module test's start page without creating an attempt.
     *
     * @since 0.1.0
     */
    public function entry(Request $request, string $module): Response
    {
        $record = $this->attempts->module($this->id($module), $request->attributes->getInt('slmsLearnerId'));

        return response(view('sLMS::learner.assessment', [
            'moduleId' => $record->getKey(),
            'moduleTitle' => $record->title,
            'attemptId' => null,
            'available' => $record->assessment()->whereHas('questions')->exists(),
        ])->render());
    }

    /**
     * Start or resume an attempt and redirect to the learner's form.
     *
     * @since 0.1.0
     */
    public function start(Request $request, string $module): RedirectResponse
    {
        $attempt = $this->attempts->start($this->id($module), $request->attributes->getInt('slmsLearnerId'));

        return redirect()->route('slms.assessment.attempt', ['attempt' => $attempt->getKey()], 303);
    }

    /**
     * Show the saved selections or the final score to their owner.
     *
     * @since 0.1.0
     */
    public function show(Request $request, string $attempt): Response
    {
        $record = $this->attempts->find($this->id($attempt), $request->attributes->getInt('slmsLearnerId'));

        return $this->attemptPage($record, saved: $request->query('saved') === '1');
    }

    /**
     * Save a draft from the learner form.
     *
     * @since 0.1.0
     */
    public function save(Request $request, string $attempt): Response
    {
        return $this->update($request, $this->id($attempt), submit: false);
    }

    /**
     * Submit learner selections for scoring.
     *
     * @since 0.1.0
     */
    public function submit(Request $request, string $attempt): Response
    {
        return $this->update($request, $this->id($attempt), submit: true);
    }

    /**
     * Validate form completion and return the result or a field error.
     *
     * @since 0.1.0
     */
    private function update(Request $request, int $attemptId, bool $submit): Response
    {
        $userId = $request->attributes->getInt('slmsLearnerId');
        $answers = $request->post('answers', []);
        try {
            // The final field guards against partial submissions cut off by PHP input limits.
            if ($request->post('answers_present') !== '1' || !is_array($answers)) {
                throw ValidationException::withMessages(['answers' => __('sLMS::global.attempt.invalid_answers')]);
            }
            $attempt = $this->attempts->save($attemptId, $userId, $answers, $submit);
        } catch (ValidationException $exception) {
            $attempt = $this->attempts->find($attemptId, $userId);

            return $this->attemptPage($attempt, error: $exception->getMessage());
        }

        $parameters = ['attempt' => $attemptId];
        if ($attempt->submitted_at === null) {
            $parameters['saved'] = 1;
        }

        return redirect()->route('slms.assessment.attempt', $parameters, 303);
    }

    /**
     * Render learner-safe question data and a summary of the stored score.
     *
     * @since 0.1.0
     */
    private function attemptPage(AssessmentAttempt $attempt, bool $saved = false, ?string $error = null): Response
    {
        $version = $attempt->version;
        $module = $version->assessment->module;
        $submitted = $attempt->submitted_at !== null;

        return response(view('sLMS::learner.assessment', [
            'moduleId' => $module->getKey(),
            'moduleTitle' => $module->title,
            'attemptId' => $attempt->getKey(),
            'submitted' => $submitted,
            'questions' => $submitted ? [] : $this->attempts->learnerQuestions($attempt),
            'answers' => $submitted ? [] : $attempt->answers,
            'score' => $attempt->score,
            'total' => $attempt->total_questions,
            'percent' => $attempt->score_percent,
            'threshold' => $version->pass_percent,
            'successful' => $attempt->is_successful,
            'saved' => $saved,
            'error' => $error,
        ])->render(), $error === null ? 200 : 422);
    }

    /**
     * Reject IDs outside the supported integer range before looking up records.
     *
     * @since 0.1.0
     */
    private function id(string $value): int
    {
        $id = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($id === false) {
            throw new NotFoundHttpException();
        }

        return $id;
    }
}
