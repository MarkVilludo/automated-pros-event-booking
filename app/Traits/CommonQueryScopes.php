<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait CommonQueryScopes
{
    public function scopeFilterByDate(Builder $query, array|string|null $date = null): Builder
    {
        if ($date === null) {
            return $query;
        }
        $model = $query->getModel();
        $column = property_exists($model, 'dateColumn') ? $model->dateColumn : 'date';
        if (is_string($date)) {
            $query->whereDate($column, $date);
            return $query;
        }
        if (! empty($date['from'])) {
            $query->whereDate($column, '>=', $date['from']);
        }
        if (! empty($date['to'])) {
            $query->whereDate($column, '<=', $date['to']);
        }
        return $query;
    }

    public function scopeSearchByTitle(Builder $query, ?string $search = null): Builder
    {
        if ($search === null || $search === '') {
            return $query;
        }
        $model = $query->getModel();
        $column = property_exists($model, 'titleColumn') ? $model->titleColumn : 'title';
        return $query->where($column, 'like', '%' . $search . '%');
    }
}
