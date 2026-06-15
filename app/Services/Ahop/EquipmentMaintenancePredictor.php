<?php

namespace App\Services\Ahop;

use App\Models\Asset;
use App\Models\Maintenance;
use Carbon\Carbon;

/**
 * Predictive maintenance scoring for medical equipment (local analytics).
 */
class EquipmentMaintenancePredictor
{
    protected const CALIBRATION_INTERVAL_MONTHS = 12;

    protected const REPAIR_ESCALATION_DAYS = 30;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function predictAll(int $limit = 40): array
    {
        $assets = Asset::query()
            ->with(['model', 'status', 'location', 'maintenances'])
            ->orderBy('asset_tag')
            ->get();

        $predictions = $assets->map(fn (Asset $asset) => $this->predict($asset))
            ->sortByDesc('priority_score')
            ->values()
            ->take($limit)
            ->all();

        return $predictions;
    }

    public function predict(Asset $asset): array
    {
        $score = 0;
        $factors = [];
        $now = Carbon::now();

        $lastMaintenance = $asset->maintenances
            ->filter(fn (Maintenance $m) => $m->completion_date)
            ->sortByDesc('completion_date')
            ->first();

        $lastCalibration = $asset->maintenances
            ->filter(fn (Maintenance $m) => $m->completion_date && stripos((string) $m->asset_maintenance_type, 'calibration') !== false)
            ->sortByDesc('completion_date')
            ->first();

        $monthsSinceAny = $lastMaintenance
            ? Carbon::parse($lastMaintenance->completion_date)->diffInMonths($now)
            : ($asset->purchase_date ? Carbon::parse($asset->purchase_date)->diffInMonths($now) : 24);

        if ($monthsSinceAny >= self::CALIBRATION_INTERVAL_MONTHS) {
            $score += 25;
            $factors[] = 'No completed maintenance in '.$monthsSinceAny.' months';
        } elseif ($monthsSinceAny >= 9) {
            $score += 12;
            $factors[] = 'Maintenance due within ~'.(self::CALIBRATION_INTERVAL_MONTHS - $monthsSinceAny).' months';
        }

        if (! $lastCalibration) {
            $score += 15;
            $factors[] = 'No calibration record on file';
        } elseif (Carbon::parse($lastCalibration->completion_date)->diffInMonths($now) >= self::CALIBRATION_INTERVAL_MONTHS) {
            $score += 20;
            $factors[] = 'Calibration overdue (last: '.$lastCalibration->completion_date.')';
        }

        $statusName = strtolower($asset->status?->name ?? '');
        if (str_contains($statusName, 'repair') || str_contains($statusName, 'calibration')) {
            $score += 30;
            $factors[] = 'Current status: '.$asset->status->name;
        }

        if ($asset->purchase_date && $asset->model?->eol) {
            $eolDate = Carbon::parse($asset->purchase_date)->addMonths((int) $asset->model->eol);
            $monthsToEol = $now->diffInMonths($eolDate, false);
            if ($monthsToEol <= 6 && $monthsToEol >= 0) {
                $score += 18;
                $factors[] = 'Approaching end-of-life ('.$monthsToEol.' months remaining)';
            } elseif ($monthsToEol < 0) {
                $score += 22;
                $factors[] = 'Past model end-of-life date';
            }
        }

        $pendingMaintenance = $asset->maintenances->first(
            fn (Maintenance $m) => ! $m->completion_date && $m->start_date
        );
        if ($pendingMaintenance) {
            $score += 15;
            $factors[] = 'Open maintenance: '.$pendingMaintenance->name;
        }

        $score = min(100, $score);
        $urgency = $this->urgencyFromScore($score);
        $dueInDays = $this->estimateDueDays($monthsSinceAny, $urgency, $statusName);

        return [
            'asset_id' => $asset->id,
            'asset_tag' => $asset->asset_tag,
            'name' => $asset->name ?: $asset->model?->name,
            'model_name' => $asset->model?->name,
            'status' => $asset->status?->name,
            'location' => $asset->location?->name,
            'priority_score' => $score,
            'urgency' => $urgency,
            'urgency_label' => trans('admin/ai_insights/general.urgency_'.$urgency),
            'due_in_days' => $dueInDays,
            'due_label' => $dueInDays <= 0
                ? trans('admin/ai_insights/general.due_now')
                : trans('admin/ai_insights/general.due_in_days', ['days' => $dueInDays]),
            'factors' => $factors,
            'recommendation' => $this->recommendation($urgency, $factors),
            'last_maintenance' => $lastMaintenance?->completion_date,
        ];
    }

    protected function urgencyFromScore(int $score): string
    {
        if ($score >= 55) {
            return 'critical';
        }
        if ($score >= 30) {
            return 'high';
        }
        if ($score >= 15) {
            return 'medium';
        }

        return 'low';
    }

    protected function estimateDueDays(int $monthsSinceMaintenance, string $urgency, string $statusName): int
    {
        if (str_contains($statusName, 'repair') || str_contains($statusName, 'calibration')) {
            return 0;
        }

        if ($urgency === 'critical') {
            return max(0, 7 - (self::CALIBRATION_INTERVAL_MONTHS - $monthsSinceMaintenance) * 7);
        }

        if ($urgency === 'high') {
            return 30;
        }

        if ($urgency === 'medium') {
            return 60;
        }

        return 90;
    }

    protected function recommendation(string $urgency, array $factors): string
    {
        if ($urgency === 'critical') {
            return trans('admin/ai_insights/general.maint_recommend_critical');
        }
        if ($urgency === 'high') {
            return trans('admin/ai_insights/general.maint_recommend_high');
        }
        if (count($factors) > 0) {
            return trans('admin/ai_insights/general.maint_recommend_routine');
        }

        return trans('admin/ai_insights/general.maint_recommend_ok');
    }

    /**
     * Chart.js payloads for the equipment maintenance UI.
     *
     * @param  array<int, array<string, mixed>>  $predictions
     * @return array<string, mixed>
     */
    public function buildChartPayload(array $predictions): array
    {
        $summary = ['low' => 0, 'medium' => 0, 'high' => 0, 'critical' => 0];
        foreach ($predictions as $row) {
            $key = $row['urgency'] ?? 'low';
            if (isset($summary[$key])) {
                $summary[$key]++;
            }
        }

        $top = array_slice($predictions, 0, 8);

        return [
            'urgencySummary' => [
                'labels' => [
                    trans('admin/ai_insights/general.urgency_low'),
                    trans('admin/ai_insights/general.urgency_medium'),
                    trans('admin/ai_insights/general.urgency_high'),
                    trans('admin/ai_insights/general.urgency_critical'),
                ],
                'data' => [$summary['low'], $summary['medium'], $summary['high'], $summary['critical']],
                'colors' => ['#2e7d32', '#1565c0', '#e65100', '#c62828'],
            ],
            'topEquipment' => [
                'labels' => array_map(fn (array $row) => (string) ($row['asset_tag'] ?? ''), $top),
                'values' => array_map(fn (array $row) => (int) ($row['priority_score'] ?? 0), $top),
            ],
        ];
    }
}
