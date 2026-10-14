<?php

namespace App\Domain\Reporting;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportSubscription extends Model
{
    use HasFactory;

    protected $table = 'report_subscriptions';

    protected $fillable = [
        'user_id',
        'title',
        'frequency',
        'filter_config',
        'format',
        'recipients',
        'last_dispatched_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'filter_config' => 'array',
            'recipients' => 'array',
            'last_dispatched_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
