<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AuditLogger
{
    public function record(
        string $event,
        ?Model $subject = null,
        array $metadata = [],
        ?User $actor = null,
        ?Request $request = null,
    ): AuditLog {
        $request ??= request();

        return AuditLog::create([
            'actor_id' => $actor?->getKey() ?? auth()->id(),
            'event' => $event,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey() === null ? null : (string) $subject->getKey(),
            'metadata' => $this->sanitizeMetadata($metadata),
            'ip_address' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 512) ?: null,
        ]);
    }

    /**
     * @param  array<string, mixed>  $metadata
     * @return array<string, mixed>
     */
    private function sanitizeMetadata(array $metadata): array
    {
        $safe = [];

        foreach ($metadata as $key => $value) {
            if (preg_match('/password|token|session|secret|authorization|credential/i', (string) $key)) {
                continue;
            }

            if (is_array($value)) {
                $value = $this->sanitizeMetadata($value);
            } elseif (is_string($value)) {
                $value = mb_substr($value, 0, 500);
            } elseif (! is_scalar($value) && $value !== null) {
                continue;
            }

            $safe[$key] = $value;
        }

        return $safe;
    }
}
