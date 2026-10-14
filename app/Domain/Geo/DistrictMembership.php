<?php

namespace App\Domain\Geo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DistrictMembership extends Model
{
    use HasFactory;

    protected $table = 'district_memberships';

    protected $fillable = [
        'district_id',
        'citymun_code',
        'effective_from',
        'effective_to',
    ];

    protected function casts(): array
    {
        return [
            'effective_from' => 'date',
            'effective_to' => 'date',
        ];
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(CongressionalDistrict::class, 'district_id');
    }

    public function cityMunicipality(): BelongsTo
    {
        return $this->belongsTo(PsgcArea::class, 'citymun_code', 'code');
    }
}
