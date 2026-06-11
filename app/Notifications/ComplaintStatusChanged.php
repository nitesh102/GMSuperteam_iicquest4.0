<?php

namespace App\Notifications;

use App\Models\Complaint;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ComplaintStatusChanged extends Notification
{
    use Queueable;

    public Complaint $complaint;
    public string $oldStatus;
    public string $newStatus;

    public function __construct(Complaint $complaint, string $oldStatus, string $newStatus)
    {
        $this->complaint = $complaint;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Status Updated',
            'message' => "{$this->complaint->complaint_no} changed from "
                . str_replace('_', ' ', $this->oldStatus)
                . ' to '
                . str_replace('_', ' ', $this->newStatus),
            'complaint_id' => $this->complaint->id,
            'type' => 'status_change',
        ];
    }
}
