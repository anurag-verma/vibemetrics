<?php

namespace App\Services;

use App\Models\Site;
use Illuminate\Support\Facades\DB;

class SiteResetService
{
    public function reset(Site $site): int
    {
        $deleted = 0;

        do {
            $count = DB::table('page_views')
                ->where('site_id', $site->id)
                ->limit(10000)
                ->delete();
            $deleted += $count;
        } while ($count > 0);

        DB::table('daily_stats')->where('site_id', $site->id)->delete();

        return $deleted;
    }
}
