<?php

namespace Webkul\Core\Traits;

use Webkul\Core\Models\AuditLog;

trait Auditable
{
    protected static function bootAuditable(): void
    {
        static::created(function ($model) {
            $payload = static::redactEncryptedAttributes($model, $model->toArray());

            static::logAudit('create', $model, $payload);
        });

        static::updated(function ($model) {
            $changes = $model->getChanges();

            $old = [];
            foreach ($changes as $key => $value) {
                $old[$key] = $model->getOriginal($key);
            }

            $encrypted = static::getEncryptedAttributes($model);

            foreach ($encrypted as $key) {
                if (isset($changes[$key])) {
                    $changes[$key] = '[ENCRYPTED]';
                }

                if (isset($old[$key])) {
                    $old[$key] = '[ENCRYPTED]';
                }
            }

            static::logAudit('update', $model, [
                'old' => $old,
                'new' => $changes,
            ]);
        });

        static::deleted(function ($model) {
            $payload = static::redactEncryptedAttributes($model, $model->toArray());

            static::logAudit('delete', $model, $payload);
        });
    }

    protected static function logAudit(string $action, $model, ?array $payload = null): void
    {
        AuditLog::create([
            'user_id' => auth()->guard('user')->id(),
            'action' => $action,
            'entity_type' => $model->getTable(),
            'entity_id' => $model->getKey(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'payload' => $payload,
        ]);
    }

    protected static function getEncryptedAttributes($model): array
    {
        $encrypted = [];

        foreach ($model->getCasts() as $attribute => $cast) {
            if (str_starts_with($cast, 'encrypted')) {
                $encrypted[] = $attribute;
            }
        }

        return $encrypted;
    }

    protected static function redactEncryptedAttributes($model, array $payload): array
    {
        foreach (static::getEncryptedAttributes($model) as $key) {
            if (array_key_exists($key, $payload)) {
                $payload[$key] = '[ENCRYPTED]';
            }
        }

        return $payload;
    }
}
