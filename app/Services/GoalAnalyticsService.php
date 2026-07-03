<?php

namespace App\Services;

use App\Models\Site;
use App\Support\AnalyticsDateRange;
use App\Support\AnalyticsSql;
use Illuminate\Support\Facades\DB;

class GoalAnalyticsService
{
    /** @return list<array<string, mixed>> */
    public function forSite(Site $site, AnalyticsDateRange $range, int $totalUniqueVisitors): array
    {
        $goals = $site->goals()->orderBy('name')->get();

        if ($goals->isEmpty()) {
            return [];
        }

        $fingerprint = AnalyticsSql::visitorFingerprintExpression();

        return $goals->map(function ($goal) use ($site, $range, $totalUniqueVisitors, $fingerprint) {
            $base = DB::table('page_views')
                ->where('site_id', $site->id)
                ->where('created_at', '>=', $range->startUtc())
                ->where('created_at', '<=', $range->endUtc());

            if ($goal->match_type === 'exact') {
                $pattern = $goal->url_pattern;
                $base->where(function ($q) use ($pattern) {
                    $q->where('url', $pattern)
                      ->orWhere('url', 'like', '%' . ltrim($pattern, '/'));
                });
            } else {
                $base->where('url', 'like', '%' . $goal->url_pattern . '%');
            }

            $completions = (int) (clone $base)->count();
            $uniqueCompletions = (int) (clone $base)
                ->selectRaw("COUNT(DISTINCT {$fingerprint}) as cnt")
                ->value('cnt');

            $conversionRate = $totalUniqueVisitors > 0
                ? round(($uniqueCompletions / $totalUniqueVisitors) * 100, 1)
                : 0.0;

            return [
                'id'                 => $goal->id,
                'name'               => $goal->name,
                'match_type'         => $goal->match_type,
                'url_pattern'        => $goal->url_pattern,
                'completions'        => $completions,
                'unique_completions' => $uniqueCompletions,
                'conversion_rate'    => $conversionRate,
            ];
        })->all();
    }
}
