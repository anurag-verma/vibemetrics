<x-mail::message>
# Password changed

Hi {{ $user->name }},

Your password was changed successfully. If you made this change, no further action is needed.

If you did not change your password, reset it immediately and contact support.

<x-mail::button :url="route('password.request')">
Reset password
</x-mail::button>

Thanks,<br>
{{ $appName }}
</x-mail::message>
