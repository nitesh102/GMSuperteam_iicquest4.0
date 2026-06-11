<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Complaint extends Model
{
    /** @use HasFactory<\Database\Factories\ComplaintFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'complaint_no',
        'citizen_id',
        'category_id',
        'department_id',
        'assigned_to',
        'title',
        'description',
        'location',
        'latitude',
        'longitude',
        'priority',
        'current_status',
        'ai_summary',
        'is_spam',
        'resolved_at',
        'resolution_notes',
        'before_photo',
        'after_photo',
        'due_at',
        'escalation_level',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'is_spam'          => 'boolean',
            'latitude'         => 'decimal:7',
            'longitude'        => 'decimal:7',
            'resolved_at'      => 'datetime',
            'due_at'           => 'datetime',
            'escalation_level' => 'integer',
        ];
    }

    public function citizen(): BelongsTo
    {
        return $this->belongsTo(User::class, 'citizen_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ComplaintCategory::class, 'category_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(ComplaintAttachment::class);
    }

    public function aiAnalysis(): HasOne
    {
        return $this->hasOne(ComplaintAiAnalysis::class, 'complaint_id');
    }

    public function tracks(): HasMany
    {
        return $this->hasMany(ComplaintTrack::class)->orderBy('created_at', 'desc');
    }
}
