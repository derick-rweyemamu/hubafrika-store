<?php

namespace App\Livewire\Concerns;

/**
 * Chart maths shared by the dashboard and the reports screen. Series are drawn
 * on a 0-100 grid so the SVG stretches with its container and no JavaScript
 * charting library is needed.
 */
trait DrawsCharts
{
    /**
     * Describe the movement between two periods as a signed percentage.
     *
     * @return array{direction: string, label: string}
     */
    protected function delta(float $current, float $previous): array
    {
        if ($previous <= 0.0) {
            return $current > 0.0
                ? ['direction' => 'up', 'label' => 'New']
                : ['direction' => 'flat', 'label' => '0%'];
        }

        $change = ($current - $previous) / $previous * 100;

        return [
            'direction' => match (true) {
                $change > 0.05 => 'up',
                $change < -0.05 => 'down',
                default => 'flat',
            },
            'label' => number_format(abs($change), 1).'%',
        ];
    }

    /**
     * Draw a revenue series as a line plus the closed area beneath it.
     *
     * @param  array<int, array{label: string, revenue: float}>  $points
     * @return array{line: string, area: string, max: float, labels: array<int, string>}
     */
    protected function chartGeometry(array $points): array
    {
        $count = count($points);
        $values = array_column($points, 'revenue');
        $max = $values === [] ? 0.0 : (float) max($values);
        $coordinates = [];

        foreach ($values as $index => $value) {
            $x = $count <= 1 ? 0.0 : $index * 100 / ($count - 1);
            $ratio = $max > 0.0 ? $value / $max : 0.0;
            $coordinates[] = round($x, 2).','.round(92 - $ratio * 84, 2);
        }

        $line = $coordinates === [] ? '' : 'M'.implode(' L', $coordinates);

        $labelIndexes = $count <= 1
            ? [0]
            : array_values(array_unique([
                0,
                (int) round(($count - 1) * 0.25),
                (int) round(($count - 1) * 0.5),
                (int) round(($count - 1) * 0.75),
                $count - 1,
            ]));

        return [
            'line' => $line,
            'area' => $line === '' ? '' : $line.' L100,100 L0,100 Z',
            'max' => $max,
            'labels' => array_map(static fn (int $index): string => $points[$index]['label'], $labelIndexes),
        ];
    }

    /**
     * Build a compact trend path for a KPI card.
     *
     * @param  array<int, float|int>  $values
     */
    protected function sparkline(array $values): string
    {
        $count = count($values);

        if ($count === 0) {
            return '';
        }

        $max = max($values);
        $min = min($values);
        $range = $max - $min;
        $coordinates = [];

        foreach ($values as $index => $value) {
            $x = $count === 1 ? 50.0 : $index * 100 / ($count - 1);
            $ratio = $range > 0 ? ($value - $min) / $range : 0.5;
            $coordinates[] = round($x, 2).','.round(96 - $ratio * 88, 2);
        }

        return 'M'.implode(' L', $coordinates);
    }
}
