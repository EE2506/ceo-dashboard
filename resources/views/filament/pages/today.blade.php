<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">Needs attention</x-slot>
        <x-slot name="description">
            <time datetime="{{ $today->toDateString() }}">{{ $today->format('l, F j, Y') }}</time> · Manila time
        </x-slot>

        @if ($items === [])
            <p>No follow-ups or payments are due.</p>
        @else
            <ol class="space-y-4">
                @foreach ($items as $item)
                    <li>
                        <x-filament::link :href="$item['url']">{{ $item['title'] }}</x-filament::link>
                        <p>
                            {{ $item['client'] }} · {{ $item['type'] === 'payment' ? 'Payment' : 'Follow-up' }} ·
                            {{ $item['date'] < $today->toDateString() ? 'Overdue' : 'Due today' }}
                            <time datetime="{{ $item['date'] }}">({{ \Illuminate\Support\Carbon::parse($item['date'])->format('M j') }})</time>
                        </p>
                    </li>
                @endforeach
            </ol>
        @endif
    </x-filament::section>
</x-filament-panels::page>
