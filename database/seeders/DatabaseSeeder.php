<?php

namespace Database\Seeders;

use App\Models\Site;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(PlatformSettingsSeeder::class);

        $admin = User::query()->create([
            'name' => 'Super Admin',
            'email' => 'admin@vibemetrics.test',
            'password' => Hash::make('password'),
            'is_admin' => true,
            'email_verified_at' => now(),
        ]);

        $user = User::query()->create([
            'name' => 'Demo User',
            'email' => 'demo@vibemetrics.test',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $site = Site::query()->create([
            'user_id' => $user->id,
            'name' => 'Demo Site',
            'domain' => 'example.com',
        ]);

        $now = now();

        for ($i = 0; $i < 14; $i++) {
            $date = $now->copy()->subDays($i);

            for ($j = 0; $j < rand(5, 25); $j++) {
                DB::table('page_views')->insert([
                    'site_id' => $site->id,
                    'url' => collect(['/', '/about', '/pricing', '/blog/post-'.rand(1, 5)])->random(),
                    'referrer' => collect([null, 'https://google.com', 'https://twitter.com', 'https://github.com'])->random(),
                    'browser' => collect(['Chrome', 'Firefox', 'Safari', 'Edge'])->random(),
                    'os' => collect(['Windows', 'macOS', 'Linux', 'Android'])->random(),
                    'device' => collect(['desktop', 'mobile', 'tablet'])->random(),
                    'country' => collect(['US', 'GB', 'DE', 'IN', 'CA'])->random(),
                    'utm_campaign' => $j % 4 === 0 ? collect(['spring_sale', 'launch', 'newsletter'])->random() : null,
                    'utm_source' => $j % 4 === 0 ? 'google' : null,
                    'utm_medium' => $j % 4 === 0 ? 'cpc' : null,
                    'created_at' => $date->copy()->subMinutes(rand(0, 1440)),
                ]);
            }
        }
    }
}
