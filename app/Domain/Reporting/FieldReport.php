<?php

namespace App\Domain\Reporting;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LogicException;

class FieldReport extends Model
{
    use HasFactory;

    protected $table = 'field_reports';

    protected $fillable = [
        'intervention_id',
        'notes',
        'submission_lat',
        'submission_lng',
        'gps_accuracy_m',
        'device_timestamp',
        'server_timestamp',
        'custom_values',
        'verification_status',
        'verification_flags',
        'verified_by',
        'verified_at',
        'review_notes',
    ];

    protected function casts(): array
    {
        return [
            'submission_lat' => 'decimal:6',
            'submission_lng' => 'decimal:6',
            'gps_accuracy_m' => 'float',
            'device_timestamp' => 'datetime',
            'server_timestamp' => 'datetime',
            'custom_values' => 'array',
            'verification_flags' => 'array',
            'verified_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        // Enforce append-only integrity for deletions
        static::deleting(function () {
            throw new LogicException('Field report records are strictly append-only and cannot be deleted.');
        });
    }

    public function intervention(): BelongsTo
    {
        return $this->belongsTo(Intervention::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    public function correctionRequests(): HasMany
    {
        return $this->hasMany(CorrectionRequest::class);
    }
}
