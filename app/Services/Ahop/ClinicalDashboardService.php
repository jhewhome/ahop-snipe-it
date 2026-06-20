<?php

namespace App\Services\Ahop;

use App\Models\Appointment;
use App\Models\Asset;
use App\Models\BillingInvoice;
use App\Models\BillingPayment;
use App\Models\Consumable;
use App\Models\LabOrder;
use App\Models\OpdVisit;
use App\Models\Patient;
use App\Models\Statuslabel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;

class ClinicalDashboardService
{
    /**
     * Whether the current (or given) user may open the AHOP clinical dashboard.
     * Clinical role groups do not use Snipe-IT superuser ("admin") access.
     */
    public static function canViewDashboard(?User $user = null): bool
    {
        if (! config('ahop.clinical_sidebar_mode') || ! config('ahop.clinical_dashboard')) {
            return false;
        }

        $user = $user ?? auth()->user();

        if (! $user) {
            return false;
        }

        if ($user->hasAccess('admin')) {
            return true;
        }

        $gate = Gate::forUser($user);

        return $gate->allows('view', Patient::class)
            || $gate->allows('view', Appointment::class)
            || $gate->allows('view', OpdVisit::class)
            || $gate->allows('view', LabOrder::class)
            || $gate->allows('view', BillingInvoice::class)
            || $gate->allows('view', Asset::class);
    }

    /**
     * @return array{
     *     stats: array<string, int|float>,
     *     recentOpd: Collection,
     *     recentLab: Collection,
     *     recentAppointments: Collection,
     *     equipmentByStatus: Collection
     * }
     */
    public function build(?Carbon $today = null): array
    {
        $today = $today ?? Carbon::today();

        $pendingStatusIds = Statuslabel::where('pending', 1)->pluck('id');

        $stats = [
            'patients' => Patient::count(),
            'opd_today' => OpdVisit::whereDate('visit_date', $today)->count(),
            'opd_in_progress' => OpdVisit::where('status', OpdVisit::STATUS_IN_PROGRESS)->count(),
            'lab_pending' => LabOrder::whereIn('status', [
                LabOrder::STATUS_ORDERED,
                LabOrder::STATUS_IN_PROGRESS,
            ])->count(),
            'appointments_today' => Appointment::query()
                ->whereBetween('scheduled_at', [$today->copy()->startOfDay(), $today->copy()->endOfDay()])
                ->whereIn('status', [Appointment::STATUS_SCHEDULED, Appointment::STATUS_CHECKED_IN])
                ->count(),
            'collections_today' => (float) BillingPayment::query()
                ->whereDate('paid_at', $today)
                ->sum('amount'),
            'equipment' => Asset::count(),
            'equipment_pending' => $pendingStatusIds->isEmpty()
                ? 0
                : Asset::whereIn('status_id', $pendingStatusIds)->count(),
            'supplies_low' => $this->countLowStockConsumables(),
        ];

        $recentOpd = OpdVisit::with('patient')
            ->orderByDesc('visit_date')
            ->limit(5)
            ->get();

        $recentLab = LabOrder::with('patient')
            ->orderByDesc('ordered_at')
            ->limit(5)
            ->get();

        $recentAppointments = Appointment::with('patient')
            ->whereBetween('scheduled_at', [$today->copy()->startOfDay(), $today->copy()->endOfDay()])
            ->orderBy('scheduled_at')
            ->limit(5)
            ->get();

        $equipmentByStatus = Statuslabel::query()
            ->where('show_in_nav', 1)
            ->withCount('assets')
            ->orderBy('name')
            ->get();

        return compact('stats', 'recentOpd', 'recentLab', 'recentAppointments', 'equipmentByStatus');
    }

    /**
     * JSON payload for dashboard auto-refresh (permission-aware).
     */
    public function refreshPayload(?Carbon $today = null): array
    {
        $data = $this->build($today);
        $user = auth()->user();

        $payload = [
            'stats' => $data['stats'],
            'refreshed_at' => now()->toIso8601String(),
        ];

        if ($user && Gate::forUser($user)->allows('view', Appointment::class)) {
            $payload['recent_appointments'] = $this->serializeAppointments($data['recentAppointments']);
        }

        if ($user && Gate::forUser($user)->allows('view', OpdVisit::class)) {
            $payload['recent_opd'] = $this->serializeOpdVisits($data['recentOpd']);
        }

        if ($user && Gate::forUser($user)->allows('view', LabOrder::class)) {
            $payload['recent_lab'] = $this->serializeLabOrders($data['recentLab']);
        }

        if ($user && Gate::forUser($user)->allows('view', Asset::class)) {
            $payload['equipment_by_status'] = $data['equipmentByStatus']->map(fn (Statuslabel $status) => [
                'id' => $status->id,
                'count' => $status->assets_count,
            ])->values()->all();
        }

        return $payload;
    }

    protected function countLowStockConsumables(): int
    {
        if (! Consumable::query()->exists()) {
            return 0;
        }

        return Consumable::query()
            ->whereNotNull('min_amt')
            ->get()
            ->filter(fn (Consumable $consumable) => $consumable->numRemaining() <= (int) $consumable->min_amt)
            ->count();
    }

    /**
     * @return list<array<string, string>>
     */
    protected function serializeOpdVisits(Collection $visits): array
    {
        return $visits->map(fn (OpdVisit $visit) => [
            'url' => route('opd-visits.show', $visit),
            'primary' => $visit->visit_number,
            'secondary' => $visit->patient?->full_name ?? '',
            'badge' => OpdVisit::statusOptions()[$visit->status] ?? $visit->status,
            'badge_class' => $visit->status,
        ])->all();
    }

    /**
     * @return list<array<string, string>>
     */
    protected function serializeLabOrders(Collection $orders): array
    {
        return $orders->map(fn (LabOrder $order) => [
            'url' => route('lab-orders.show', $order),
            'primary' => $order->order_number,
            'secondary' => $order->patient?->full_name ?? '',
            'badge' => LabOrder::statusOptions()[$order->status] ?? $order->status,
            'badge_class' => $order->status,
        ])->all();
    }

    /**
     * @return list<array<string, string>>
     */
    protected function serializeAppointments(Collection $appointments): array
    {
        return $appointments->map(fn (Appointment $appointment) => [
            'url' => route('appointments.show', $appointment),
            'primary' => $appointment->appointment_number,
            'secondary' => $appointment->patient?->full_name ?? '',
            'badge' => $appointment->scheduled_at?->format('H:i') ?? '',
            'badge_class' => $appointment->status,
        ])->all();
    }
}
