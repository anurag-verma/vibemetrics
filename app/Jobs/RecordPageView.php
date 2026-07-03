<?php

namespace App\Jobs;

use App\Services\GeoIpResolver;
use App\Services\UrlNormalizer;
use App\Services\UserAgentParser;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

class RecordPageView implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /** @var list<int> */
    public array $backoff = [1, 5, 15];

    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public int $siteId,
        public array $payload,
        public ?string $country = null,
        public ?string $ip = null,
        public ?string $userAgent = null,
    ) {}

    public function handle(UserAgentParser $parser, UrlNormalizer $normalizer, GeoIpResolver $geoIp): void
    {
        $parsed = $parser->parse($this->userAgent);

        $device = $this->payload['device'] ?? null;

        if (! in_array($device, ['desktop', 'mobile', 'tablet'], true)) {
            $device = $parser->inferDevice($this->userAgent);
        }

        $visitorId = $this->payload['visitor_id'] ?? null;
        if (! is_string($visitorId) || strlen($visitorId) > 36) {
            $visitorId = null;
        }

        $resolved = $this->country ?? $geoIp->resolveFromIp($this->ip);
        $country = strtoupper(substr($resolved, 0, 2));
        if ($country === '' || strlen($country) !== 2) {
            $country = 'XX';
        }

        DB::table('page_views')->insert([
            'site_id' => $this->siteId,
            'visitor_id' => $visitorId,
            'url' => $normalizer->normalize((string) $this->payload['url']),
            'referrer' => isset($this->payload['referrer']) ? mb_substr((string) $this->payload['referrer'], 0, 2048) : null,
            'browser' => $parsed['browser'],
            'os' => $parsed['os'],
            'device' => $device,
            'country' => $country,
            'utm_source' => $this->payload['utm_source'] ?? null,
            'utm_medium' => $this->payload['utm_medium'] ?? null,
            'utm_campaign' => $this->payload['utm_campaign'] ?? null,
            'utm_term' => $this->payload['utm_term'] ?? null,
            'utm_content' => $this->payload['utm_content'] ?? null,
            'created_at' => now(),
        ]);
    }
}
