<?php

namespace Seiger\sLMS\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Stores a snapshot of the questions, answer key and passing threshold.
 *
 * @since 0.1.0
 */
class AssessmentVersion extends Model
{
    protected $table = 's_lms_assessment_versions';

    protected $fillable = ['fingerprint', 'definition', 'pass_percent'];

    protected $casts = ['definition' => 'array', 'pass_percent' => 'integer'];

    protected $hidden = ['definition', 'fingerprint'];

    /**
     * Get the editable assessment from which this version was created.
     *
     * @return BelongsTo<Assessment, $this>
     * @since 0.1.0
     */
    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    /**
     * Get the learner attempts that use this version.
     *
     * @return HasMany<AssessmentAttempt, $this>
     * @since 0.1.0
     */
    public function attempts(): HasMany
    {
        return $this->hasMany(AssessmentAttempt::class, 'version_id');
    }
}
