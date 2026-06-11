<?php

namespace App\Console\Commands;

use App\Models\Complaint;
use App\Models\ComplaintTrack;
use Illuminate\Console\Command;

class AutoCloseComplaints extends Command
{
    protected $signature   = 'complaints:auto-close';
    protected $description = 'Automatically close complaints that have been resolved for 7+ days';

    public function handle(): void
    {
        $toClose = Complaint::where('current_status', 'resolved')
            ->where('resolved_at', '<=', now()->subDays(7))
            ->get();

        foreach ($toClose as $complaint) {
            $complaint->update(['current_status' => 'closed']);

            ComplaintTrack::create([
                'complaint_id' => $complaint->id,
                'old_status'   => 'resolved',
                'new_status'   => 'closed',
                'changed_by'   => null,
                'notes'        => 'Auto-closed after 7 days in resolved state.',
            ]);
        }

        $this->info("Auto-closed {$toClose->count()} complaint(s).");
    }
}
