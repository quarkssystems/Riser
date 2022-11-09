<?php

namespace App\Http\Traits;
use App\Employee;

trait CommonTrait {
    public function scopeStatus($query, $status = null)
    {
        $status = $status ?? config('constant.status.active_value');
        return $query->where('status', $status);
    }

    public function scopeStatusIn($query, $status = array())
    {
        $status = $status ?? [config('constant.status.active_value')];
        return $query->whereIn('status', $status);
    }
}