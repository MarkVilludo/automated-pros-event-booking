<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;

trait InvalidatesCache
{
    protected function invalidateTags(array $tags): void
    {
        Cache::tags($tags)->flush();
    }

    protected function forgetKey(string $key): bool
    {
        return Cache::forget($key);
    }
}
