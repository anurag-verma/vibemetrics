<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformSetting extends Model
{
    public $incrementing = false;

    protected $primaryKey = 'key';

    protected $keyType = 'string';

    public const UPDATED_AT = 'updated_at';

    public const CREATED_AT = null;

    /** @var list<string> */
    protected $fillable = ['key', 'value'];
}
