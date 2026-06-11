<?php

namespace App\Notifications;

use App\Models\Complaint;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewComplaintSubmitted extends Notification
{
    use Queueable;

    public Complaint $complaint;

    public function __construct(Complaint $complaint)
    {
        $this->complaint = $complaint;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'New Complaint',
            'message' => "{$this->complaint->complaint_no} — {$this->complaint->title}",
            'complaint_id' => $this->complaint->id,
            'type' => 'new_complaint',
        ];
    }
}
