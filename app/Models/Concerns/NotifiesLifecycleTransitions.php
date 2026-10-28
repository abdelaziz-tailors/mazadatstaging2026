<?php

namespace App\Models\Concerns;

use App\Services\LifecycleNotificationService;
use Illuminate\Database\Eloquent\Builder;

trait NotifiesLifecycleTransitions
{
    /** Serialize status writes, including callers holding stale Eloquent instances. */
    protected function performUpdate(Builder $query)
    {
        $fields = $this instanceof \App\Models\Order ? ['status', 'payment_status'] : ['status'];
        if (! $this->isDirty($fields)) {
            return parent::performUpdate($query);
        }

        return $this->getConnection()->transaction(function () use ($query, $fields) {
            $before = $this->newQueryWithoutScopes()->whereKey($this->getKey())->lockForUpdate()->firstOrFail();
            $changed = [];
            foreach ($fields as $field) {
                if ($this->isDirty($field) && $before->$field !== $this->$field) {
                    $changed[$field] = $before->$field;
                }
            }
            $saved = parent::performUpdate($query);
            if ($saved && $changed) {
                $current = $this->fresh();
                $changed = array_filter($changed, function ($from, $field) use ($current) {
                    return $current->$field !== $from;
                }, ARRAY_FILTER_USE_BOTH);
                app(LifecycleNotificationService::class)->transitioned($current, $changed);
            }
            return $saved;
        });
    }
}
