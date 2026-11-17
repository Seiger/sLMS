<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $moduleTitle }} · {{ __('sLMS::global.assessment.title') }}</title>
    <style>
        body { margin: 0; color: #182235; background: #f4f6f9; font: 1rem/1.55 Inter, sans-serif; }
        main { box-sizing: border-box; width: min(100%, 48rem); margin: auto; padding: clamp(1rem, 4vw, 2.5rem); }
        h1 { margin: 0 0 1.25rem; line-height: 1.2; }
        fieldset { margin: 0 0 1rem; min-width: 0; padding: 1rem; border: 1px solid #c5ceda; border-radius: .5rem; background: #fff; }
        legend { padding-inline: .375rem; font-weight: 650; }
        legend, label span { overflow-wrap: anywhere; white-space: pre-wrap; }
        label { display: flex; align-items: baseline; gap: .65rem; padding-block: .35rem; cursor: pointer; }
        input[type=checkbox] { flex: none; }
        .actions { display: flex; flex-wrap: wrap; align-items: center; gap: .75rem; }
        button { padding: .65rem 1rem; border: 1px solid #184d8e; border-radius: .375rem; background: #184d8e; color: #fff; font: inherit; cursor: pointer; }
        button.secondary { background: #fff; color: #184d8e; }
        a { color: #184d8e; }
        [role=alert] { color: #8b1d26; }
    </style>
</head>
<body>
<main>
    <h1>{{ $moduleTitle }}</h1>

    @if($attemptId === null)
        @if($available)
            <form method="post" action="{{ route('slms.assessment.start', ['module' => $moduleId]) }}">
                @csrf
                <button type="submit">{{ __('sLMS::global.attempt.start') }}</button>
            </form>
        @else
            <p>{{ __('sLMS::global.attempt.unavailable') }}</p>
        @endif
    @elseif($submitted)
        <p>{{ __('sLMS::global.attempt.score', ['score' => $score, 'total' => $total, 'percent' => $percent]) }}</p>
        <p><strong>{{ __($successful ? 'sLMS::global.attempt.success' : 'sLMS::global.attempt.failure') }}</strong>
            {{ __('sLMS::global.attempt.threshold', ['percent' => $threshold]) }}</p>
        <form class="actions" method="post" action="{{ route('slms.assessment.start', ['module' => $moduleId]) }}">
            @csrf
            <button type="submit">{{ __('sLMS::global.attempt.retry') }}</button>
        </form>
    @else
        @if($error !== null)
            <p role="alert">{{ $error }}</p>
        @elseif($saved)
            <p role="status">{{ __('sLMS::global.attempt.saved') }}</p>
        @endif
        <form method="post" action="{{ route('slms.assessment.save', ['attempt' => $attemptId]) }}">
            @csrf
            @foreach($questions as $question)
                <fieldset>
                    <legend>{{ $loop->iteration }}. {{ $question['prompt'] }}</legend>
                    @foreach($question['options'] as $option)
                        <label>
                            <input type="checkbox" name="answers[{{ $question['id'] }}][]" value="{{ $option['id'] }}"
                                @checked(in_array($option['id'], $answers[$question['id']] ?? [], true))>
                            <span>{{ $option['text'] }}</span>
                        </label>
                    @endforeach
                </fieldset>
            @endforeach
            <input type="hidden" name="answers_present" value="1">
            <div class="actions">
                <button type="submit" class="secondary">{{ __('sLMS::global.attempt.save') }}</button>
                <button type="submit" formaction="{{ route('slms.assessment.submit', ['attempt' => $attemptId]) }}">
                    {{ __('sLMS::global.attempt.submit') }}
                </button>
            </div>
        </form>
    @endif
</main>
</body>
</html>
