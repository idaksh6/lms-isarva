<?php

namespace App\Notifications;

use App\Models\Announcement;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class AnnouncementPublishedNotification extends Notification
{
    public function __construct(public Announcement $announcement) {}

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
        $announcement = $this->announcement->loadMissing('course', 'author');
        $scope = $announcement->isGlobal()
            ? 'the whole programme'
            : $announcement->course->code.' — '.$announcement->course->title;

        $mail = (new MailMessage)
            ->subject('New announcement: '.$announcement->title)
            ->greeting('Hello '.$notifiable->name.',')
            ->line('A new announcement was posted for '.$scope.'.')
            ->line('**'.$announcement->title.'**')
            ->line(Str::limit($announcement->body, 300));

        if ($announcement->is_pinned) {
            $mail->line('This announcement is pinned to the top of the Announcements page.');
        }

        return $mail->action('View announcements', route('announcements.index'));
    }

    public function toArray(object $notifiable): array
    {
        $this->announcement->loadMissing('course');

        return [
            'type' => 'announcement_published',
            'announcement_id' => $this->announcement->id,
            'title' => $this->announcement->title,
            'body' => Str::limit($this->announcement->body, 120),
            'course_code' => $this->announcement->course?->code,
            'is_pinned' => $this->announcement->is_pinned,
            'url' => route('announcements.index'),
        ];
    }
}
