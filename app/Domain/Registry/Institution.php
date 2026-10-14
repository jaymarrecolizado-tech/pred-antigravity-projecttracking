<?php

namespace App\Domain\Registry;

use App\Domain\Geo\PsgcArea;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Institution extends Model
{
    use HasFactory;

    protected $table = 'institutions';

    protected $fillable = [
        'kind',
        'name',
        'external_id',
        'psgc_code',
        'primary_location_id',
        'contact_person',
        'contact_email',
        'contact_phone',
    ];

    public function area(): BelongsTo
    {
        return $this->belongsTo(PsgcArea::class, 'psgc_code', 'code');
    }

    public function primaryLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'primary_location_id');
    }

    public function projectSites(): HasMany
    {
        return $this->hasMany(ProjectSite::class);
    }
}
