<x-filament-panels::page>
    <div class="desk-stack">
        <section aria-labelledby="today-priorities">
            <div class="desk-section-heading">
                <h2 id="today-priorities">Needs attention</h2>
                <p><time datetime="{{ $today->toDateString() }}">{{ $today->format('l, F j, Y') }}</time> · Manila time</p>
            </div>

            @if ($items === [])
                <p class="desk-panel desk-empty">No follow-ups or payments are due.</p>
            @else
                <ol class="desk-priorities">
                    @foreach ($items as $item)
                        @php($overdue = $item['date'] < $today->toDateString())
                        <li>
                            <a class="desk-panel desk-priority" href="{{ $item['url'] }}">
                                <span>
                                    <strong>{{ $item['title'] }}</strong>
                                    <span class="desk-meta">{{ $item['client'] }} · {{ $item['type'] === 'payment' ? 'Payment' : 'Follow-up' }}</span>
                                </span>
                                <time datetime="{{ $item['date'] }}">{{ \Illuminate\Support\Carbon::parse($item['date'])->format('M j, Y') }}</time>
                                <span class="desk-status @if ($overdue) desk-status-overdue @endif">{{ $overdue ? 'Overdue' : 'Due today' }}</span>
                                <x-filament::icon icon="heroicon-o-chevron-right" class="desk-chevron" aria-hidden="true" />
                            </a>
                        </li>
                    @endforeach
                </ol>
            @endif
        </section>

        @if ($snapshot !== null)
            <section aria-labelledby="today-snapshot">
                <div class="desk-section-heading">
                    <h2 id="today-snapshot">Business snapshot</h2>
                    <x-filament::link :href="\App\Filament\Pages\Analytics::getUrl()">View analytics</x-filament::link>
                </div>
                <x-dashboard-metrics :snapshot="$snapshot" />
            </section>
        @endif
    </div>
</x-filament-panels::page>
