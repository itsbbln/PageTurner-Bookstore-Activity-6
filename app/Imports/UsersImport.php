<?php

namespace App\Imports;

use App\Models\ImportLog;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Events\BeforeImport;
use Maatwebsite\Excel\Events\ImportFailed;
use Maatwebsite\Excel\Validators\Failure;

class UsersImport implements ToModel, WithHeadingRow, WithValidation, WithChunkReading, WithBatchInserts, SkipsOnFailure, WithEvents, ShouldQueue
{
    use Importable;

    public function __construct(
        private readonly int $importLogId,
        private readonly string $defaultRole = 'customer',
    ) {
    }

    public function model(array $row)
    {
        $password = trim((string) ($row['password'] ?? ''));
        if ($password === '') {
            $password = Str::random(12);
        }

        $role = trim((string) ($row['role'] ?? '')) ?: $this->defaultRole;
        if (! in_array($role, ['admin', 'customer', 'premium'], true)) {
            $role = 'customer';
        }

        ImportLog::whereKey($this->importLogId)->update([
            'successful_rows' => DB::raw('successful_rows + 1'),
        ]);

        return new User([
            'name' => trim((string) ($row['name'] ?? '')),
            'email' => trim((string) ($row['email'] ?? '')),
            'password' => $password,
            'role' => $role,
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['nullable', 'string', 'min:6'],
            'role' => ['nullable', 'in:admin,customer,premium'],
        ];
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function batchSize(): int
    {
        return 1000;
    }

    public function registerEvents(): array
    {
        return [
            BeforeImport::class => function () {
                ImportLog::whereKey($this->importLogId)->update([
                    'status' => 'running',
                    'started_at' => now(),
                ]);
            },
            AfterImport::class => function () {
                $log = ImportLog::find($this->importLogId);
                if (! $log) {
                    return;
                }

                $failed = (int) $log->failed_rows;
                $success = (int) $log->successful_rows;

                $log->update([
                    'status' => $failed > 0 ? 'completed_with_errors' : 'completed',
                    'processed_rows' => $failed + $success,
                    'total_rows' => $failed + $success,
                    'finished_at' => now(),
                ]);
            },
            ImportFailed::class => function () {
                ImportLog::whereKey($this->importLogId)->update([
                    'status' => 'failed',
                    'finished_at' => now(),
                ]);
            },
        ];
    }

    public function onFailure(Failure ...$failures): void
    {
        $payload = collect($failures)->map(fn (Failure $f) => [
            'row' => $f->row(),
            'attribute' => $f->attribute(),
            'errors' => $f->errors(),
            'values' => $f->values(),
        ])->values();

        ImportLog::whereKey($this->importLogId)->update([
            'failed_rows' => DB::raw('failed_rows + ' . $payload->count()),
        ]);

        $log = ImportLog::find($this->importLogId);
        if (! $log) {
            return;
        }

        $merged = collect($log->failures ?? [])->concat($payload)->take(200)->values()->all();
        $log->update(['failures' => $merged]);
    }
}

