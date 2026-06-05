<?php

namespace App\Notifications;

use App\Models\Assignment;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AssignmentDueReminderNotification extends Notification
{

    public function __construct(public Assignment $assignment) {}

    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if ($notifiable->email_notifications) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Due soon: '.$this->assignment->title)
            ->greeting('Hello '.$notifiable->name.',')
            ->line('Reminder: **'.$this->assignment->title.'** is due '.$this->assignment->due_at->diffForHumans().'.')
            ->action('Open assignment', route('assignments.show', $this->assignment));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'assignment_due',
            'assignment_id' => $this->assignment->id,
            'title' => $this->assignment->title,
            'due_at' => $this->assignment->due_at?->toIso8601String(),
            'url' => route('assignments.show', $this->assignment),
        ];
    }
}
