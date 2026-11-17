<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Defines the sLMS catalogue and assessment schema.
 *
 * @since 0.1.0
 */
return new class extends Migration
{
    private const PREVIOUS_MIGRATIONS = [
        's_lms_courses' => '2026_11_16_000001_create_s_lms_courses_table',
        's_lms_modules' => '2026_11_16_000002_create_s_lms_modules_table',
        's_lms_lessons' => '2026_11_16_000003_create_s_lms_lessons_table',
        's_lms_assessments' => '2026_11_16_000004_create_s_lms_assessments_table',
        's_lms_assessment_questions' => '2026_11_16_000005_create_s_lms_assessment_questions_table',
        's_lms_assessment_options' => '2026_11_16_000006_create_s_lms_assessment_options_table',
    ];

    /**
     * Create the missing tables in foreign-key dependency order.
     *
     * @since 0.1.0
     */
    public function up(): void
    {
        foreach ($this->tables() as $table => $definition) {
            if (!Schema::hasTable($table)) {
                Schema::create($table, $definition);
            }
        }
    }

    /**
     * Reverse this migration without dropping tables installed by earlier sLMS migrations.
     *
     * @since 0.1.0
     */
    public function down(): void
    {
        $repository = app('migration.repository');
        $previous = $repository->repositoryExists() ? $repository->getRan() : [];

        foreach (array_reverse(array_keys($this->tables())) as $table) {
            $legacyMigration = self::PREVIOUS_MIGRATIONS[$table] ?? null;
            if (!in_array($legacyMigration, $previous, true)) {
                Schema::dropIfExists($table);
            }
        }
    }

    /**
     * Define the complete package schema in creation order.
     *
     * @return array<string, Closure(Blueprint): void>
     * @since 0.1.0
     */
    private function tables(): array
    {
        return [
            's_lms_courses' => static function (Blueprint $table): void {
                $table->engine = 'InnoDB';
                $table->id();
                $table->unsignedBigInteger('resource_id')->nullable()->index();
                $table->string('slug', 191)->unique();
                $table->string('title');
                $table->text('description')->nullable();
                $table->boolean('published')->default(false);
                $table->timestamps();
            },
            's_lms_modules' => static function (Blueprint $table): void {
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
            },
            's_lms_lessons' => static function (Blueprint $table): void {
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
            },
            's_lms_assessments' => static function (Blueprint $table): void {
                $table->engine = 'InnoDB';
                $table->id();
                $table->foreignId('module_id')->unique()->constrained('s_lms_modules')->restrictOnDelete();
                $table->timestamps();
            },
            's_lms_assessment_questions' => static function (Blueprint $table): void {
                $table->engine = 'InnoDB';
                $table->id();
                $table->foreignId('assessment_id')->constrained('s_lms_assessments')->cascadeOnDelete();
                $table->text('prompt');
                $table->unsignedInteger('position');
                $table->unsignedInteger('revision')->default(1);
                $table->index(['assessment_id', 'position', 'id']);
                $table->timestamps();
            },
            's_lms_assessment_options' => static function (Blueprint $table): void {
                $table->engine = 'InnoDB';
                $table->id();
                $table->foreignId('question_id')->constrained('s_lms_assessment_questions')->cascadeOnDelete();
                $table->text('text');
                $table->boolean('is_correct')->default(false);
                $table->unsignedInteger('position');
                $table->index(['question_id', 'position', 'id']);
                $table->timestamps();
            },
        ];
    }
};
