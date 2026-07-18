<?php

namespace App\Notifications;

use App\Models\Assessment;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AssessmentPublishedNotification extends Notification
{
    public function __construct(public Assessment $assessment) {}

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
        $course = $this->assessment->course;

        return (new MailMessage)
            ->subject('New assessment: '.$this->assessment->title)
            ->greeting('Hello '.$notifiable->name.',')
            ->line('A new internal assessment was published in '.$course->code.' — '.$course->title.'.')
            ->line('**'.$this->assessment->title.'**')
            ->line('Total marks: '.$this->assessment->maxScore())
            ->when($this->assessment->due_at, fn ($mail) => $mail->line('Deadline: '.$this->assessment->due_at->format('l, F j, Y g:i A')))
            ->action('Open assessment', route('assessments.show', $this->assessment));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'assessment_published',
            'assessment_id' => $this->assessment->id,
            'title' => $this->assessment->title,
            'course_code' => $this->assessment->course->code,
            'due_at' => $this->assessment->due_at?->toIso8601String(),
            'url' => route('assessments.show', $this->assessment),
        ];
    }
}
