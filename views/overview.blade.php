<x-evo::dashboard :cards="[
    ['title' => 'sLMS::global.courses.list', 'icon' => 'book', 'span' => 4, 'status' => 'primary', 'stats' => [['value' => $overview['courses']]]],
    ['title' => 'sLMS::global.overview.modules', 'icon' => 'stack-2', 'span' => 4, 'status' => 'info', 'stats' => [['value' => $overview['modules']]]],
    ['title' => 'sLMS::global.overview.lessons', 'icon' => 'file-text', 'span' => 4, 'stats' => [['value' => $overview['lessons']]]],
]">
    <x-slot:body>
        <div class="evo-ui-dashboard__cards">
            <x-evo::dashboard-card title="sLMS::global.overview.publication" icon="chart-bar" :span="6">
                @if($overview['courses'] > 0)
                    <div class="evo-ui-table-wrap">
                        <table class="evo-ui-table" style="min-width: 0;">
                            <tbody>
                                @foreach(['published' => 'sLMS::global.courses.published', 'drafts' => 'sLMS::global.overview.drafts'] as $key => $label)
                                    @php($share = (int) round($overview[$key] * 100 / $overview['courses']))
                                    <tr>
                                        <th scope="row">{{ __($label) }}</th>
                                        <td>
                                            <meter min="0" max="100" value="{{ $share }}" aria-hidden="true" style="width: 100%; min-width: 3rem;"></meter>
                                        </td>
                                        <td style="text-align: end;">{{ $overview[$key] }} ({{ $share }}%)</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="evo-ui-empty">@lang('sLMS::global.overview.empty')</p>
                @endif
            </x-evo::dashboard-card>
            <x-evo::dashboard-card title="sLMS::global.overview.created" icon="calendar" :span="6">
                <div class="evo-ui-table-wrap">
                    <table class="evo-ui-table" style="min-width: 0;">
                        <tbody>
                            @foreach(['today', 'week', 'month'] as $period)
                                <tr>
                                    <th scope="row">{{ __('sLMS::global.overview.' . $period) }}</th>
                                    <td style="text-align: end;">{{ $overview[$period] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-evo::dashboard-card>
        </div>
    </x-slot:body>
</x-evo::dashboard>
