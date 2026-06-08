<?php

namespace App\Console\Commands;

use App\Services\PlatformSettingsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PurgeOldPageViews extends Command
{
    protected $signature = 'analytics:purge';

    protected $description = 'Delete page views older than the retention period';

    public function handle(PlatformSettingsService $settings): int
    {
        $days = $settings->getInt('retention_days', 365);
        $cutoff = now()->subDays($days);
        $totalDeleted = 0;

        do {
            $deleted = DB::table('page_views')
                ->where('created_at', '<', $cutoff)
                ->limit(10000)
                ->delete();

            $totalDeleted += $deleted;
        } while ($deleted > 0);

        $settings->set('last_purge_at', now()->toIso8601String());

        $this->info("Purged {$totalDeleted} page views older than {$days} days.");

        return self::SUCCESS;
    }
}
