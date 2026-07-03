<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

class RecordCustomEvent implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /** @var list<int> */
    public array $backoff = [1, 5, 15];

    /** @param array<string, mixed>|null $properties */
    public function __construct(
        public int $siteId,
        public string $name,
        public ?string $url,
        public ?string $visitorId,
        public ?array $properties,
    ) {}

    public function handle(): void
    {
        DB::table('custom_events')->insert([
            'site_id'    => $this->siteId,
            'name'       => $this->name,
            'visitor_id' => $this->visitorId,
            'url'        => $this->url,
            'properties' => $this->properties ? json_encode($this->properties) : null,
            'created_at' => now(),
        ]);
    }
}
