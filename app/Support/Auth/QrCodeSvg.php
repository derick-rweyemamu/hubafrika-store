<?php

namespace App\Support\Auth;

use InvalidArgumentException;

/**
 * Pure-PHP ISO/IEC 18004 QR Code SVG generator (Byte mode, Error Correction Level M, Versions 1-15).
 */
class QrCodeSvg
{
    private const GF_POLY = 0x11D;

    /**
     * Version specs for ECC Level M:
     * [totalDataCodewords, ecPerBlock, g1Blocks, g1Data, g2Blocks, g2Data, remainderBits].
     *
     * @var array<int, array{0: int, 1: int, 2: int, 3: int, 4: int, 5: int, 6: int}>
     */
    private const VERSION_SPECS = [
        1 => [16, 10, 1, 16, 0, 0, 0],
        2 => [28, 16, 1, 28, 0, 0, 7],
        3 => [44, 26, 1, 44, 0, 0, 7],
        4 => [64, 18, 2, 32, 0, 0, 7],
        5 => [86, 24, 2, 43, 0, 0, 7],
        6 => [108, 16, 4, 27, 0, 0, 7],
        7 => [124, 18, 4, 31, 0, 0, 0],
        8 => [154, 22, 2, 38, 2, 39, 0],
        9 => [182, 22, 3, 36, 2, 37, 0],
        10 => [216, 26, 4, 43, 1, 44, 0],
        11 => [254, 30, 1, 50, 4, 51, 0],
        12 => [290, 22, 6, 36, 2, 37, 0],
        13 => [334, 22, 8, 37, 1, 38, 0],
        14 => [365, 24, 4, 40, 5, 41, 3],
        15 => [415, 24, 5, 41, 5, 42, 3],
    ];

    /**
     * ISO/IEC 18004 Annex E alignment pattern center coordinates per version.
     *
     * @var array<int, list<int>>
     */
    private const ALIGNMENT_POSITIONS = [
        1 => [],
        2 => [6, 18],
        3 => [6, 22],
        4 => [6, 26],
        5 => [6, 30],
        6 => [6, 34],
        7 => [6, 22, 38],
        8 => [6, 24, 42],
        9 => [6, 26, 46],
        10 => [6, 28, 50],
        11 => [6, 30, 54],
        12 => [6, 32, 58],
        13 => [6, 34, 62],
        14 => [6, 26, 46, 66],
        15 => [6, 26, 48, 70],
    ];

