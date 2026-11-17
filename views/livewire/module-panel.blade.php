<x-evo::module-tab-shell :tabs="$tabs" model="activeTab" label="sLMS">
    @if(!$schemaReady)
        <p class="evo-ui-empty">@lang('sLMS::global.courses.schema_missing')</p>
    @elseif($activeTab === 'overview')
        @include('sLMS::overview', ['overview' => $overview])
    @else
        @php
            $section = $assessment ? 'sLMS::global.assessment.title' : 'sLMS::global.overview.lessons';
        @endphp
        @if($course)
            <header class="evo-ui-surface__header">
                <h2>{{ $module ? $course->title . ' / ' . $module->title : $course->title }} · {{ __($module ? $section : 'sLMS::global.overview.modules') }}</h2>
                <x-evo::button icon="arrow-left" :label="__($module ? 'sLMS::global.curriculum.back_to_modules' : 'sLMS::global.curriculum.back_to_courses')" wire:click="backToParent" />
            </header>
        @endif
        @if(!$assessmentReady)
            <p class="evo-ui-empty">@lang('sLMS::global.assessment.schema_missing')</p>
        @else
            @php
                $preset = $module ? ($assessment ? 'slms.assessment' : 'slms.lessons') : ($course ? 'slms.modules' : 'slms.courses');
                $context = $course ? ['parent_id' => $module?->id ?? $course->id, 'module' => $module?->id ?? $course->id] : [];
            @endphp
            <livewire:slms.catalogue-table :preset="$preset" :context="$context" :key="$preset . '-' . ($context['parent_id'] ?? 0)" />
        @endif
    @endif
</x-evo::module-tab-shell>
