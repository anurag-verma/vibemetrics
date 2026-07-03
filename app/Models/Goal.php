<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Goal extends Model
{
    protected $fillable = ['site_id', 'name', 'match_type', 'url_pattern'];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
