<?php

namespace App\Services;

use App\Models\FollowUp;
use App\Models\Payment;

class TodayPriorities
{
    private const LIMIT = 5;

    /**
     * @return list<array{id: int, type: 'follow-up'|'payment', title: string, client: string, date: string}>
     */
    public function forDate(string $date): array
    {
        $items = FollowUp::query()
            ->with('client')
            ->where('is_completed', false)
            ->where('due_on', '<=', $date)
            ->orderBy('due_on')
            ->orderBy('id')
            ->limit(self::LIMIT)
            ->get()
            ->map(fn (FollowUp $record): array => [
                'id' => $record->id,
                'type' => 'follow-up',
                'title' => $record->title,
                'client' => $record->client->name,
                'date' => $record->due_on->toDateString(),
            ])
            ->all();

        $remaining = self::LIMIT - count($items);

        if ($remaining === 0) {
            return $items;
        }

        $payments = Payment::query()
            ->with('client')
            ->whereIn('status', Payment::OUTSTANDING_STATUSES)
            ->where('due_on', '<=', $date)
            ->orderBy('due_on')
            ->orderBy('id')
            ->limit($remaining)
            ->get()
            ->map(fn (Payment $record): array => [
                'id' => $record->id,
                'type' => 'payment',
                'title' => $record->purpose,
                'client' => $record->client->name,
                'date' => $record->due_on->toDateString(),
            ])
            ->all();

        return [...$items, ...$payments];
    }
}
