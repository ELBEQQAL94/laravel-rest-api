@component('mail::message')
# Hello {{ $user->name }}

you changed your email. Please verify your new email using this button below:


@component('mail::button', ['url' => route('verify', $user->verification_token)])
Verify Account
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
