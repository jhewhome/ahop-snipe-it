<?php

namespace App\Console\Commands;

use App\Services\Ahop\EquipmentAlertService;
use Illuminate\Console\Command;

class SendEquipmentAlerts extends Command
{
    protected $signature = 'ahop:send-equipment-alerts';

    protected $description = 'Email biomedical staff about high-priority equipment maintenance and pending repair assets';

    public function handle(EquipmentAlertService $service): int
    {
        if (! config('ahop.equipment_alerts.enabled', true)) {
            $this->warn('Equipment alerts are disabled (AHOP_EQUIPMENT_ALERTS=false).');

            return self::SUCCESS;
        }

        try {
            $result = $service->sendAlerts();
        } catch (\Throwable $e) {
            $this->error('Failed to send equipment alert: '.$e->getMessage());

            return self::FAILURE;
        }

        return match ($result['reason']) {
            'disabled' => tap(self::SUCCESS, fn () => $this->warn('Equipment alerts are disabled.')),
            'no_alert_email' => tap(self::FAILURE, fn () => $this->error('No alert email configured in Admin → Settings → Alerts.')),
            'none' => tap(self::SUCCESS, fn () => $this->info('No equipment alerts to send.')),
            null => tap(self::SUCCESS, function () use ($result) {
                $this->info(sprintf(
                    'Equipment alert sent (%d maintenance, %d pending repair).',
                    $result['maintenance_count'],
                    $result['pending_count']
                ));
            }),
            default => self::SUCCESS,
        };
    }
}
