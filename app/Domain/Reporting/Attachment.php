<?php

namespace App\Domain\Reporting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attachment extends Model
{
    use HasFactory;

    protected $table = 'attachments';

    protected $fillable = [
        'field_report_id',
        'file_path',
        'thumbnail_path',
        'file_name',
        'mime_type',
        'file_size_bytes',
        'sha256_hash',
        'exif_lat',
        'exif_lng',
        'exif_timestamp',
    ];

    protected function casts(): array
    {
        return [
            'file_size_bytes' => 'integer',
            'exif_lat' => 'decimal:6',
            'exif_lng' => 'decimal:6',
            'exif_timestamp' => 'datetime',
        ];
    }

    public function fieldReport(): BelongsTo
    {
        return $this->belongsTo(FieldReport::class);
    }
}
