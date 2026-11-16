<?php

namespace Seiger\sLMS\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A lesson whose identity stays fixed when its position or slug changes.
 *
 * @since 0.1.0
 */
class Lesson extends Model
{
    protected $table = 's_lms_lessons';

    protected $fillable = ['slug', 'title', 'content', 'position', 'published'];

    protected $casts = ['position' => 'integer', 'published' => 'boolean'];

    /**
     * Get the module that owns this lesson.
     *
     * @since 0.1.0
     * @return BelongsTo<Module, $this>
     */
    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }
}
