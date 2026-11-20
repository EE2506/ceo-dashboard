<x-filament-panels::page>
    <div class="desk-stack">
        <x-filament::section heading="Mailbox connection">
            @if ($feedback !== null)
                <p role="status">{{ $feedback }}</p>
            @endif

            @if ($inbox['address'] !== null)
                <p>{{ $inbox['address'] }} · Read-only</p>
            @endif

            @if ($inbox['status'] === 'unconfigured')
                <p>Configure Gmail OAuth in the local environment to connect a mailbox.</p>
            @elseif ($inbox['status'] === 'disconnected')
                <p>Connect the Google account that receives your client mail.</p>
            @elseif ($inbox['status'] === 'reconnect')
                <p>Gmail access expired or was revoked. Reconnect to see labeled threads.</p>
            @endif

            <x-slot name="footer">
                @if (in_array($inbox['status'], ['disconnected', 'reconnect'], true))
                    <form method="POST" action="{{ route('gmail.connect') }}">
                        @csrf
                        <x-filament::button type="submit">
                            {{ $inbox['status'] === 'reconnect' ? 'Reconnect Gmail' : 'Connect Gmail' }}
                        </x-filament::button>
                    </form>
                @endif

                @if ($inbox['address'] !== null)
                    <form method="POST" action="{{ route('gmail.disconnect') }}">
                        @csrf
                        <x-filament::button type="submit" color="gray">Disconnect Gmail</x-filament::button>
                    </form>
                @endif
            </x-slot>
        </x-filament::section>

        @if (in_array($inbox['status'], ['connected', 'label_missing', 'unavailable'], true))
            <section aria-labelledby="gmail-threads">
                <div class="desk-section-heading">
                    <h2 id="gmail-threads">{{ $label }}</h2>
                    <x-filament::link :href="\App\Filament\Pages\Gmail::getUrl()">Refresh threads</x-filament::link>
                </div>

                @if ($inbox['status'] === 'label_missing')
                    <p class="desk-panel desk-empty">Create the label “{{ $label }}” in Gmail, then refresh this page.</p>
                @elseif ($inbox['status'] === 'unavailable')
                    <p class="desk-panel desk-empty">Gmail is temporarily unavailable. Try refreshing shortly.</p>
                @elseif ($inbox['threads'] === [])
                    <p class="desk-panel desk-empty">No Gmail threads have this label.</p>
                @else
                    <ul class="desk-priorities">
                        @foreach ($inbox['threads'] as $thread)
                            <li>
                                <div class="desk-panel desk-empty">
                                    <x-filament::link :href="$thread['url']" target="_blank" rel="noopener noreferrer">
                                        {{ $thread['subject'] }}
                                    </x-filament::link>
                                    <p class="desk-meta">{{ $thread['sender'] }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        @endif
    </div>
</x-filament-panels::page>
