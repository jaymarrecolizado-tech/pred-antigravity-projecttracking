<?php

namespace App\Domain\Portfolio;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InterventionType extends Model
{
    use HasFactory;

    protected $table = 'intervention_types';

    protected $fillable = [
        'project_type_id',
        'key',
        'label',
    ];

    public function projectType(): BelongsTo
    {
        return $this->belongsTo(ProjectType::class);
    }
}
