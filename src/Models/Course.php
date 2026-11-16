<?php

namespace Seiger\sLMS\Models;

use EvolutionCMS\Models\SiteContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A learning programme with an optional public Evolution CMS resource.
 *
 * @since 0.1.0
 */
class Course extends Model
{
    protected $table = 's_lms_courses';

    protected $fillable = ['resource_id', 'slug', 'title', 'description', 'published'];

    protected $casts = ['resource_id' => 'integer', 'published' => 'boolean'];

    /**
     * Get the linked CMS resource, when the course has a public page.
     *
     * @since 0.1.0
     * @return BelongsTo<SiteContent, $this>
     */
    public function resource(): BelongsTo
    {
        return $this->belongsTo(SiteContent::class, 'resource_id');
    }

    /**
     * Get all modules in programme order, including unpublished modules.
     *
     * @since 0.1.0
     * @return HasMany<Module, $this>
     */
    public function modules(): HasMany
    {
        return $this->hasMany(Module::class)->orderBy('position')->orderBy('id');
    }
}
