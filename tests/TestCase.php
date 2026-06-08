<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /** @return array<string, string> */
    protected function inertiaHeaders(): array
    {
        $version = '';

        if (file_exists($manifest = public_path('build/manifest.json'))) {
            $version = hash_file('xxh128', $manifest);
        }

        return [
            'X-Inertia' => 'true',
            'X-Inertia-Version' => $version,
        ];
    }

    protected function getInertia(string $uri)
    {
        return $this->withHeaders($this->inertiaHeaders())->get($uri);
    }
}
