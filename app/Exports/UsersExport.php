<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UsersExport implements FromQuery, WithHeadings, WithMapping, WithChunkReading, ShouldAutoSize
{
    public function __construct(
        private readonly bool $redactPii = false,
        private readonly ?string $role = null
    ) {
    }

    public function query()
    {
        $q = User::query();
        if ($this->role) {
            $q->where('role', $this->role);
        }

        return $q->latest();
    }

    public function headings(): array
    {
        return ['ID', 'Name', 'Email', 'Role', 'Email Verified At', 'Created At'];
    }

    public function map($user): array
    {
        $name = $user->name;
        $email = $user->email;

        if ($this->redactPii) {
            $name = 'REDACTED';
            $email = $this->maskEmail((string) $user->email);
        }

        return [
            $user->id,
            $name,
            $email,
            $user->role,
            optional($user->email_verified_at)?->toDateTimeString(),
            optional($user->created_at)?->toDateTimeString(),
        ];
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    private function maskEmail(string $email): string
    {
        if (! str_contains($email, '@')) {
            return '***';
        }

        [$local, $domain] = explode('@', $email, 2);
        $localMask = strlen($local) <= 2 ? str_repeat('*', strlen($local)) : substr($local, 0, 2) . str_repeat('*', max(strlen($local) - 2, 1));

        return $localMask . '@' . $domain;
    }
}

