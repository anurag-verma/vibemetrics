<?php

namespace App\Services;

class UserAgentParser
{
    /**
     * @return array{browser: string, os: string}
     */
    public function parse(?string $userAgent): array
    {
        if ($userAgent === null || $userAgent === '') {
            return ['browser' => 'Unknown', 'os' => 'Unknown'];
        }

        return [
            'browser' => $this->parseBrowser($userAgent),
            'os' => $this->parseOs($userAgent),
        ];
    }

    private function parseBrowser(string $ua): string
    {
        if (preg_match('/Edg\/([\d.]+)/', $ua)) {
            return 'Edge';
        }

        if (preg_match('/OPR\/([\d.]+)/', $ua) || preg_match('/Opera/', $ua)) {
            return 'Opera';
        }

        if (preg_match('/CriOS\/([\d.]+)/', $ua)) {
            return 'Chrome';
        }

        if (preg_match('/FxiOS\/([\d.]+)/', $ua)) {
            return 'Firefox';
        }

        if (preg_match('/Chrome\/([\d.]+)/', $ua) && ! preg_match('/Edg/', $ua)) {
            return 'Chrome';
        }

        if (preg_match('/Firefox\/([\d.]+)/', $ua)) {
            return 'Firefox';
        }

        if (preg_match('/Version\/([\d.]+).*Safari/', $ua)) {
            return 'Safari';
        }

        return 'Unknown';
    }

    private function parseOs(string $ua): string
    {
        if (preg_match('/iPhone|iPad|iPod/', $ua)) {
            return 'iOS';
        }

        if (preg_match('/Windows NT/', $ua)) {
            return 'Windows';
        }

        if (preg_match('/Android/', $ua)) {
            return 'Android';
        }

        if (preg_match('/Mac OS X|Macintosh/', $ua)) {
            return 'macOS';
        }

        if (preg_match('/Linux/', $ua)) {
            return 'Linux';
        }

        return 'Unknown';
    }

    public function inferDevice(?string $userAgent): string
    {
        if ($userAgent === null) {
            return 'desktop';
        }

        if (preg_match('/Mobile|Android.*Mobile|iPhone|iPod/', $userAgent)) {
            return 'mobile';
        }

        if (preg_match('/iPad|Tablet|Android(?!.*Mobile)/', $userAgent)) {
            return 'tablet';
        }

        return 'desktop';
    }
}
