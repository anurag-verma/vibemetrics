<x-mail::layout>
{{-- Header --}}
<x-slot:header>
<x-mail::header :url="config('app.url')">
<img
    src="{{ $branding['emailLogoUrl'] }}"
    class="logo"
    alt="{{ $branding['appName'] }}"
    style="display: block; height: auto; max-height: 56px; max-width: 220px; width: auto; margin: 0 auto;"
>
</x-mail::header>
</x-slot:header>

{{-- Body --}}
{!! $slot !!}

{{-- Subcopy --}}
@isset($subcopy)
<x-slot:subcopy>
<x-mail::subcopy>
{!! $subcopy !!}
</x-mail::subcopy>
</x-slot:subcopy>
@endisset

{{-- Footer --}}
<x-slot:footer>
<x-mail::footer>
© {{ date('Y') }} {{ $branding['appName'] }}. {{ __('All rights reserved.') }}
@if (!empty($branding['supportEmail']))
<br>{{ $branding['supportEmail'] }}
@endif
</x-mail::footer>
</x-slot:footer>
</x-mail::layout>
