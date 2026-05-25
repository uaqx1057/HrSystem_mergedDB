<div {{
        $attributes->class([
            'card',
            'border-0',
            'b-shadow-4',
            'e-card-d-info-light' => !user()->dark_theme,
        ])
    }}>
    @if ($title)
        <x-cards.card-header>
            {!! $title !!}

            <x-slot name="action">
                {!! $action !!}
            </x-slot>

        </x-cards.card-header>
    @endif

    @if ($padding === 'false')
        <div class="card-body p-0 {{ $otherClasses }}">
            {{ $slot }}
        </div>
    @else
        <div @class([
            'card-body', 'pt-2' => ($title),
            $otherClasses
        ])>
            {{ $slot }}
        </div>
    @endif
</div>
