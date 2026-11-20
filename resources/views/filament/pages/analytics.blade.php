<x-filament-panels::page>
    <div class="desk-stack">
        <section aria-labelledby="analytics-overview">
            <div class="desk-section-heading">
                <h2 id="analytics-overview">Business snapshot</h2>
                <p>{{ $today->format('F Y') }} · Manila time</p>
            </div>
            <x-dashboard-metrics :snapshot="$snapshot" />
        </section>

        <section class="desk-panel desk-report" aria-labelledby="paid-trend">
            <h2 id="paid-trend">Paid records by month</h2>
            <p class="desk-meta">Totals use the recorded date of payments marked paid.</p>

            @if (array_sum(array_column($months, 'amount')) > 0)
                <ul class="desk-trend">
                    @foreach ($months as $month)
                        <li class="desk-trend-row">
                            <span>{{ $month['label'] }}</span>
                            <div class="desk-trend-track" aria-hidden="true">
                                <div class="desk-trend-bar @if ($loop->last) desk-trend-current @endif"
                                    style="--desk-bar-width: {{ $month['height'] }}%"></div>
                            </div>
                            <strong>{{ \Illuminate\Support\Number::currency($month['amount'], 'PHP', 'en_PH') }}</strong>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="desk-trend">No paid records in the last six months.</p>
            @endif
        </section>
    </div>
</x-filament-panels::page>
