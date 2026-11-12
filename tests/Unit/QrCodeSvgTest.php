<?php

namespace Tests\Unit;

use App\Support\Auth\QrCodeSvg;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class QrCodeSvgTest extends TestCase
{
    public function test_it_renders_valid_svg_for_an_otpauth_provisioning_uri(): void
    {
        $uri = 'otpauth://totp/Hubafrika%20Shop:amina%40example.com?secret=GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ&issuer=Hubafrika%20Shop&algorithm=SHA1&digits=6&period=30';
        $svg = QrCodeSvg::render($uri, size: 220);

        $xml = simplexml_load_string($svg);

        $this->assertNotFalse($xml);
        $this->assertSame('svg', $xml->getName());
        $this->assertSame('220', (string) $xml['width']);
        $this->assertSame('220', (string) $xml['height']);
        $this->assertStringContainsString('<path d="', $svg);
    }

    public function test_it_builds_valid_finder_patterns_across_versions(): void
    {
        // Version 1 (21x21) for short string
        $v1 = QrCodeSvg::encodeMatrix('HELLO WORLD');
        $this->assertCount(21, $v1);
        $this->assertFinderPatternsIntact($v1, version: 1);

        // Version 7 (45x45, ~110 bytes triggers V7 which embeds 3x6 version info blocks)
        $v7 = QrCodeSvg::encodeMatrix(str_repeat('H', 110));
        $this->assertCount(45, $v7);
        $this->assertFinderPatternsIntact($v7, version: 7);

        // Version 8 (49x49, standard 143-byte otpauth URI)
        $otpAuthUri = 'otpauth://totp/Hubafrika%20Shop:customer%40example.com?secret=GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ&issuer=Hubafrika%20Shop&algorithm=SHA1&digits=6&period=30';
        $v8 = QrCodeSvg::encodeMatrix($otpAuthUri);
        $this->assertCount(49, $v8);
        $this->assertFinderPatternsIntact($v8, version: 8);

        // Version 10+ (>=180 bytes uses 16-bit byte length header)
        $longMatrix = QrCodeSvg::encodeMatrix(str_repeat('HUBAFRIKA-2FA-', 16));
        $this->assertGreaterThanOrEqual(57, count($longMatrix));
        $version = intdiv(count($longMatrix) - 17, 4);
        $this->assertFinderPatternsIntact($longMatrix, version: $version);
    }

    public function test_empty_payload_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);

        QrCodeSvg::encodeMatrix('');
    }

    /**
     * @param  array<int, array<int, int>>  $matrix
     */
    private function assertFinderPatternsIntact(array $matrix, int $version): void
    {
        $size = count($matrix);

        foreach ([[0, 0], [0, $size - 7], [$size - 7, 0]] as [$top, $left]) {
            $this->assertSame(1, $matrix[$top][$left]);
            $this->assertSame(1, $matrix[$top + 6][$left + 6]);
            $this->assertSame(0, $matrix[$top + 1][$left + 1]);
            $this->assertSame(1, $matrix[$top + 3][$left + 3]);
        }

        // Fixed dark module required by ISO/IEC 18004
        $this->assertSame(1, $matrix[(4 * $version) + 9][8]);
    }
}
