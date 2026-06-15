<?php

namespace App\Services\Ahop;

use App\Models\Asset;
use App\Models\Recipients\AlertRecipient;
use App\Models\Setting;
use App\Models\Statuslabel;
use App\Notifications\EquipmentMaintenanceAlert;
use Illuminate\Support\Facades\Notification;

class EquipmentAlertService
{
    public function __construct(
        protected EquipmentMaintenancePredictor $predictor
    ) {}

    /**
     * @return array{
     *     maintenance: list<array<string, mixed>>,
     *     pending_repair: list<array<string, mixed>>
     * }
     */
    public function collectAlerts(): array
    {
        $minScore = (int) config('ahop.equipment_alerts.min_score', 30);

        $maintenance = collect($this->predictor->predictAll(100))
            ->filter(fn (array $item) => ($item['priority_score'] ?? 0) >= $minScore)
            ->map(fn (array $item) => array_merge($item, [
                'url' => route('hardware.show', $item['asset_id']),
            ]))
            ->values()
            ->all();

        $pendingStatusIds = Statuslabel::where('pending', 1)->pluck('id');
        $pendingRepair = [];

        if ($pendingStatusIds->isNotEmpty()) {
            $pendingRepair = Asset::query()
                ->with(['status', 'location', 'model'])
                ->whereIn('status_id', $pendingStatusIds)
                ->orderBy('asset_tag')
                ->limit(50)
                ->get()
                ->map(fn (Asset $asset) => [
                    'asset_id' => $asset->id,
                    'asset_tag' => $asset->asset_tag,
                    'name' => $asset->name ?: $asset->model?->name,
                    'status' => $asset->status?->name,
                    'location' => $asset->location?->name,
                    'url' => route('hardware.show', $asset),
                ])
                ->all();
        }

        return [
            'maintenance' => $maintenance,
            'pending_repair' => $pendingRepair,
        ];
    }

    public function hasAlerts(array $alerts): bool
    {
        return count($alerts['maintenance']) > 0 || count($alerts['pending_repair']) > 0;
    }

    /**
     * @return array{sent: bool, reason: string|null, maintenance_count: int, pending_count: int}
     */
    public function sendAlerts(): array
    {
        if (! config('ahop.equipment_alerts.enabled', true)) {
            return [
                'sent' => false,
                'reason' => 'disabled',
                'maintenance_count' => 0,
                'pending_count' => 0,
            ];
        }

        $settings = Setting::getSettings();
        $alertEmail = trim((string) ($settings->alert_email ?? ''));

        if ($alertEmail === '') {
            return [
                'sent' => false,
                'reason' => 'no_alert_email',
                'maintenance_count' => 0,
                'pending_count' => 0,
            ];
        }

        $alerts = $this->collectAlerts();

        if (! $this->hasAlerts($alerts)) {
            return [
                'sent' => false,
                'reason' => 'none',
                'maintenance_count' => 0,
                'pending_count' => 0,
            ];
        }

        $recipients = collect(explode(',', $alertEmail))
            ->map(fn (string $email) => trim($email))
            ->filter()
            ->map(fn (string $email) => new AlertRecipient($email));

        Notification::send($recipients, new EquipmentMaintenanceAlert(
            $alerts,
            url('/clinical-analytics?tab=equipment')
        ));

        return [
            'sent' => true,
            'reason' => null,
            'maintenance_count' => count($alerts['maintenance']),
            'pending_count' => count($alerts['pending_repair']),
        ];
    }
}
