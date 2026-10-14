<?php

namespace App\Domain\Portfolio;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Program extends Model
{
    use HasFactory;

    protected $table = 'programs';

    protected $fillable = [
        'name',
        'owner_unit',
        'color',
        'icon',
        'description',
    ];

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }
}
