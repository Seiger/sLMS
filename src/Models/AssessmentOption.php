<?php

namespace Seiger\sLMS\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * An answer option and its correctness flag in the editable assessment.
 *
 * @since 0.1.0
 */
class AssessmentOption extends Model
{
    protected $table = 's_lms_assessment_options';

    protected $fillable = ['text', 'is_correct', 'position'];

    protected $casts = ['is_correct' => 'boolean', 'position' => 'integer'];

    /**
     * Get the question to which the option belongs.
     *
     * @return BelongsTo<AssessmentQuestion, $this>
     * @since 0.1.0
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(AssessmentQuestion::class, 'question_id');
    }
}
