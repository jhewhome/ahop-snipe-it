<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Services\Ahop\EquipmentMaintenancePredictor;
use App\Services\Ahop\LabTrendAnalyzer;
use App\Services\Ahop\PatientRiskPredictor;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ClinicalAnalyticsController extends Controller
{
    public function __construct(
        protected PatientRiskPredictor $patientRisk,
        protected LabTrendAnalyzer $labTrends,
        protected EquipmentMaintenancePredictor $equipmentMaintenance,
    ) {
        $this->middleware('auth');
        parent::__construct();
    }

    public function index(Request $request): View
    {
        abort_unless($this->enabled() && Gate::allows('ai_insights.view'), 403);

        $tab = $request->input('tab', 'patients');
        if (! in_array($tab, ['patients', 'labs', 'equipment'], true)) {
            $tab = 'patients';
        }

        $patientRisks = [];
        $patientChartData = null;
        $labTrends = [];
        $labChartData = null;
        $equipmentPredictions = [];
        $equipmentChartData = null;
        $labMonths = max(1, min(36, (int) $request->input('months', 12)));

        if ($tab === 'patients' && Gate::allows('patients.view')) {
            $patientRisks = $this->patientRisk->assessAll(50);
            if (count($patientRisks) > 0) {
                $patientChartData = $this->patientRisk->buildChartPayload($patientRisks);
            }
        }

        if ($tab === 'labs' && Gate::allows('lab_orders.view')) {
            $labTrends = $this->labTrends->analyzeGlobal($labMonths, 40);
            if (count($labTrends) > 0) {
                $labChartData = $this->labTrends->buildChartPayload($labTrends);
            }
        }

        if ($tab === 'equipment' && auth()->user()->hasAccess('assets.view')) {
            $equipmentPredictions = $this->equipmentMaintenance->predictAll(40);
            if (count($equipmentPredictions) > 0) {
                $equipmentChartData = $this->equipmentMaintenance->buildChartPayload($equipmentPredictions);
            }
        }

        return view('ai_insights.index', compact(
            'tab',
            'patientRisks',
            'patientChartData',
            'labTrends',
            'labChartData',
            'labMonths',
            'equipmentPredictions',
            'equipmentChartData',
        ));
    }

    public function patient(Patient $patient): View
    {
        abort_unless($this->enabled() && Gate::allows('ai_insights.view'), 403);
        $this->authorize('view', $patient);

        $risk = $this->patientRisk->assess($patient);
        $labTrends = $this->labTrends->analyzePatient($patient);
        $labChartData = count($labTrends) > 0
            ? $this->labTrends->buildChartPayload($labTrends, 4)
            : null;

        return view('ai_insights.patient', compact('patient', 'risk', 'labTrends', 'labChartData'));
    }

    public function exportLabTrends(Request $request): StreamedResponse
    {
        abort_unless($this->enabled() && Gate::allows('ai_insights.view'), 403);
        abort_unless(Gate::allows('lab_orders.view'), 403);

        $months = max(1, min(36, (int) $request->input('months', 12)));
        $patientId = $request->input('patient_id');

        if ($patientId) {
            $patient = Patient::findOrFail($patientId);
            $this->authorize('view', $patient);
            $trends = $this->labTrends->analyzePatient($patient, $months);
            $filename = 'lab-trends-'.$patient->patient_number.'-'.now()->format('Y-m-d').'.csv';
        } else {
            $trends = $this->labTrends->analyzeGlobal($months, 500);
            $filename = 'lab-trends-agilitycare-'.now()->format('Y-m-d').'.csv';
        }

        $headers = LabTrendAnalyzer::csvHeaders();
        $rows = $this->labTrends->flattenForCsv($trends);

        return response()->streamDownload(function () use ($headers, $rows) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($handle, $headers);
            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    protected function enabled(): bool
    {
        return (bool) config('ahop.clinical_analytics_enabled', config('ahop.ai_insights_enabled', true));
    }
}

