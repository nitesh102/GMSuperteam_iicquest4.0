<?php

namespace App\Console\Commands;

use App\Models\Complaint;
use App\Models\ComplaintTrack;
use Illuminate\Console\Command;

class EscalateComplaints extends Command
{
    protected $signature   = 'complaints:escalate';
    protected $description = 'Bump priority on overdue complaints that have not been resolved';

    private const PRIORITY_LADDER = ['low', 'medium', 'high', 'emergency'];

    public function handle(): void
    {
        $overdue = Complaint::whereNotIn('current_status', ['resolved', 'closed', 'rejected'])
            ->where('escalation_level', '<', 3)
            ->whereNotNull('due_at')
            ->where('due_at', '<', now())
            ->get();

        foreach ($overdue as $complaint) {
            $currentIndex = array_search($complaint->priority, self::PRIORITY_LADDER);
            $nextPriority = self::PRIORITY_LADDER[min($currentIndex + 1, 3)];

            $complaint->update([
                'priority'         => $nextPriority,
                'escalation_level' => $complaint->escalation_level + 1,
                'due_at'           => $this->newDueAt($nextPriority),
            ]);

            ComplaintTrack::create([
                'complaint_id' => $complaint->id,
                'old_status'   => $complaint->current_status,
                'new_status'   => $complaint->current_status,
                'changed_by'   => null,
                'remarks'      => "Auto-escalated to {$nextPriority} priority (SLA breached, escalation #{$complaint->escalation_level}).",
            ]);
        }

        $this->info("Escalated {$overdue->count()} complaint(s).");
    }

    private function newDueAt(string $priority): \Carbon\Carbon
    {
        return match ($priority) {
            'emergency' => now()->addHours(2),
            'high'      => now()->addHours(12),
            'medium'    => now()->addHours(48),
            default     => now()->addHours(120),
        };
    }
}
