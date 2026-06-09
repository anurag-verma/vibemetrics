@php
    $branding = $branding ?? app(\App\Services\BrandingService::class)->toArray();
@endphp
<x-mail::layout>
    {{-- Header --}}
    <x-slot:header>
        <x-mail::header :url="config('app.url')">
            {{ $branding['appName'] }}
        </x-mail::header>
    </x-slot:header>

    {{-- Body --}}
    {{ $slot }}

    {{-- Subcopy --}}
    @isset($subcopy)
        <x-slot:subcopy>
            <x-mail::subcopy>
                {{ $subcopy }}
            </x-mail::subcopy>
        </x-slot:subcopy>
    @endisset

    {{-- Footer --}}
    <x-slot:footer>
        <x-mail::footer>
            © {{ date('Y') }} {{ $branding['appName'] }}. @lang('All rights reserved.')
            @if (!empty($branding['supportEmail']))
            @lang('Support'): {{ $branding['supportEmail'] }}
            @endif
        </x-mail::footer>
    </x-slot:footer>
</x-mail::layout>
