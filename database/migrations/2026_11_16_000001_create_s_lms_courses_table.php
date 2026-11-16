<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Defines the schema for sLMS courses.
 *
 * @since 0.1.0
 */
return new class extends Migration
{
    /**
     * Create the course table and index its optional CMS resource reference.
     *
     * @since 0.1.0
     */
    public function up(): void
    {
        Schema::create('s_lms_courses', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('resource_id')->nullable()->index();
            $table->string('slug', 191)->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->boolean('published')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Drop the course table after the dependent tables have been rolled back.
     *
     * @since 0.1.0
     */
    public function down(): void
    {
        Schema::dropIfExists('s_lms_courses');
    }
};
