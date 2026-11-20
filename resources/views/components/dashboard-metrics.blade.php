@props(['snapshot'])

<dl class="desk-metrics">
    <div class="desk-panel desk-metric">
        <dt>Paid records this month</dt>
        <dd>{{ \Illuminate\Support\Number::currency($snapshot['paid'], 'PHP', 'en_PH') }}</dd>
    </div>
    <div class="desk-panel desk-metric">
        <dt>Outstanding payment records</dt>
        <dd>{{ number_format($snapshot['outstanding']) }}</dd>
    </div>
    <div class="desk-panel desk-metric">
        <dt>Tracked sites</dt>
        <dd>{{ number_format($snapshot['sites']) }}</dd>
    </div>
    <div class="desk-panel desk-metric">
        <dt>Open follow-ups</dt>
        <dd>{{ number_format($snapshot['open']) }}</dd>
        <dd class="desk-metric-note">{{ number_format($snapshot['overdue']) }} overdue</dd>
    </div>
</dl>
