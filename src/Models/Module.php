<?php

namespace Seiger\sLMS\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * An ordered group of lessons within a single course.
 *
 * @since 0.1.0
 */
class Module extends Model
{
    protected $table = 's_lms_modules';

    protected $fillable = ['slug', 'title', 'description', 'position', 'published'];

    protected $casts = ['position' => 'integer', 'published' => 'boolean'];

    /**
     * Get the course that owns this module.
     *
     * @since 0.1.0
     * @return BelongsTo<Course, $this>
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get all lessons in module order, including unpublished lessons.
     *
     * @since 0.1.0
     * @return HasMany<Lesson, $this>
     */
    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class)->orderBy('position')->orderBy('id');
    }

    /**
     * Get the editable assessment for this module, once it has questions.
     *
     * @return HasOne<Assessment, $this>
     * @since 0.1.0
     */
    public function assessment(): HasOne
    {
        return $this->hasOne(Assessment::class);
    }
}