    /**
     * Render a crisp standalone SVG QR code with a quiet zone.
     */
    public static function render(string $payload, int $size = 200, int $quietZone = 4): string
    {
        $matrix = self::encodeMatrix($payload);
        $modules = count($matrix);
        $totalModules = $modules + ($quietZone * 2);

        $path = '';

        for ($r = 0; $r < $modules; $r++) {
            for ($c = 0; $c < $modules; $c++) {
                if ($matrix[$r][$c] !== 1) {
                    continue;
                }

                $x = $c + $quietZone;
                $y = $r + $quietZone;
                $path .= "M{$x},{$y}h1v1h-1z";
            }
        }

        return sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 %d %d" width="%d" height="%d" role="img" aria-label="%s" shape-rendering="crispEdges"><rect width="100%%" height="100%%" fill="#ffffff"/><path d="%s" fill="#0f172a"/></svg>',
            $totalModules,
            $totalModules,
            $size,
            $size,
            htmlspecialchars('Authenticator setup QR code', ENT_QUOTES, 'UTF-8'),
            $path,
        );
    }

    /**
     * Build the encoded binary matrix (0 = light, 1 = dark) for the given payload.
     *
     * @return array<int, array<int, int>>
     */
    public static function encodeMatrix(string $payload): array
    {
        if ($payload === '') {
            throw new InvalidArgumentException('QR payload cannot be empty.');
        }

        $bytes = array_values(unpack('C*', $payload) ?: []);
        $version = self::selectVersion(count($bytes));
        $spec = self::VERSION_SPECS[$version];
        $codewords = self::encodeCodewords($bytes, $version, $spec);
        $bits = self::buildBitStream($codewords, $spec[6]);

        [$modules, $reserved] = self::initializeFunctionMatrix($version);
        self::placeDataBits($modules, $reserved, $bits);

        $bestMask = 0;
        $bestScore = PHP_INT_MAX;

        for ($mask = 0; $mask < 8; $mask++) {
            $candidate = self::applyMask($modules, $reserved, $mask);
            self::writeFormatInfo($candidate, $mask);

            if ($version >= 7) {
                self::writeVersionInfo($candidate, $version);
            }

            $score = self::evaluatePenalty($candidate);

            if ($score < $bestScore) {
                $bestScore = $score;
                $bestMask = $mask;
            }
        }

        $final = self::applyMask($modules, $reserved, $bestMask);
        self::writeFormatInfo($final, $bestMask);

        if ($version >= 7) {
            self::writeVersionInfo($final, $version);
        }

        return $final;
    }

    /**
     * Choose the smallest QR version whose ECC-M byte capacity accommodates $byteLength bytes.
     */
    private static function selectVersion(int $byteLength): int
    {
        foreach (self::VERSION_SPECS as $version => $spec) {
            $headerBits = $version >= 10 ? 20 : 12; // 4 mode bits + 8 or 16 count bits
            $capacityBytes = intdiv(($spec[0] * 8) - $headerBits, 8);

            if ($byteLength <= $capacityBytes) {
                return $version;
            }
        }

        throw new InvalidArgumentException('Payload exceeds supported QR code version capacity.');
    }

    /**
     * Encode data codewords and Reed-Solomon error-correction codewords, interleaved per block.
     *
     * @param  list<int>  $bytes
     * @param  array{0: int, 1: int, 2: int, 3: int, 4: int, 5: int, 6: int}  $spec
     * @return list<int>
     */
    private static function encodeCodewords(array $bytes, int $version, array $spec): array
    {
        $totalDataBytes = $spec[0];
        $ecPerBlock = $spec[1];
        $bitBuffer = [];

        // Mode indicator: 0100 (Byte mode)
        self::appendBits($bitBuffer, 0b0100, 4);

        // Character count indicator: 8 bits (V1-9) or 16 bits (V10+)
        $countBits = $version >= 10 ? 16 : 8;
        self::appendBits($bitBuffer, count($bytes), $countBits);

        foreach ($bytes as $byte) {
            self::appendBits($bitBuffer, $byte, 8);
        }

        $capacityBits = $totalDataBytes * 8;
        $terminator = min(4, $capacityBits - count($bitBuffer));
        self::appendBits($bitBuffer, 0, $terminator);

        while ((count($bitBuffer) % 8) !== 0) {
            $bitBuffer[] = 0;
        }

        $dataCodewords = [];
        $bitCount = count($bitBuffer);

        for ($i = 0; $i < $bitCount; $i += 8) {
            $byte = 0;

            for ($b = 0; $b < 8; $b++) {
                $byte = ($byte << 1) | $bitBuffer[$i + $b];
            }

            $dataCodewords[] = $byte;
        }

        $padBytes = [0xEC, 0x11];
        $padIndex = 0;

        while (count($dataCodewords) < $totalDataBytes) {
            $dataCodewords[] = $padBytes[$padIndex];
            $padIndex ^= 1;
        }

        $blocks = [];
        $offset = 0;

        for ($i = 0; $i < $spec[2]; $i++) {
            $blocks[] = array_slice($dataCodewords, $offset, $spec[3]);
            $offset += $spec[3];
        }

        for ($i = 0; $i < $spec[4]; $i++) {
            $blocks[] = array_slice($dataCodewords, $offset, $spec[5]);
            $offset += $spec[5];
        }

        $ecBlocks = [];
        $generator = self::rsGeneratorPoly($ecPerBlock);

        foreach ($blocks as $block) {
            $ecBlocks[] = self::rsComputeRemainder($block, $generator);
        }

        $interleaved = [];
        $maxDataLen = max(array_map('count', $blocks));

        for ($i = 0; $i < $maxDataLen; $i++) {
            foreach ($blocks as $block) {
                if (isset($block[$i])) {
                    $interleaved[] = $block[$i];
                }
            }
        }

        for ($i = 0; $i < $ecPerBlock; $i++) {
            foreach ($ecBlocks as $ecBlock) {
                $interleaved[] = $ecBlock[$i];
            }
        }

        return $interleaved;
    }

    /**
     * Expand interleaved codewords + remainder bits into an ordered bit list.
     *
     * @param  list<int>  $codewords
     * @return list<int>
     */
    private static function buildBitStream(array $codewords, int $remainderBits): array
    {
        $bits = [];

        foreach ($codewords as $byte) {
            self::appendBits($bits, $byte, 8);
        }

        for ($i = 0; $i < $remainderBits; $i++) {
            $bits[] = 0;
        }

        return $bits;
    }

    /**
     * Create the blank QR grid and populate all fixed function patterns and reserved coordinates.
     *
     * @return array{0: array<int, array<int, int>>, 1: array<int, array<int, bool>>}
     */
    private static function initializeFunctionMatrix(int $version): array
    {
        $size = ($version * 4) + 17;
        $modules = array_fill(0, $size, array_fill(0, $size, 0));
        $reserved = array_fill(0, $size, array_fill(0, $size, false));

        // Three finder patterns + 1-module quiet separator around each
        self::placeFinder($modules, $reserved, 0, 0, $size);
        self::placeFinder($modules, $reserved, 0, $size - 7, $size);
        self::placeFinder($modules, $reserved, $size - 7, 0, $size);

        // Dark module required by ISO/IEC 18004 section 6.3.9
        $modules[(4 * $version) + 9][8] = 1;
        $reserved[(4 * $version) + 9][8] = true;

        // Alignment patterns (skip corners overlapping finder separators)
        $centers = self::ALIGNMENT_POSITIONS[$version];

        foreach ($centers as $rCenter) {
            foreach ($centers as $cCenter) {
                if ($reserved[$rCenter][$cCenter]) {
                    continue;
                }

                self::placeAlignment($modules, $reserved, $rCenter, $cCenter);
            }
        }

        // Timing patterns along row 6 and col 6
        for ($i = 8; $i < $size - 8; $i++) {
            $bit = ($i % 2) === 0 ? 1 : 0;

            if (! $reserved[6][$i]) {
                $modules[6][$i] = $bit;
                $reserved[6][$i] = true;
            }

            if (! $reserved[$i][6]) {
                $modules[$i][6] = $bit;
                $reserved[$i][6] = true;
            }
        }

        // Reserve format information zones (populated once optimal mask is selected)
        for ($i = 0; $i <= 8; $i++) {
            if ($i !== 6) {
                $reserved[8][$i] = true;
                $reserved[$i][8] = true;
            }
        }

        for ($i = 0; $i < 8; $i++) {
            $reserved[8][$size - 1 - $i] = true;

            if ($i < 7) {
                $reserved[$size - 1 - $i][8] = true;
            }
        }

        // Reserve version information areas for V7+
        if ($version >= 7) {
            for ($r = 0; $r < 6; $r++) {
                for ($c = $size - 11; $c <= $size - 9; $c++) {
                    $reserved[$r][$c] = true;
                    $reserved[$c][$r] = true;
                }
            }
        }

        return [$modules, $reserved];
    }

    /**
     * @param  array<int, array<int, int>>  $modules
     * @param  array<int, array<int, bool>>  $reserved
     */
    private static function placeFinder(array &$modules, array &$reserved, int $top, int $left, int $size): void
    {
        for ($r = -1; $r <= 7; $r++) {
            for ($c = -1; $c <= 7; $c++) {
                $row = $top + $r;
                $col = $left + $c;

                if ($row < 0 || $row >= $size || $col < 0 || $col >= $size) {
                    continue;
                }

                $isBorderOrCore = ($r >= 0 && $r <= 6 && $c >= 0 && $c <= 6)
                    && ($r === 0 || $r === 6 || $c === 0 || $c === 6 || ($r >= 2 && $r <= 4 && $c >= 2 && $c <= 4));

                $modules[$row][$col] = $isBorderOrCore ? 1 : 0;
                $reserved[$row][$col] = true;
            }
        }
    }

    /**
     * @param  array<int, array<int, int>>  $modules
     * @param  array<int, array<int, bool>>  $reserved
     */
    private static function placeAlignment(array &$modules, array &$reserved, int $rCenter, int $cCenter): void
    {
        for ($dr = -2; $dr <= 2; $dr++) {
            for ($dc = -2; $dc <= 2; $dc++) {
                $dist = max(abs($dr), abs($dc));
                $modules[$rCenter + $dr][$cCenter + $dc] = ($dist === 2 || $dist === 0) ? 1 : 0;
                $reserved[$rCenter + $dr][$cCenter + $dc] = true;
            }
        }
    }

    /**
     * Walk the grid in upward/downward 2-column zigzag pairs, skipping vertical timing column 6.
     *
     * @param  array<int, array<int, int>>  $modules
     * @param  array<int, array<int, bool>>  $reserved
     * @param  list<int>  $bits
     */
    private static function placeDataBits(array &$modules, array $reserved, array $bits): void
    {
        $size = count($modules);
        $bitIdx = 0;
        $totalBits = count($bits);
        $goingUp = true;

        for ($col = $size - 1; $col >= 1; $col -= 2) {
            if ($col === 6) {
                $col--;
            }

            for ($step = 0; $step < $size; $step++) {
                $row = $goingUp ? ($size - 1 - $step) : $step;

                for ($dc = 0; $dc < 2; $dc++) {
                    $c = $col - $dc;

                    if ($reserved[$row][$c]) {
                        continue;
                    }

                    $modules[$row][$c] = $bitIdx < $totalBits ? $bits[$bitIdx] : 0;
                    $bitIdx++;
                }
            }

            $goingUp = ! $goingUp;
        }
    }

    /**
     * Apply one of the 8 ISO/IEC 18004 mask patterns to non-function modules only.
     *
     * @param  array<int, array<int, int>>  $modules
     * @param  array<int, array<int, bool>>  $reserved
     * @return array<int, array<int, int>>
     */
    private static function applyMask(array $modules, array $reserved, int $mask): array
    {
        $size = count($modules);
        $masked = $modules;

        for ($r = 0; $r < $size; $r++) {
            for ($c = 0; $c < $size; $c++) {
                if ($reserved[$r][$c]) {
                    continue;
                }

                $invert = match ($mask) {
                    0 => (($r + $c) % 2) === 0,
                    1 => ($r % 2) === 0,
                    2 => ($c % 3) === 0,
                    3 => (($r + $c) % 3) === 0,
                    4 => ((intdiv($r, 2) + intdiv($c, 3)) % 2) === 0,
                    5 => (($r * $c) % 2) + (($r * $c) % 3) === 0,
                    6 => (((($r * $c) % 2) + (($r * $c) % 3)) % 2) === 0,
                    7 => (((($r + $c) % 2) + (($r * $c) % 3)) % 2) === 0,
                    default => false,
                };

                if ($invert) {
                    $masked[$r][$c] ^= 1;
                }
            }
        }

        return $masked;
    }

    /**
     * Encode BCH(15,5) format bits for ECC Level M (0b00) and write both copies.
     *
     * @param  array<int, array<int, int>>  $modules
     */
    private static function writeFormatInfo(array &$modules, int $mask): void
    {
        $size = count($modules);
        $data = (0b00 << 3) | $mask; // ECC M = 00
        $rem = $data << 10;

        for ($bit = 4; $bit >= 0; $bit--) {
            if (($rem & (1 << ($bit + 10))) !== 0) {
                $rem ^= 0x537 << $bit;
            }
        }

        $format = (($data << 10) | $rem) ^ 0x5412;

        // First copy: horizontal around top-left finder
        $rowCoords = [0, 1, 2, 3, 4, 5, 7, 8];

        foreach ($rowCoords as $idx => $col) {
            $modules[8][$col] = ($format >> (14 - $idx)) & 1;
        }

        $colCoords = [7, 5, 4, 3, 2, 1, 0];

        foreach ($colCoords as $idx => $row) {
            $modules[$row][8] = ($format >> (14 - (8 + $idx))) & 1;
        }

        // Second copy: bottom-left vertical and top-right horizontal
        for ($i = 0; $i < 7; $i++) {
            $modules[$size - 1 - $i][8] = ($format >> (14 - $i)) & 1;
        }

        for ($i = 0; $i < 8; $i++) {
            $modules[8][$size - 8 + $i] = ($format >> (7 - $i)) & 1;
        }
    }

    /**
     * Encode BCH(18,6) version bits for V7+ and write both 3x6 blocks.
     *
     * @param  array<int, array<int, int>>  $modules
     */
    private static function writeVersionInfo(array &$modules, int $version): void
    {
        $size = count($modules);
        $rem = $version << 12;

        for ($bit = 5; $bit >= 0; $bit--) {
            if (($rem & (1 << ($bit + 12))) !== 0) {
                $rem ^= 0x1F25 << $bit;
            }
        }

        $encoded = ($version << 12) | $rem;

        for ($i = 0; $i < 18; $i++) {
            $bit = ($encoded >> $i) & 1;
            $a = intdiv($i, 3);
            $b = $size - 11 + ($i % 3);

            $modules[$a][$b] = $bit;
            $modules[$b][$a] = $bit;
        }
    }

    /**
     * Score the matrix according to ISO/IEC 18004 mask penalty rules N1-N4 (lower is better).
     *
     * @param  array<int, array<int, int>>  $modules
     */
    private static function evaluatePenalty(array $modules): int
    {
        $size = count($modules);
        $penalty = 0;
        $darkCount = 0;

        // N1: 5+ same-colored consecutive modules in rows and columns
        for ($r = 0; $r < $size; $r++) {
            $rowRunColor = $modules[$r][0];
            $rowRunLen = 0;
            $colRunColor = $modules[0][$r];
            $colRunLen = 0;

            for ($c = 0; $c < $size; $c++) {
                $darkCount += $modules[$r][$c];

                if ($modules[$r][$c] === $rowRunColor) {
                    $rowRunLen++;
                } else {
                    if ($rowRunLen >= 5) {
                        $penalty += 3 + ($rowRunLen - 5);
                    }
                    $rowRunColor = $modules[$r][$c];
                    $rowRunLen = 1;
                }

                if ($modules[$c][$r] === $colRunColor) {
                    $colRunLen++;
                } else {
                    if ($colRunLen >= 5) {
                        $penalty += 3 + ($colRunLen - 5);
                    }
                    $colRunColor = $modules[$c][$r];
                    $colRunLen = 1;
                }
            }

            if ($rowRunLen >= 5) {
                $penalty += 3 + ($rowRunLen - 5);
            }

            if ($colRunLen >= 5) {
                $penalty += 3 + ($colRunLen - 5);
            }
        }

        // N2: 2x2 uniform blocks
        for ($r = 0; $r < $size - 1; $r++) {
            for ($c = 0; $c < $size - 1; $c++) {
                $sum = $modules[$r][$c] + $modules[$r][$c + 1] + $modules[$r + 1][$c] + $modules[$r + 1][$c + 1];

                if ($sum === 0 || $sum === 4) {
                    $penalty += 3;
                }
            }
        }

        // N3: 1:1:3:1:1 finder-like patterns in rows and columns
        $finderPatternA = [1, 0, 1, 1, 1, 0, 1, 0, 0, 0, 0];
        $finderPatternB = [0, 0, 0, 0, 1, 0, 1, 1, 1, 0, 1];

        for ($r = 0; $r < $size; $r++) {
            for ($c = 0; $c <= $size - 11; $c++) {
                $rowSlice = array_slice($modules[$r], $c, 11);

                if ($rowSlice === $finderPatternA || $rowSlice === $finderPatternB) {
                    $penalty += 40;
                }

                $colSlice = [];

                for ($k = 0; $k < 11; $k++) {
                    $colSlice[] = $modules[$c + $k][$r];
                }

                if ($colSlice === $finderPatternA || $colSlice === $finderPatternB) {
                    $penalty += 40;
                }
            }
        }

        // N4: balance of dark modules vs 50%
        $percent = ($darkCount * 100) / ($size * $size);
        $prevFive = (int) floor($percent / 5) * 5;
        $nextFive = (int) ceil($percent / 5) * 5;
        $penalty += min(intdiv(abs($prevFive - 50), 5), intdiv(abs($nextFive - 50), 5)) * 10;

        return $penalty;
    }

    /**
     * @param  list<int>  $bits
     */
    private static function appendBits(array &$bits, int $value, int $length): void
    {
        for ($i = $length - 1; $i >= 0; $i--) {
            $bits[] = ($value >> $i) & 1;
        }
    }

    /**
     * Compute the Reed-Solomon generator polynomial over GF(256) of degree $ecCount.
     *
     * @return list<int>
     */
    private static function rsGeneratorPoly(int $ecCount): array
    {
        $poly = [1];

        for ($i = 0; $i < $ecCount; $i++) {
            $poly = self::rsPolyMultiply($poly, [1, self::gfPow2($i)]);
        }

        return $poly;
    }

    /**
     * @param  list<int>  $a
     * @param  list<int>  $b
     * @return list<int>
     */
    private static function rsPolyMultiply(array $a, array $b): array
    {
        $result = array_fill(0, count($a) + count($b) - 1, 0);

        foreach ($a as $i => $coeffA) {
            foreach ($b as $j => $coeffB) {
                $result[$i + $j] ^= self::gfMultiply($coeffA, $coeffB);
            }
        }

        return $result;
    }

    /**
     * @param  list<int>  $data
     * @param  list<int>  $generator
     * @return list<int>
     */
    private static function rsComputeRemainder(array $data, array $generator): array
    {
        $ecCount = count($generator) - 1;
        $buffer = array_merge($data, array_fill(0, $ecCount, 0));
        $dataLen = count($data);

        for ($i = 0; $i < $dataLen; $i++) {
            $factor = $buffer[$i];

            if ($factor === 0) {
                continue;
            }

            foreach ($generator as $j => $coeff) {
                $buffer[$i + $j] ^= self::gfMultiply($coeff, $factor);
            }
        }

        return array_slice($buffer, $dataLen);
    }

    private static function gfPow2(int $exponent): int
    {
        $val = 1;

        for ($i = 0; $i < $exponent; $i++) {
            $val <<= 1;

            if (($val & 0x100) !== 0) {
                $val ^= self::GF_POLY;
            }
        }

        return $val;
    }

    private static function gfMultiply(int $a, int $b): int
    {
        $result = 0;

        for ($i = 0; $i < 8 && $b > 0; $i++) {
            if (($b & 1) !== 0) {
                $result ^= $a;
            }

            $a <<= 1;

            if (($a & 0x100) !== 0) {
                $a ^= self::GF_POLY;
            }

            $b >>= 1;
        }

        return $result;
    }
}
