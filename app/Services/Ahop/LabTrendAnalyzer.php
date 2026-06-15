<?php

namespace App\Services\Ahop;

use App\Models\LabResult;
use App\Models\Patient;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Trend analysis for laboratory results (local analytics).
 */
class LabTrendAnalyzer
{
    /** Tests where higher numeric values often indicate worsening. */
    protected const HIGHER_IS_WORSE = [
        'glucose', 'creatinine', 'bun', 'wbc', 'alt', 'ast', 'bilirubin',
        'troponin', 'hba1c', 'cholesterol', 'ldl', 'triglyceride',
    ];

    /**
     * @return array<int, array<string, mixed>>
     */
    public function analyzeGlobal(int $months = 12, int $limit = 30): array
    {
        $cutoff = Carbon::now()->subMonths($months);

        $results = LabResult::query()
            ->with(['labOrder' => fn ($q) => $q->with('patient')])
            ->where('result_at', '>=', $cutoff)
            ->orderBy('result_at')
            ->get();

        return $this->buildTrends($results, $limit);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function analyzePatient(Patient $patient, int $months = 12): array
    {
        $cutoff = Carbon::now()->subMonths($months);

        $results = LabResult::query()
            ->whereHas('labOrder', fn ($q) => $q->where('patient_id', $patient->id))
            ->where('result_at', '>=', $cutoff)
            ->orderBy('result_at')
            ->get();

        return $this->buildTrends($results, 20, $patient);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function buildTrends(Collection $results, int $limit, ?Patient $patient = null): array
    {
        $grouped = $results->groupBy(fn (LabResult $r) => strtolower(trim($r->test_name)));

        $trends = [];

        foreach ($grouped as $testName => $series) {
            $numeric = $series->map(function (LabResult $r) {
                $value = $this->parseNumeric($r->result_value);
                if ($value === null) {
                    return null;
                }

                return [
                    'result' => $r,
                    'value' => $value,
                    'at' => $r->result_at,
                ];
            })->filter()->values();

            if ($numeric->count() < 2) {
                continue;
            }

            $latest = $numeric->last();
            $previous = $numeric->get($numeric->count() - 2);
            $delta = $latest['value'] - $previous['value'];
            $pct = $previous['value'] != 0
                ? round(($delta / abs($previous['value'])) * 100, 1)
                : null;

            $direction = $this->direction($testName, $delta, $latest['result']->flag, $previous['result']->flag);
            $interpretation = $this->interpret($direction, $testName, $latest['result']);

            $order = $latest['result']->labOrder;
            $trends[] = [
                'test_name' => $series->first()->test_name,
                'patient_id' => $order?->patient_id,
                'patient_name' => $order?->patient?->full_name,
                'patient_number' => $order?->patient?->patient_number,
                'latest_value' => $latest['value'],
                'previous_value' => $previous['value'],
                'unit' => $latest['result']->unit,
                'delta' => round($delta, 2),
                'delta_percent' => $pct,
                'direction' => $direction,
                'direction_label' => trans('admin/ai_insights/general.trend_'.$direction),
                'latest_flag' => $latest['result']->flag,
                'interpretation' => $interpretation,
                'points' => $numeric->map(fn ($p) => [
                    'at' => $p['at']?->format('Y-m-d'),
                    'value' => $p['value'],
                    'flag' => $p['result']->flag,
                ])->all(),
            ];
        }

        usort($trends, function ($a, $b) {
            $priority = ['worsening' => 3, 'stable' => 2, 'improving' => 1];
            $pa = $priority[$a['direction']] ?? 0;
            $pb = $priority[$b['direction']] ?? 0;
            if ($pa !== $pb) {
                return $pb <=> $pa;
            }

            return abs($b['delta_percent'] ?? 0) <=> abs($a['delta_percent'] ?? 0);
        });

        return array_slice($trends, 0, $limit);
    }

    protected function parseNumeric(?string $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (preg_match('/-?\d+(\.\d+)?/', str_replace(',', '', $value), $m)) {
            return (float) $m[0];
        }

        return null;
    }

    protected function direction(string $testName, float $delta, ?string $latestFlag, ?string $prevFlag): string
    {
        if ($latestFlag === 'critical' && $prevFlag !== 'critical') {
            return 'worsening';
        }

        if (in_array($latestFlag, ['high', 'low'], true) && $prevFlag === 'normal') {
            return 'worsening';
        }

        if ($latestFlag === 'normal' && in_array($prevFlag, ['high', 'low', 'critical'], true)) {
            return 'improving';
        }

        $higherWorse = $this->isHigherWorse($testName);

        if (abs($delta) < 0.01) {
            return 'stable';
        }

        if ($higherWorse) {
            return $delta > 0 ? 'worsening' : 'improving';
        }

        return $delta > 0 ? 'improving' : 'worsening';
    }

    protected function isHigherWorse(string $testName): bool
    {
        $name = strtolower($testName);
        foreach (self::HIGHER_IS_WORSE as $token) {
            if (str_contains($name, $token)) {
                return true;
            }
        }

        return false;
    }

    protected function interpret(string $direction, string $testName, LabResult $latest): string
    {
        if ($latest->flag === 'critical') {
            return trans('admin/ai_insights/general.trend_interp_critical');
        }

        return match ($direction) {
            'worsening' => trans('admin/ai_insights/general.trend_interp_worsening', ['test' => $testName]),
            'improving' => trans('admin/ai_insights/general.trend_interp_improving', ['test' => $testName]),
            default => trans('admin/ai_insights/general.trend_interp_stable', ['test' => $testName]),
        };
    }

    /**
     * Chart.js payloads for the lab trends UI.
     *
     * @param  array<int, array<string, mixed>>  $trends
     * @return array<string, mixed>
     */
    public function buildChartPayload(array $trends, int $maxLineCharts = 6): array
    {
        $summary = ['worsening' => 0, 'stable' => 0, 'improving' => 0];
        foreach ($trends as $trend) {
            $key = $trend['direction'] ?? 'stable';
            if (isset($summary[$key])) {
                $summary[$key]++;
            }
        }

        $lineCharts = [];
        foreach (array_slice($trends, 0, $maxLineCharts) as $index => $trend) {
            $points = $trend['points'] ?? [];
            if (count($points) < 2) {
                continue;
            }

            $title = $trend['test_name'];
            if (! empty($trend['patient_name'])) {
                $title .= ' — '.$trend['patient_name'];
            }

            $lineCharts[] = [
                'id' => 'ahopLabChart'.$index,
                'title' => $title,
                'labels' => array_column($points, 'at'),
                'values' => array_column($points, 'value'),
                'direction' => $trend['direction'],
                'unit' => $trend['unit'] ?? '',
            ];
        }

        return [
            'directionSummary' => [
                'labels' => [
                    trans('admin/ai_insights/general.trend_worsening'),
                    trans('admin/ai_insights/general.trend_stable'),
                    trans('admin/ai_insights/general.trend_improving'),
                ],
                'data' => [
                    $summary['worsening'],
                    $summary['stable'],
                    $summary['improving'],
                ],
                'colors' => ['#c62828', '#1565c0', '#2e7d32'],
            ],
            'lineCharts' => $lineCharts,
        ];
    }

    /**
     * Flat rows for CSV export (one row per result point).
     *
     * @param  array<int, array<string, mixed>>  $trends
     * @return array<int, array<int, string|float|null>>
     */
    public function flattenForCsv(array $trends): array
    {
        $rows = [];

        foreach ($trends as $trend) {
            foreach ($trend['points'] ?? [] as $point) {
                $rows[] = [
                    $trend['patient_number'] ?? '',
                    $trend['patient_name'] ?? '',
                    $trend['test_name'] ?? '',
                    $point['at'] ?? '',
                    $point['value'] ?? '',
                    $trend['unit'] ?? '',
                    $point['flag'] ?? '',
                    $trend['direction_label'] ?? $trend['direction'] ?? '',
                    $trend['delta_percent'] ?? '',
                    $trend['delta'] ?? '',
                    $trend['interpretation'] ?? '',
                ];
            }
        }

        return $rows;
    }

    /**
     * @return array<int, string>
     */
    public static function csvHeaders(): array
    {
        return [
            'Patient Number',
            'Patient Name',
            'Test Name',
            'Result Date',
            'Value',
            'Unit',
            'Flag',
            'Trend Direction',
            'Change %',
            'Change (absolute)',
            'Interpretation',
        ];
    }
}
