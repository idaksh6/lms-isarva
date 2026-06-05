<?php

namespace App\Notifications;

use App\Models\Submission;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubmissionReceivedNotification extends Notification
{

    public function __construct(public Submission $submission) {}

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
        $assignment = $this->submission->assignment;

        return (new MailMessage)
            ->subject('New submission: '.$assignment->title)
            ->greeting('Hello '.$notifiable->name.',')
            ->line($this->submission->student->name.' submitted work for **'.$assignment->title.'**.')
            ->action('Review submission', route('submissions.show', $this->submission));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'submission_received',
            'submission_id' => $this->submission->id,
            'student_name' => $this->submission->student->name,
            'assignment_title' => $this->submission->assignment->title,
            'url' => route('submissions.show', $this->submission),
        ];
    }
}
