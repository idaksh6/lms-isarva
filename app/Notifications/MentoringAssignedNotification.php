<?php

namespace App\Notifications;

use App\Models\MentoringRelationship;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MentoringAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public MentoringRelationship $relationship) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if ($notifiable->email_notifications ?? true) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->relationship->loadMissing(['mentor', 'course']);

        return (new MailMessage)
            ->subject('You have been assigned a mentor')
            ->greeting('Hello '.$notifiable->name.',')
            ->line($this->relationship->mentor->name.' has been assigned as your mentor'
                .($this->relationship->course ? ' for '.$this->relationship->course->code : '').'.')
            ->action('View mentoring', url(route('mentoring.show', $this->relationship)))
            ->line('Use this space to track sessions, improvement areas, and action plans.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $this->relationship->loadMissing(['mentor', 'course']);

        return [
            'type' => 'mentoring_assigned',
            'mentoring_relationship_id' => $this->relationship->id,
            'title' => 'Mentor assigned: '.$this->relationship->mentor->name,
            'mentor_name' => $this->relationship->mentor->name,
            'course_code' => $this->relationship->course?->code,
            'url' => route('mentoring.show', $this->relationship),
        ];
    }
}
