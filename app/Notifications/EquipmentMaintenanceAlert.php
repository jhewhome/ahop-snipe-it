<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Symfony\Component\Mime\Email;

class EquipmentMaintenanceAlert extends Notification
{
    use Queueable;

    /**
     * @param  array{maintenance: list<array<string, mixed>>, pending_repair: list<array<string, mixed>>}  $alerts
     */
    public function __construct(
        protected array $alerts,
        protected string $analyticsUrl
    ) {}

    public function via(): array
    {
        return ['mail'];
    }

    public function toMail(): MailMessage
    {
        return (new MailMessage)
            ->markdown('notifications.markdown.equipment-maintenance-alert', [
                'alerts' => $this->alerts,
                'analyticsUrl' => $this->analyticsUrl,
            ])
            ->subject('⚠️ '.trans('ahop.equipment_alert_subject'))
            ->withSymfonyMessage(function (Email $message) {
                $message->getHeaders()->addTextHeader('X-System-Sender', 'AHOP');
            });
    }
}
