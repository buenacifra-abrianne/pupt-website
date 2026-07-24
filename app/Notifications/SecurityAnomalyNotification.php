<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SecurityAnomalyNotification extends Notification
{
    use Queueable;

    public $ip;
    public $email;
    public $eventType;
    public $failureCount;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $ip, ?string $email, string $eventType, int $failureCount)
    {
        $this->ip = $ip;
        $this->email = $email;
        $this->eventType = $eventType;
        $this->failureCount = $failureCount;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Security Anomaly Detected',
            'message' => "Detected $this->failureCount failed attempts from IP $this->ip. Temporary containment applied.",
            'ip_address' => $this->ip,
            'email' => $this->email,
            'event_type' => $this->eventType,
            'failure_count' => $this->failureCount,
            'action_url' => route('superadmin.security.incidents'),
        ];
    }
}
