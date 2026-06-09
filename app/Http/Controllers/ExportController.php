<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Services\AnalyticsDateRangeResolver;
use App\Services\SiteAnalyticsService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function show(
        Site $site,
        Request $request,
        SiteAnalyticsService $analytics,
        AnalyticsDateRangeResolver $rangeResolver,
    ): StreamedResponse {
        $this->authorize('view', $site);

        $user = $request->user();
        $dateRange = $rangeResolver->resolve(
            $request,
            $user->preferredTimezone(),
            $user->default_date_range,
        );

        $metrics = $analytics->aggregate($site, $dateRange, $user->preferredTimezone());
        $filename = sprintf(
            '%s-analytics-%s-%s.csv',
            $site->domain,
            $dateRange->preset,
            now()->format('Y-m-d')
        );

        return response()->streamDownload(function () use ($metrics, $site, $dateRange): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['VibeMetrics Export']);
            fputcsv($handle, ['Site', $site->name]);
            fputcsv($handle, ['Domain', $site->domain]);
            fputcsv($handle, ['Date range', $metrics['date_range']['label'] ?? $dateRange->label]);
            fputcsv($handle, []);
            fputcsv($handle, ['Total Page Views', $metrics['total_page_views']]);
            fputcsv($handle, ['Unique Visitors', $metrics['unique_visitors']]);
            fputcsv($handle, ['Live Visitors (5 min)', $metrics['live_visitors']]);
            fputcsv($handle, []);
            fputcsv($handle, ['Trend']);
            fputcsv($handle, ['Period', 'Page Views', 'Visitors']);

            foreach ($metrics['daily_trend'] as $index => $row) {
                $visitors = $metrics['visitors_trend'][$index]['count'] ?? 0;
                fputcsv($handle, [$row['date'], $row['count'], $visitors]);
            }

            fputcsv($handle, []);
            fputcsv($handle, ['Top Pages', 'Views']);
            foreach ($metrics['top_urls'] as $row) {
                fputcsv($handle, [$row['label'], $row['count']]);
            }

            fputcsv($handle, []);
            fputcsv($handle, ['Top Referrers', 'Views']);
            foreach ($metrics['top_referrers'] as $row) {
                fputcsv($handle, [$row['label'], $row['count']]);
            }

            fputcsv($handle, []);
            fputcsv($handle, ['Channels', 'Views']);
            foreach ($metrics['channels'] as $row) {
                fputcsv($handle, [$row['label'], $row['count']]);
            }

            fputcsv($handle, []);
            fputcsv($handle, ['Top Campaigns', 'Views']);
            foreach ($metrics['top_campaigns'] as $row) {
                fputcsv($handle, [$row['label'], $row['count']]);
            }

            fputcsv($handle, []);
            fputcsv($handle, ['UTM Sources', 'Views']);
            foreach ($metrics['utm_sources'] as $row) {
                fputcsv($handle, [$row['label'], $row['count']]);
            }

            fputcsv($handle, []);
            fputcsv($handle, ['UTM Mediums', 'Views']);
            foreach ($metrics['utm_mediums'] as $row) {
                fputcsv($handle, [$row['label'], $row['count']]);
            }

            fputcsv($handle, []);
            fputcsv($handle, ['Browsers', 'Views']);
            foreach ($metrics['browsers'] as $row) {
                fputcsv($handle, [$row['label'], $row['count']]);
            }

            fputcsv($handle, []);
            fputcsv($handle, ['Operating Systems', 'Views']);
            foreach ($metrics['operating_systems'] as $row) {
                fputcsv($handle, [$row['label'], $row['count']]);
            }

            fputcsv($handle, []);
            fputcsv($handle, ['Devices', 'Views']);
            foreach ($metrics['devices'] as $row) {
                fputcsv($handle, [$row['label'], $row['count']]);
            }

            fputcsv($handle, []);
            fputcsv($handle, ['Countries', 'Views']);
            foreach ($metrics['countries'] as $row) {
                fputcsv($handle, [$row['label'], $row['count']]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
