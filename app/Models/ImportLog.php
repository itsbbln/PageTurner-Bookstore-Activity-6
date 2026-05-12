<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ImportLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'user_id',
        'type',
        'original_filename',
        'stored_path',
        'stored_disk',
        'mime_type',
        'file_hash',
        'update_existing',
        'status',
        'total_rows',
        'processed_rows',
        'successful_rows',
        'failed_rows',
        'failures',
        'failure_report_path',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'update_existing' => 'bool',
        'failures' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $log) {
            $log->uuid ??= (string) Str::uuid();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

