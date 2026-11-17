<?php

namespace Seiger\sLMS\Components;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use Seiger\sLMS\Models\Assessment;
use Seiger\sLMS\Models\AssessmentOption;
use Seiger\sLMS\Models\AssessmentQuestion;
use Seiger\sLMS\Models\Course;
use Seiger\sLMS\Models\Lesson;
use Seiger\sLMS\Models\Module;
use Seiger\sLMS\Support\ManagerAccess;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Switch between the course manager and the current catalogue overview.
 *
 * @since 0.1.0
 */
class ModulePanel extends Component
{
    private const DEFAULT_TAB = 'overview';

    public string $activeTab = self::DEFAULT_TAB;

    #[Locked]
    public ?int $courseId = null;

    #[Locked]
    public ?int $moduleId = null;

    #[Locked]
    public bool $assessment = false;

    /**
     * Keep the selected tab within the two sections offered by sLMS.
     *
     * @since 0.1.0
     */
    public function updatedActiveTab(): void
    {
        if (!in_array($this->activeTab, ['courses', 'overview'], true)) {
            $this->activeTab = self::DEFAULT_TAB;
        }

        if ($this->activeTab === self::DEFAULT_TAB) {
            $this->courseId = $this->moduleId = null;
            $this->assessment = false;
        }
    }

    /**
     * Open the module list for a course selected in the course table.
     *
     * @since 0.1.0
     */
    #[On('slms-open-course')]
    public function openCourse(int $id): void
    {
        ManagerAccess::authorize();
        if ($this->activeTab !== 'courses' || $this->courseId !== null) {
            throw new NotFoundHttpException();
        }

        $this->courseId = (int) Course::query()->findOrFail($id)->getKey();
    }

    /**
     * Open lessons or the assessment for a module in the current course.
     *
     * @since 0.1.0
     */
    #[On('slms-open-module')]
    public function openModule(int $id, bool $assessment = false): void
    {
        ManagerAccess::authorize();
        if ($this->activeTab !== 'courses' || $this->courseId === null || $this->moduleId !== null) {
            throw new NotFoundHttpException();
        }

        $this->moduleId = (int) Module::query()->where('course_id', $this->courseId)->findOrFail($id)->getKey();
        $this->assessment = $assessment;
    }

    /**
     * Return to the modules of the current course or to the course catalogue.
     *
     * @since 0.1.0
     */
    public function backToParent(): void
    {
        ManagerAccess::authorize();

        if ($this->moduleId !== null) {
            $this->moduleId = null;
            $this->assessment = false;
        } else {
            $this->courseId = null;
        }
    }

    /**
     * Render the active manager tab and load the current catalogue level.
     *
     * @since 0.1.0
     */
    public function render(): View
    {
        ManagerAccess::authorize();

        $isOverview = $this->activeTab === 'overview';
        $schemaReady = Schema::hasTable((new Course())->getTable())
            && (!$isOverview && $this->courseId === null
                || (Schema::hasTable((new Module())->getTable()) && Schema::hasTable((new Lesson())->getTable())));

        $course = $schemaReady && $this->courseId !== null ? Course::query()->findOrFail($this->courseId) : null;
        $module = $course !== null && $this->moduleId !== null ? $course->modules()->findOrFail($this->moduleId) : null;
        $assessmentReady = !$this->assessment || $module === null
            || (Schema::hasTable((new Assessment())->getTable())
                && Schema::hasTable((new AssessmentQuestion())->getTable())
                && Schema::hasTable((new AssessmentOption())->getTable()));

        return view('sLMS::livewire.module-panel', [
            'tabs' => [
                ['key' => 'overview', 'label' => 'sLMS::global.overview.title', 'icon' => 'layout-dashboard'],
                ['key' => 'courses', 'label' => 'sLMS::global.courses.list', 'icon' => 'book'],
            ],
            'schemaReady' => $schemaReady,
            'overview' => $schemaReady && $isOverview ? $this->overview() : null,
            'course' => $course,
            'module' => $module,
            'assessmentReady' => $assessmentReady,
        ]);
    }

    /**
     * Count catalogue records, publication states and courses created in each period.
     *
     * @return array{courses: int, modules: int, lessons: int, published: int, drafts: int, today: int, week: int, month: int}
     * @since 0.1.0
     */
    protected function overview(): array
    {
        $today = CarbonImmutable::today();
        $counts = Course::query()->toBase()->selectRaw(
            'COUNT(*) AS total, SUM(CASE WHEN published THEN 1 ELSE 0 END) AS published, '
            . 'SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END) AS today_count, '
            . 'SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END) AS week_count, '
            . 'SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END) AS month_count',
            [$today, $today->subDays(6), $today->subDays(29)],
        )->first();

        return [
            'courses' => (int) $counts->total,
            'modules' => Module::query()->count(),
            'lessons' => Lesson::query()->count(),
            'published' => (int) $counts->published,
            'drafts' => (int) $counts->total - (int) $counts->published,
            'today' => (int) $counts->today_count,
            'week' => (int) $counts->week_count,
            'month' => (int) $counts->month_count,
        ];
    }
}
