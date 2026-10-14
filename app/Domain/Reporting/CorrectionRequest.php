<?php

namespace App\Domain\Reporting;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CorrectionRequest extends Model
{
    use HasFactory;

    protected $table = 'correction_requests';

    protected $fillable = [
        'field_report_id',
        'requested_by',
        'reviewed_by',
        'status',
        'proposed_diff',
        'review_notes',
    ];

    protected function casts(): array
    {
        return [
            'proposed_diff' => 'array',
        ];
    }

    public function fieldReport(): BelongsTo
    {
        return $this->belongsTo(FieldReport::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
