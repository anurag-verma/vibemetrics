<x-mail::message>
# Welcome, {{ $user->name }}!

Your email is verified and your account is ready. Add your first site to start collecting privacy-friendly analytics.

<x-mail::button :url="$actionUrl">
Get started
</x-mail::button>

If you did not create this account, please contact support.

Thanks,<br>
{{ $appName }}
</x-mail::message>
