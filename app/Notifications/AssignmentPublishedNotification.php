<?php

namespace App\Notifications;

use App\Models\Assignment;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AssignmentPublishedNotification extends Notification
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
        $course = $this->assignment->course;

        return (new MailMessage)
            ->subject('New assignment: '.$this->assignment->title)
            ->greeting('Hello '.$notifiable->name.',')
            ->line('A new assignment was published in '.$course->code.' — '.$course->title.'.')
            ->line('**'.$this->assignment->title.'**')
            ->when($this->assignment->due_at, fn ($mail) => $mail->line('Due: '.$this->assignment->due_at->format('l, F j, Y g:i A')))
            ->action('View assignment', route('assignments.show', $this->assignment));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'assignment_published',
            'assignment_id' => $this->assignment->id,
            'title' => $this->assignment->title,
            'course_code' => $this->assignment->course->code,
            'due_at' => $this->assignment->due_at?->toIso8601String(),
            'url' => route('assignments.show', $this->assignment),
        ];
    }
}
