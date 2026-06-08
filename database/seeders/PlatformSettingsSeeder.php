<?php

namespace Database\Seeders;

use App\Services\PlatformSettingsService;
use Illuminate\Database\Seeder;

class PlatformSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $service = app(PlatformSettingsService::class);

        foreach (PlatformSettingsService::defaults() as $key => $value) {
            $service->set($key, $value);
        }
    }
}
