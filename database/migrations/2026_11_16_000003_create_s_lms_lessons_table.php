<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Defines the schema for lessons and their module membership.
 *
 * @since 0.1.0
 */
return new class extends Migration
{
    /**
     * Create the lesson table with slugs and ordering scoped to a module.
     *
     * @since 0.1.0
     */
    public function up(): void
    {
        Schema::create('s_lms_lessons', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->id();
            $table->foreignId('module_id')->constrained('s_lms_modules')->restrictOnDelete();
            $table->string('slug', 191);
            $table->string('title');
            $table->longText('content')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->boolean('published')->default(false);
            $table->timestamps();

            $table->unique(['module_id', 'slug']);
            $table->index(['module_id', 'position', 'id']);
        });
    }

    /**
     * Drop the lesson table before rolling back its parent table.
     *
     * @since 0.1.0
     */
    public function down(): void
    {
        Schema::dropIfExists('s_lms_lessons');
    }
};
