<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyStat extends Model
{
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'site_id',
        'date',
        'page_views',
        'unique_visitors',
        'devices',
        'top_browsers',
        'top_os',
        'countries',
        'top_urls',
        'top_referrers',
        'top_campaigns',
        'utm_sources',
        'utm_mediums',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'devices' => 'array',
            'top_browsers' => 'array',
            'top_os' => 'array',
            'countries' => 'array',
            'top_urls' => 'array',
            'top_referrers' => 'array',
            'top_campaigns' => 'array',
            'utm_sources' => 'array',
            'utm_mediums' => 'array',
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
