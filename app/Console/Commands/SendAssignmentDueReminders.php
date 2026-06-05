<?php

namespace App\Console\Commands;

use App\Models\Assignment;
use App\Notifications\AssignmentDueReminderNotification;
use Illuminate\Console\Command;

class SendAssignmentDueReminders extends Command
{
    protected $signature = 'lms:due-reminders';

    protected $description = 'Notify enrolled students about assignments due within 24 hours';

    public function handle(): int
    {
        $windowStart = now();
        $windowEnd = now()->addDay();

        $assignments = Assignment::query()
            ->where('is_published', true)
            ->whereNotNull('due_at')
            ->whereBetween('due_at', [$windowStart, $windowEnd])
            ->with(['course.students'])
            ->get();

        $count = 0;

        foreach ($assignments as $assignment) {
            foreach ($assignment->course->students as $student) {
                $alreadySubmitted = $assignment->submissions()
                    ->where('user_id', $student->id)
                    ->where('status', '!=', 'needs_revision')
                    ->exists();

                if ($alreadySubmitted || ! $student->isActive()) {
                    continue;
                }

                $student->notify(new AssignmentDueReminderNotification($assignment));
                $count++;
            }
        }

        $this->info("Sent {$count} due-date reminder(s).");

        return self::SUCCESS;
    }
}
