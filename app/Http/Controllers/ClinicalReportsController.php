<?php

namespace App\Http\Controllers;

use App\Services\Ahop\ClinicalReportService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ClinicalReportsController extends Controller
{
    public function __construct(
        protected ClinicalReportService $reports
    ) {
        $this->middleware('auth');
        parent::__construct();
    }

    public function index(Request $request): View
    {
        $this->authorize('reports.view');

        abort_unless(config('ahop.clinical_sidebar_mode'), 404);

        $from = $request->filled('from')
            ? Carbon::parse($request->input('from'))->startOfDay()
            : now()->subDays(30)->startOfDay();
        $to = $request->filled('to')
            ? Carbon::parse($request->input('to'))->endOfDay()
            : now()->endOfDay();

        if ($from->gt($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        return view('reports.clinical.index', [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'reportTypes' => ClinicalReportService::REPORT_TYPES,
            'chartData' => $this->reports->chartDashboard($from, $to),
        ]);
    }

    public function export(Request $request, string $type): StreamedResponse
    {
        $this->authorize('reports.view');

        abort_unless(config('ahop.clinical_sidebar_mode'), 404);
        abort_unless(in_array($type, ClinicalReportService::REPORT_TYPES, true), 404);

        $from = $request->filled('from')
            ? Carbon::parse($request->input('from'))->startOfDay()
            : now()->subDays(30)->startOfDay();
        $to = $request->filled('to')
            ? Carbon::parse($request->input('to'))->endOfDay()
            : now()->endOfDay();

        if ($type === 'invoice_aging') {
            $from = now()->subYears(10)->startOfDay();
            $to = now()->endOfDay();
        }

        $data = $this->reports->export($type, $from, $to);
        $filename = 'ahop-'.$type.'-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($data) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($handle, $data['headers']);
            foreach ($data['rows'] as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
