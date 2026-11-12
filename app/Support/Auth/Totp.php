<?php

namespace App\Support\Auth;

use Illuminate\Support\Carbon;
use InvalidArgumentException;

/**
 * Pure-PHP RFC 4648 Base32 & RFC 6238 TOTP implementation (HMAC-SHA1, 6 digits, 30s period).
 */
class Totp
{
    public const PERIOD = 30;

    public const DIGITS = 6;

    private const BASE32_ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    /**
     * Generate a cryptographically random Base32 secret key (default 160 bits = 32 Base32 chars).
     */
    public static function generateSecret(int $bytes = 20): string
    {
        if ($bytes < 10) {
            throw new InvalidArgumentException('TOTP secret must be at least 10 bytes.');
        }

        return self::encodeBase32(random_bytes($bytes));
    }

    /**
     * Group a Base32 secret into readable 4-character blocks for manual authenticator entry.
     */
    public static function formatSecret(string $secret): string
    {
        $clean = strtoupper(preg_replace('/[\s=-]+/', '', $secret) ?? '');

        return trim(chunk_split($clean, 4, ' '));
    }

    /**
     * Build the otpauth:// provisioning URI scanned by authenticator apps.
     */
    public static function provisioningUri(string $issuer, string $accountName, string $secret): string
    {
        $cleanIssuer = trim($issuer) !== '' ? trim($issuer) : 'Application';
        $cleanAccount = trim($accountName) !== '' ? trim($accountName) : 'user';
        $cleanSecret = strtoupper(preg_replace('/[\s=-]+/', '', $secret) ?? '');

        $label = rawurlencode($cleanIssuer).':'.rawurlencode($cleanAccount);

        $query = http_build_query([
            'secret' => $cleanSecret,
            'issuer' => $cleanIssuer,
            'algorithm' => 'SHA1',
            'digits' => self::DIGITS,
            'period' => self::PERIOD,
        ], '', '&', PHP_QUERY_RFC3986);

        return "otpauth://totp/{$label}?{$query}";
    }

    /**
     * Compute the 6-digit TOTP code for a given secret and Unix timestamp.
     */
    public static function codeAt(string $secret, int $timestamp): string
    {
        $step = (int) floor($timestamp / self::PERIOD);

        return self::codeForStep($secret, $step);
    }

    /**
     * Compute the 6-digit TOTP code for a specific discrete time step.
     */
    public static function codeForStep(string $secret, int $step): string
    {
        $key = self::decodeBase32($secret);
        $counter = pack('J', $step);
        $hash = hash_hmac('sha1', $counter, $key, true);
        $offset = ord($hash[19]) & 0x0F;

        $binary = ((ord($hash[$offset]) & 0x7F) << 24)
            | ((ord($hash[$offset + 1]) & 0xFF) << 16)
            | ((ord($hash[$offset + 2]) & 0xFF) << 8)
            | (ord($hash[$offset + 3]) & 0xFF);

        $otp = $binary % (10 ** self::DIGITS);

        return str_pad((string) $otp, self::DIGITS, '0', STR_PAD_LEFT);
    }

    /**
     * Verify a user-submitted 6-digit TOTP code with optional clock-drift window and replay guard.
     *
     * Returns the matched discrete time step on success, or null when invalid or already replayed.
     */
    public static function verify(
        string $secret,
        string $code,
        ?int $timestamp = null,
        int $window = 1,
        ?int $lastUsedStep = null,
    ): ?int {
        $normalized = preg_replace('/[\s-]+/', '', trim($code)) ?? '';

        if (! preg_match('/^\d{'.self::DIGITS.'}$/', $normalized)) {
            return null;
        }

        $now = $timestamp ?? Carbon::now()->getTimestamp();
        $currentStep = (int) floor($now / self::PERIOD);

        for ($offset = -$window; $offset <= $window; $offset++) {
            $candidateStep = $currentStep + $offset;

            if ($candidateStep < 0) {
                continue;
            }

            if ($lastUsedStep !== null && $candidateStep <= $lastUsedStep) {
                continue;
            }

            $expected = self::codeForStep($secret, $candidateStep);

            if (hash_equals($expected, $normalized)) {
                return $candidateStep;
            }
        }

        return null;
    }

    /**
     * Encode arbitrary binary bytes as unpadded RFC 4648 Base32.
     */
    public static function encodeBase32(string $binary): string
    {
        if ($binary === '') {
            return '';
        }

        $buffer = 0;
        $bitsLeft = 0;
        $output = '';
        $length = strlen($binary);

        for ($i = 0; $i < $length; $i++) {
            $buffer = ($buffer << 8) | ord($binary[$i]);
            $bitsLeft += 8;

            while ($bitsLeft >= 5) {
                $bitsLeft -= 5;
                $output .= self::BASE32_ALPHABET[($buffer >> $bitsLeft) & 0x1F];
            }
        }

        if ($bitsLeft > 0) {
            $output .= self::BASE32_ALPHABET[($buffer << (5 - $bitsLeft)) & 0x1F];
        }

        return $output;
    }

    /**
     * Decode an RFC 4648 Base32 string (case-insensitive, ignoring spaces and "=" padding).
     */
    public static function decodeBase32(string $encoded): string
    {
        $clean = strtoupper(preg_replace('/[\s=]+/', '', $encoded) ?? '');

        if ($clean === '') {
            throw new InvalidArgumentException('Base32 secret must not be empty.');
        }

        $buffer = 0;
        $bitsLeft = 0;
        $output = '';
        $length = strlen($clean);

        for ($i = 0; $i < $length; $i++) {
            $index = strpos(self::BASE32_ALPHABET, $clean[$i]);

            if ($index === false) {
                throw new InvalidArgumentException('Invalid Base32 character in TOTP secret.');
            }

            $buffer = ($buffer << 5) | $index;
            $bitsLeft += 5;

            if ($bitsLeft >= 8) {
                $bitsLeft -= 8;
                $output .= chr(($buffer >> $bitsLeft) & 0xFF);
            }
        }

        return $output;
    }
}
