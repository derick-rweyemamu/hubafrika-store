<x-mail::message>
# {{ __('Two-factor authentication recovery') }}

{{ __('Hello :name,', ['name' => $user->name]) }}

{{ __('Use the single-use recovery code below to finish signing in to your account since you do not have access to your authenticator app:') }}

<x-mail::panel>
**{{ $formattedCode }}**
</x-mail::panel>

{{ __('This code expires in :minutes minutes and can only be used once.', ['minutes' => $expiresInMinutes]) }}

{{ __('If you did not try to sign in, someone may know your password. Please sign in to your account and change your password immediately.') }}

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
