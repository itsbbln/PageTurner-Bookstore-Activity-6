<?php

namespace App\Models;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use OwenIt\Auditing\Models\Audit as BaseAudit;
use Throwable;

class Audit extends BaseAudit
{
    // Allow the auditing package to mass-assign its required attributes (event, auditable_type, etc.)
    // while we still auto-fill uuid/checksum in model events.
    protected $guarded = [];

    protected static function booted(): void
    {
        static::creating(function (self $audit) {
            $audit->uuid ??= (string) Str::uuid();
            $audit->checksum = $audit->checksumPayload();
        });

        static::created(function (self $audit) {
            if (! env('AUDIT_CRITICAL_ALERTS', true)) {
                return;
            }

            // Basic critical rule: changes to users/roles or admin-initiated deletes
            $critical = $audit->auditable_type === User::class
                || $audit->event === 'deleted'
                || str_contains(strtolower((string) $audit->url), '/admin/');

            if (! $critical) {
                return;
            }

            try {
                $emails = User::query()->where('role', 'admin')->pluck('email')->filter()->unique()->values()->all();
                if (count($emails) === 0) {
                    return;
                }

                $subject = "Critical Audit Alert: {$audit->event} {$audit->auditable_type}";
                $body = "A critical audit event was recorded.\n\n"
                    . "Event: {$audit->event}\n"
                    . "User ID: {$audit->user_id}\n"
                    . "Model: {$audit->auditable_type}\n"
                    . "Model ID: {$audit->auditable_id}\n"
                    . "IP: {$audit->ip_address}\n"
                    . "URL: {$audit->url}\n"
                    . "Timestamp: " . optional($audit->created_at)->toDateTimeString() . "\n";

                Mail::raw($body, function ($message) use ($emails, $subject) {
                    $message->to($emails)->subject($subject);
                });
            } catch (Throwable $e) {
                logger()->warning('Critical audit alert email failed', ['error' => $e->getMessage()]);
            }
        });
    }

    public function checksumPayload(): string
    {
        $payload = [
            'user_type' => $this->getAttribute('user_type'),
            'user_id' => $this->getAttribute('user_id'),
            'event' => $this->event,
            'auditable_type' => $this->auditable_type,
            'auditable_id' => $this->auditable_id,
            'old_values' => $this->old_values,
            'new_values' => $this->new_values,
            'url' => $this->url,
            'ip_address' => $this->ip_address,
            'user_agent' => $this->user_agent,
            'tags' => $this->tags,
            'created_at' => optional($this->created_at)->toJSON(),
        ];

        $payload = $this->ksortRecursive($payload);
        $json = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return hash('sha256', $json ?: '');
    }

    private function ksortRecursive(array $value): array
    {
        foreach ($value as $k => $v) {
            if (is_array($v)) {
                $value[$k] = $this->ksortRecursive($v);
            }
        }
        ksort($value);

        return $value;
    }
}

