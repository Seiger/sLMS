@php
    $manager = app(\EvoUI\Support\ManagerContext::class);
    $theme = $manager->theme();
@endphp

@include('evo::partials.assets')

<div class="evo-ui evo-ui-module-page {{ $manager->themeClasses($theme) }}"
     data-evo-ui-root
     data-theme="{{ $theme }}"
     data-theme-mode="{{ $manager->themeMode($theme) }}">
    <livewire:slms.module-panel />
</div>
