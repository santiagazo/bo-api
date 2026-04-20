<?php

namespace App\Models;

use App\Enums\NotificationIntentStatus;
use App\Enums\NotificationIntentType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class NotificationIntent extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'job_id',
        'user_id',
        'type',
        'status',
        'scheduled_at',
        'sent_at',
        'metadata',
    ];

    protected $casts = [
        'type' => NotificationIntentType::class,
        'status' => NotificationIntentStatus::class,
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
