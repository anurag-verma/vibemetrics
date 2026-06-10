<x-mail::message>
# Welcome, {{ $user->name }}!

Thanks for signing up. You can start tracking your sites as soon as you verify your email address.

<x-mail::button :url="route('verification.notice')">
Verify your email
</x-mail::button>

If you did not create this account, you can ignore this message.

Thanks,<br>
{{ $appName }}
</x-mail::message>
