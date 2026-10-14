<?php

namespace App\Domain\Portfolio;

use App\Domain\Geo\PsgcArea;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectScope extends Model
{
    use HasFactory;

    protected $table = 'project_scopes';

    protected $fillable = [
        'project_id',
        'psgc_code',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(PsgcArea::class, 'psgc_code', 'code');
    }
}
