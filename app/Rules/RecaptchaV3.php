<?php

namespace App\Rules;

use App\Services\RecaptchaService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class RecaptchaV3 implements ValidationRule
{
    public function __construct(public string $action = '') {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $service = app(RecaptchaService::class);

        if (! $service->isEnabled()) {
            return;
        }

        $token = is_string($value) ? $value : null;

        if (! $service->verify($token, $this->action, request()->ip())) {
            $fail(__('Security verification failed. Please refresh the page and try again.'));
        }
    }
}
