<?php

namespace App\Domain\Geo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class CongressionalDistrict extends Model
{
    use HasFactory;

    protected $table = 'congressional_districts';

    protected $fillable = [
        'province_code',
        'name',
        'district_number',
    ];

    public function province(): BelongsTo
    {
        return $this->belongsTo(PsgcArea::class, 'province_code', 'code');
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(DistrictMembership::class, 'district_id');
    }

    public function activeMemberships(): HasMany
    {
        return $this->memberships()->whereNull('effective_to')->orWhere('effective_to', '>', now());
    }
}
