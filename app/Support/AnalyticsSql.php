<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

class AnalyticsSql
{
    public static function visitorFingerprintExpression(): string
    {
        $deviceFingerprint = match (DB::connection()->getDriverName()) {
            'sqlite' => "(COALESCE(browser, '') || '|' || COALESCE(os, '') || '|' || COALESCE(device, ''))",
            default => "CONCAT(COALESCE(browser, ''), '|', COALESCE(os, ''), '|', COALESCE(device, ''))",
        };

        return match (DB::connection()->getDriverName()) {
            'sqlite' => "COALESCE(NULLIF(visitor_id, ''), {$deviceFingerprint})",
            default => "COALESCE(NULLIF(visitor_id, ''), {$deviceFingerprint})",
        };
    }

    public static function dailyVisitorFingerprintExpression(): string
    {
        $fingerprint = self::visitorFingerprintExpression();

        return match (DB::connection()->getDriverName()) {
            'sqlite' => "(DATE(created_at) || '|' || {$fingerprint})",
            default => "CONCAT(DATE(created_at), '|', {$fingerprint})",
        };
    }

    public static function dayOfWeekExpression(): string
    {
        return match (DB::connection()->getDriverName()) {
            'sqlite' => "CAST(strftime('%w', created_at) AS INTEGER)",
            default => '(DAYOFWEEK(created_at) - 1)',
        };
    }

    public static function hourExpression(): string
    {
        return match (DB::connection()->getDriverName()) {
            'sqlite' => "CAST(strftime('%H', created_at) AS INTEGER)",
            default => 'HOUR(created_at)',
        };
    }
}
