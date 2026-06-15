<?php

namespace App\Services\Ahop;

use App\Models\OpdVisit;
use App\Models\Patient;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Rule-based patient risk scoring for AHOP Phase 5 (local analytics).
 */
class PatientRiskPredictor
{
    protected const HIGH_RISK_KEYWORDS = [
        'diabetes', 'hypertension', 'cardiac', 'heart failure', 'renal', 'kidney',
        'cancer', 'malignant', 'copd', 'asthma', 'stroke', 'sepsis', 'pneumonia',
    ];

    protected const ACUTE_KEYWORDS = [
        'chest pain', 'dyspnea', 'shortness of breath', 'unconscious', 'syncope',
        'severe', 'hemorrhage', 'bleeding',
    ];

    public function assess(Patient $patient): array
    {
        $patient->loadMissing([
            'opdVisits' => fn ($q) => $q->orderByDesc('visit_date')->limit(20),
            'labOrders.results',
        ]);

        $score = 0;
        $factors = [];

        $age = $patient->birthdate ? $patient->birthdate->age : null;
        if ($age !== null) {
            if ($age >= 80) {
                $score += 25;
                $factors[] = 'Age 80+ (elevated baseline risk)';
            } elseif ($age >= 65) {
                $score += 15;
                $factors[] = 'Age 65+ (moderate age-related risk)';
            }
        }

        $recentVisits = $patient->opdVisits->filter(
            fn (OpdVisit $v) => $v->visit_date && $v->visit_date->gte(Carbon::now()->subDays(90))
        );
        if ($recentVisits->count() >= 3) {
            $score += 10;
            $factors[] = 'Frequent OPD visits in the last 90 days ('.$recentVisits->count().')';
        }

        $latestVisit = $patient->opdVisits->first();
        if ($latestVisit) {
            $score += $this->scoreVitals($latestVisit, $factors);
            $score += $this->scoreClinicalText($latestVisit->diagnosis, self::HIGH_RISK_KEYWORDS, 5, 20, 'Chronic condition noted in diagnosis', $factors);
            $score += $this->scoreClinicalText($latestVisit->chief_complaint, self::ACUTE_KEYWORDS, 10, 20, 'Acute warning symptom in chief complaint', $factors);
        }

        $labScore = $this->scoreLabResults($patient, $factors);
        $score += $labScore;

        $score = min(100, max(0, $score));
        $level = $this->levelFromScore($score);

        return [
            'patient_id' => $patient->id,
            'patient_number' => $patient->patient_number,
            'full_name' => $patient->full_name,
            'score' => $score,
            'level' => $level,
            'level_label' => trans('admin/ai_insights/general.risk_'.$level),
            'factors' => $factors,
            'summary' => $this->summaryForLevel($level, $factors),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function assessAll(?int $limit = 50): array
    {
        return Patient::query()
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->map(fn (Patient $p) => $this->assess($p))
            ->sortByDesc('score')
            ->values()
            ->all();
    }

    protected function scoreVitals(OpdVisit $visit, array &$factors): int
    {
        $score = 0;

        if ($visit->temperature !== null && $visit->temperature >= 38.0) {
            $score += 15;
            $factors[] = 'Elevated temperature on latest visit ('.$visit->temperature.' °C)';
        }

        if ($visit->pulse_rate !== null && $visit->pulse_rate > 100) {
            $score += 10;
            $factors[] = 'Tachycardia on latest visit (pulse '.$visit->pulse_rate.')';
        }

        if ($visit->blood_pressure && preg_match('/(\d{2,3})\s*\/\s*(\d{2,3})/', $visit->blood_pressure, $m)) {
            $systolic = (int) $m[1];
            $diastolic = (int) $m[2];
            if ($systolic >= 140 || $diastolic >= 90) {
                $score += 15;
                $factors[] = 'Elevated blood pressure ('.$visit->blood_pressure.')';
            }
        }

        return $score;
    }

    protected function scoreClinicalText(?string $text, array $keywords, int $perHit, int $cap, string $label, array &$factors): int
    {
        if (! $text) {
            return 0;
        }

        $haystack = strtolower($text);
        $hits = 0;
        foreach ($keywords as $keyword) {
            if (str_contains($haystack, $keyword)) {
                $hits++;
            }
        }

        if ($hits === 0) {
            return 0;
        }

        $points = min($cap, $hits * $perHit);
        $factors[] = $label.' ('.$hits.' indicator'.($hits > 1 ? 's' : '').')';

        return $points;
    }

    protected function scoreLabResults(Patient $patient, array &$factors): int
    {
        $score = 0;
        $critical = 0;
        $abnormal = 0;
        $cutoff = Carbon::now()->subMonths(6);

        foreach ($patient->labOrders as $order) {
            foreach ($order->results as $result) {
                if ($result->result_at && $result->result_at->lt($cutoff)) {
                    continue;
                }
                if ($result->flag === 'critical') {
                    $critical++;
                } elseif (in_array($result->flag, ['high', 'low'], true)) {
                    $abnormal++;
                }
            }
        }

        if ($critical > 0) {
            $add = min(30, $critical * 15);
            $score += $add;
            $factors[] = $critical.' critical lab result(s) in the last 6 months';
        }

        if ($abnormal > 0) {
            $add = min(15, $abnormal * 5);
            $score += $add;
            if ($critical === 0) {
                $factors[] = $abnormal.' abnormal lab result(s) in the last 6 months';
            }
        }

        return $score;
    }

    protected function levelFromScore(int $score): string
    {
        if ($score >= 60) {
            return 'high';
        }
        if ($score >= 30) {
            return 'medium';
        }

        return 'low';
    }

    protected function summaryForLevel(string $level, array $factors): string
    {
        if ($level === 'high') {
            return trans('admin/ai_insights/general.risk_summary_high');
        }
        if ($level === 'medium') {
            return trans('admin/ai_insights/general.risk_summary_medium');
        }

        return count($factors) > 0
            ? trans('admin/ai_insights/general.risk_summary_low_watch')
            : trans('admin/ai_insights/general.risk_summary_low');
    }

    /**
     * Chart.js payloads for the patient risk UI.
     *
     * @param  array<int, array<string, mixed>>  $assessments
     * @return array<string, mixed>
     */
    public function buildChartPayload(array $assessments): array
    {
        $summary = ['low' => 0, 'medium' => 0, 'high' => 0];
        foreach ($assessments as $row) {
            $key = $row['level'] ?? 'low';
            if (isset($summary[$key])) {
                $summary[$key]++;
            }
        }

        $top = array_slice($assessments, 0, 8);

        return [
            'levelSummary' => [
                'labels' => [
                    trans('admin/ai_insights/general.risk_low'),
                    trans('admin/ai_insights/general.risk_medium'),
                    trans('admin/ai_insights/general.risk_high'),
                ],
                'data' => [$summary['low'], $summary['medium'], $summary['high']],
                'colors' => ['#2e7d32', '#e65100', '#c62828'],
            ],
            'topPatients' => [
                'labels' => array_map(
                    fn (array $row) => (string) ($row['patient_number'] ?? $row['full_name'] ?? ''),
                    $top
                ),
                'values' => array_map(fn (array $row) => (int) ($row['score'] ?? 0), $top),
            ],
        ];
    }
}
