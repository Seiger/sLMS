<x-evo::module-tab-shell :tabs="$tabs" model="activeTab" label="sLMS">
    @if(!$schemaReady)
        <p class="evo-ui-empty">@lang('sLMS::global.courses.schema_missing')</p>
    @elseif($activeTab === 'overview')
        @include('sLMS::overview', ['overview' => $overview])
    @else
        <x-evo::table.livewire preset="slms.courses" wire-key="slms-courses" />
    @endif
</x-evo::module-tab-shell>
