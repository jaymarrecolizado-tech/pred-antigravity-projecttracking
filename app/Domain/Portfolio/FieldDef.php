<?php

namespace App\Domain\Portfolio;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FieldDef extends Model
{
    use HasFactory;

    protected $table = 'field_defs';

    protected $fillable = [
        'project_type_id',
        'key',
        'label',
        'type',
        'options',
        'required',
        'applies_to',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'required' => 'boolean',
        ];
    }

    public function projectType(): BelongsTo
    {
        return $this->belongsTo(ProjectType::class);
    }
}
