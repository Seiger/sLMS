<?php

namespace Seiger\sLMS\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * An editable assessment attached to a learning module.
 *
 * @since 0.1.0
 */
class Assessment extends Model
{
    protected $table = 's_lms_assessments';

    /**
     * Get the learning module that owns this assessment.
     *
     * @return BelongsTo<Module, $this>
     * @since 0.1.0
     */
    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    /**
     * Get assessment questions in their stored order.
     *
     * @return HasMany<AssessmentQuestion, $this>
     * @since 0.1.0
     */
    public function questions(): HasMany
    {
        return $this->hasMany(AssessmentQuestion::class)->orderBy('position')->orderBy('id');
    }

    /**
     * Get the snapshots created for learner attempts.
     *
     * @return HasMany<AssessmentVersion, $this>
     * @since 0.1.0
     */
    public function versions(): HasMany
    {
        return $this->hasMany(AssessmentVersion::class);
    }
}
