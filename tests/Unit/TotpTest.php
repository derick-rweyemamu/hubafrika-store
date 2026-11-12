<?php

namespace Tests\Unit;

use App\Support\Auth\Totp;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class TotpTest extends TestCase
{
    private const RFC_SECRET = 'GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ'; // "12345678901234567890"

    public function test_it_encodes_and_decodes_rfc4648_base32_strings(): void
    {
        $this->assertSame('', Totp::encodeBase32(''));
        $this->assertSame('MY', Totp::encodeBase32('f'));
        $this->assertSame('MZXQ', Totp::encodeBase32('fo'));
        $this->assertSame('MZXW6', Totp::encodeBase32('foo'));
        $this->assertSame('MZXW6YTBOI', Totp::encodeBase32('foobar'));

        $this->assertSame('foobar', Totp::decodeBase32('mzxw 6ytb oi=='));
        $this->assertSame('12345678901234567890', Totp::decodeBase32(self::RFC_SECRET));
    }

    public function test_it_matches_rfc6238_sha1_test_vectors(): void
    {
        $this->assertSame('287082', Totp::codeAt(self::RFC_SECRET, 59));
        $this->assertSame('081804', Totp::codeAt(self::RFC_SECRET, 1111111109));
        $this->assertSame('050471', Totp::codeAt(self::RFC_SECRET, 1111111111));
        $this->assertSame('005924', Totp::codeAt(self::RFC_SECRET, 1234567890));
        $this->assertSame('279037', Totp::codeAt(self::RFC_SECRET, 2000000000));
    }

    public function test_verify_accepts_adjacent_window_and_blocks_replayed_steps(): void
    {
        // At t = 59, step = 1 and expected code = 287082.
        $this->assertSame(1, Totp::verify(self::RFC_SECRET, '287 082', timestamp: 65, window: 1));
        $this->assertNull(Totp::verify(self::RFC_SECRET, '287082', timestamp: 65, window: 0));
        $this->assertNull(Totp::verify(self::RFC_SECRET, '287082', timestamp: 59, window: 1, lastUsedStep: 1));
        $this->assertNull(Totp::verify(self::RFC_SECRET, '12345', timestamp: 59));
        $this->assertNull(Totp::verify(self::RFC_SECRET, 'abcdef', timestamp: 59));
    }

    public function test_provisioning_uri_and_formatted_secret_use_standard_totp_format(): void
    {
        $uri = Totp::provisioningUri('Hubafrika Shop', 'customer@example.com', self::RFC_SECRET);

        $this->assertSame(
            'otpauth://totp/Hubafrika%20Shop:customer%40example.com?secret=GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ&issuer=Hubafrika%20Shop&algorithm=SHA1&digits=6&period=30',
            $uri,
        );

        $this->assertSame('GEZD GNBV GY3T QOJQ GEZD GNBV GY3T QOJQ', Totp::formatSecret(self::RFC_SECRET));
    }

    public function test_short_or_invalid_secrets_throw_exceptions(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Totp::generateSecret(4);
    }
}
