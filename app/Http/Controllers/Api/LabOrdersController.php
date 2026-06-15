<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LabOrder;
use App\Models\LabResult;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Laboratory Information System (LIS) integration API for AgilityCare AHOP.
 */
class LabOrdersController extends Controller
{
    /**
     * List lab orders (optional filters: status, patient_id).
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('view', LabOrder::class);

        $query = LabOrder::query()
            ->with(['patient:id,patient_number,full_name', 'results'])
            ->orderByDesc('ordered_at');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('patient_id')) {
            $query->where('patient_id', $request->integer('patient_id'));
        }

        $orders = $query->limit(min((int) $request->input('limit', 50), 100))->get();

        return response()->json([
            'status' => 'success',
            'total' => $orders->count(),
            'payload' => $orders,
        ]);
    }

    /**
     * Show a single lab order with results.
     */
    public function show(LabOrder $lab_order): JsonResponse
    {
        $this->authorize('view', $lab_order);

        $lab_order->load(['patient', 'results', 'opdVisit']);

        return response()->json([
            'status' => 'success',
            'payload' => $lab_order,
        ]);
    }

    /**
     * Submit lab results from external LIS (mock or real integration).
     *
     * POST body example:
     * {
     *   "results": [
     *     {
     *       "test_code": "GLU",
     *       "test_name": "Glucose",
     *       "result_value": "95",
     *       "unit": "mg/dL",
     *       "reference_range": "70-100",
     *       "flag": "normal"
     *     }
     *   ],
     *   "mark_completed": true
     * }
     */
    public function storeResults(Request $request, LabOrder $lab_order): JsonResponse
    {
        $this->authorize('update', $lab_order);

        $validated = $request->validate([
            'results' => 'required|array|min:1',
            'results.*.test_name' => 'required|string|max:150',
            'results.*.result_value' => 'required|string|max:100',
            'results.*.test_code' => 'nullable|string|max:50',
            'results.*.unit' => 'nullable|string|max:30',
            'results.*.reference_range' => 'nullable|string|max:100',
            'results.*.flag' => 'nullable|in:normal,low,high,critical',
            'results.*.notes' => 'nullable|string',
            'results.*.result_at' => 'nullable|date',
            'mark_completed' => 'nullable|boolean',
        ]);

        $created = [];

        foreach ($validated['results'] as $row) {
            $result = new LabResult;
            $result->lab_order_id = $lab_order->id;
            $result->test_code = $row['test_code'] ?? null;
            $result->test_name = $row['test_name'];
            $result->result_value = $row['result_value'];
            $result->unit = $row['unit'] ?? null;
            $result->reference_range = $row['reference_range'] ?? null;
            $result->flag = $row['flag'] ?? null;
            $result->notes = $row['notes'] ?? null;
            $result->result_at = $row['result_at'] ?? now();

            if (! $result->save()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to save one or more results.',
                    'errors' => $result->getErrors(),
                ], 422);
            }

            $created[] = $result;
        }

        if ($lab_order->status === LabOrder::STATUS_ORDERED) {
            $lab_order->status = LabOrder::STATUS_IN_PROGRESS;
            $lab_order->save();
        }

        if ($request->boolean('mark_completed', true)) {
            $lab_order->status = LabOrder::STATUS_COMPLETED;
            $lab_order->completed_at = now();
            $lab_order->save();
        } else {
            $lab_order->markCompletedIfHasResults();
        }

        $lab_order->load('results');

        return response()->json([
            'status' => 'success',
            'message' => trans('admin/lab_orders/message.result.api_success'),
            'payload' => [
                'order' => $lab_order,
                'results_created' => count($created),
            ],
        ], 201);
    }
}
