<?php

namespace App\Notifications;

use App\Models\Submission;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GradePostedNotification extends Notification
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
            ->subject('Grade posted: '.$assignment->title)
            ->greeting('Hello '.$notifiable->name.',')
            ->line('Your submission for **'.$assignment->title.'** has been reviewed.')
            ->when($this->submission->score !== null, fn ($mail) => $mail->line('Score: **'.$this->submission->score.'%** ('.$this->submission->letter_grade.')'))
            ->action('View feedback', route('submissions.show', $this->submission));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'grade_posted',
            'submission_id' => $this->submission->id,
            'assignment_title' => $this->submission->assignment->title,
            'score' => $this->submission->score,
            'letter_grade' => $this->submission->letter_grade,
            'url' => route('submissions.show', $this->submission),
        ];
    }
}
