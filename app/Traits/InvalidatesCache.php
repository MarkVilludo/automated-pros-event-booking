<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;

trait InvalidatesCache
{
    /**
     * Invalidate cache tags (e.g. after create/update/delete).
     *
     * @param  array<int, string>  $tags
     */
    protected function invalidateTags(array $tags): void
    {
        Cache::tags($tags)->flush();
    }

    /**
     * Forget a single cache key.
     */
    protected function forgetKey(string $key): bool
    {
        return Cache::forget($key);
    }
}
