<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Notifications\Notification;

class TaskCompletedNotification extends Notification
{
    public function __construct(public Task $task) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $completer = $this->task->assignee;

        return [
            'task_id' => $this->task->id,
            'task_title' => $this->task->title,
            'message' => ($completer?->username ?? 'Someone').' completed "'.$this->task->title.'"',
            'completed_by' => $completer?->username,
        ];
    }
}
