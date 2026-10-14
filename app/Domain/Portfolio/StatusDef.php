<?php

namespace App\Domain\Portfolio;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StatusDef extends Model
{
    use HasFactory;

    protected $table = 'status_defs';

    protected $fillable = [
        'project_type_id',
        'key',
        'label',
        'sort_order',
        'is_completed',
        'is_terminal',
        'next_keys',
    ];

    protected function casts(): array
    {
        return [
            'is_completed' => 'boolean',
            'is_terminal' => 'boolean',
            'next_keys' => 'array',
        ];
    }

    public function projectType(): BelongsTo
    {
        return $this->belongsTo(ProjectType::class);
    }
}
