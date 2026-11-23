<?php

namespace App\Services;

use App\Models\BusinessSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class RecaptchaService
{
    public const VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';

    /**
     * Load reCAPTCHA v3 settings from the database (admin-configured, no .env).
     *
     * @return array{enabled: bool, site_key: string, secret_key: string, min_score: float}
     */
    public function settings(): array
    {
        $stored = BusinessSetting::query()
            ->whereIn('type', [
                'recaptcha_enabled',
                'recaptcha_site_key',
                'recaptcha_secret_key',
                'recaptcha_min_score',
            ])
            ->pluck('value', 'type');

        $minScore = (float) ($stored->get('recaptcha_min_score') ?? 0.5);

        return [
            'enabled' => (string) ($stored->get('recaptcha_enabled') ?? '0') === '1',
            'site_key' => trim((string) ($stored->get('recaptcha_site_key') ?? '')),
            'secret_key' => trim((string) ($stored->get('recaptcha_secret_key') ?? '')),
            'min_score' => max(0.0, min(1.0, $minScore)),
        ];
    }

    public function isEnabled(): bool
    {
        $settings = $this->settings();

        return $settings['enabled']
            && $settings['site_key'] !== ''
            && $settings['secret_key'] !== '';
    }

    public function siteKey(): string
    {
        return $this->settings()['site_key'];
    }

    public function verify(?string $token, string $expectedAction = '', ?string $remoteIp = null): bool
    {
        $settings = $this->settings();

        if (! $settings['enabled'] || $settings['site_key'] === '' || $settings['secret_key'] === '') {
            return true;
        }

        if ($token === null || trim($token) === '') {
            return false;
        }

        try {
            $payload = [
                'secret' => $settings['secret_key'],
                'response' => trim($token),
            ];

            if ($remoteIp !== null && $remoteIp !== '') {
                $payload['remoteip'] = $remoteIp;
            }

            $response = Http::asForm()
                ->timeout(10)
                ->post(self::VERIFY_URL, $payload);

            if (! $response->successful()) {
                Log::warning('reCAPTCHA verification HTTP failure', [
                    'status' => $response->status(),
                ]);

                return false;
            }

            $data = $response->json();

            if (! is_array($data) || ($data['success'] ?? false) !== true) {
                Log::info('reCAPTCHA verification rejected', [
                    'errors' => $data['error-codes'] ?? [],
                    'action' => $expectedAction,
                ]);

                return false;
            }

            if ($expectedAction !== '' && isset($data['action']) && $data['action'] !== $expectedAction) {
                Log::info('reCAPTCHA action mismatch', [
                    'expected' => $expectedAction,
                    'actual' => $data['action'],
                ]);

                return false;
            }

            $score = (float) ($data['score'] ?? 0.0);

            if ($score < $settings['min_score']) {
                Log::info('reCAPTCHA score below threshold', [
                    'score' => $score,
                    'min_score' => $settings['min_score'],
                    'action' => $expectedAction,
                ]);

                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('reCAPTCHA verification exception: '.$e->getMessage());

            return false;
        }
    }

    /**
     * @throws ValidationException
     */
    public function verifyOrFail(?string $token, string $expectedAction = '', string $errorField = 'recaptcha'): void
    {
        if (! $this->verify($token, $expectedAction, request()->ip())) {
            throw ValidationException::withMessages([
                $errorField => __('Security verification failed. Please refresh the page and try again.'),
            ]);
        }
    }
}
