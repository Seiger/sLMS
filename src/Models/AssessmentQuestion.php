<?php

namespace Seiger\sLMS\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * An ordered question in a module's editable assessment.
 *
 * @since 0.1.0
 */
class AssessmentQuestion extends Model
{
    protected $table = 's_lms_assessment_questions';

    protected $fillable = ['prompt', 'position'];

    protected $casts = ['position' => 'integer', 'revision' => 'integer'];

    /**
     * Get the assessment that owns the question.
     *
     * @return BelongsTo<Assessment, $this>
     * @since 0.1.0
     */
    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    /**
     * Get answer options in display order.
     *
     * @return HasMany<AssessmentOption, $this>
     * @since 0.1.0
     */
    public function options(): HasMany
    {
        return $this->hasMany(AssessmentOption::class, 'question_id')->orderBy('position')->orderBy('id');
    }
}
