<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Defines the schema for modules and their course membership.
 *
 * @since 0.1.0
 */
return new class extends Migration
{
    /**
     * Create the module table with slugs and ordering scoped to a course.
     *
     * @since 0.1.0
     */
    public function up(): void
    {
        Schema::create('s_lms_modules', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->id();
            $table->foreignId('course_id')->constrained('s_lms_courses')->restrictOnDelete();
            $table->string('slug', 191);
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->boolean('published')->default(false);
            $table->timestamps();

            $table->unique(['course_id', 'slug']);
            $table->index(['course_id', 'position', 'id']);
        });
    }

    /**
     * Drop the module table after the lessons table has been rolled back.
     *
     * @since 0.1.0
     */
    public function down(): void
    {
        Schema::dropIfExists('s_lms_modules');
    }
};
