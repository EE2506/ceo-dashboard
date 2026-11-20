<?php

namespace App\Services;

use App\Models\FollowUp;
use App\Models\Payment;
use App\Models\Site;
use Illuminate\Support\Carbon;

class DashboardAnalytics
{
    /** @return array{paid: float, outstanding: int, sites: int, open: int, overdue: int} */
    public function snapshot(Carbon $today): array
    {
        $openFollowUps = FollowUp::query()->where('is_completed', false);

        return [
            'paid' => (float) Payment::query()->where('status', 'paid')
                ->whereBetween('recorded_on', [$today->copy()->startOfMonth()->toDateString(), $today->toDateString()])
                ->sum('amount'),
            'outstanding' => Payment::query()->whereIn('status', Payment::OUTSTANDING_STATUSES)->count(),
            'sites' => Site::query()->count(),
            'open' => (clone $openFollowUps)->count(),
            'overdue' => $openFollowUps->where('due_on', '<', $today->toDateString())->count(),
        ];
    }

    /** @return list<array{label: string, amount: float, height: float}> */
    public function monthlyPaid(Carbon $today): array
    {
        $first = $today->copy()->startOfMonth()->subMonths(5);
        $totals = Payment::query()->where('status', 'paid')
            ->whereBetween('recorded_on', [$first->toDateString(), $today->toDateString()])
            ->selectRaw('SUBSTR(recorded_on, 1, 7) AS month_key, SUM(amount) AS month_amount')
            ->groupBy('month_key')
            ->pluck('month_amount', 'month_key');
        $ceiling = max(1, (float) $totals->max());

        return array_map(function (int $offset) use ($first, $totals, $ceiling): array {
            $month = $first->copy()->addMonths($offset);
            $amount = (float) ($totals[$month->format('Y-m')] ?? 0);

            return [
                'label' => $month->format('M Y'),
                'amount' => $amount,
                'height' => round($amount / $ceiling * 100, 2),
            ];
        }, range(0, 5));
    }
}
