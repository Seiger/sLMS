<?php

namespace Seiger\sLMS\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Stores a learner's answer selections and final score.
 *
 * @since 0.1.0
 */
class AssessmentAttempt extends Model
{
    protected $table = 's_lms_assessment_attempts';

    protected $fillable = ['web_user_id', 'answers', 'total_questions'];

    protected $casts = [
        'web_user_id' => 'integer',
        'answers' => 'array',
        'total_questions' => 'integer',
        'score' => 'integer',
        'score_percent' => 'integer',
        'is_successful' => 'boolean',
        'submitted_at' => 'immutable_datetime',
    ];

    /**
     * Get the frozen test definition used to grade this attempt.
     *
     * @return BelongsTo<AssessmentVersion, $this>
     * @since 0.1.0
     */
    public function version(): BelongsTo
    {
        return $this->belongsTo(AssessmentVersion::class, 'version_id');
    }
}
