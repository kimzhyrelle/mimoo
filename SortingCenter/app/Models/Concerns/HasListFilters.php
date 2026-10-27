<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Throwable;

trait HasListFilters
{
    /**
     * Apply the shared ?q, sort, status, rider, area_id, from, to filters.
     *
     * @param  array<string, mixed>  $filters
     */
    public function scopeListFilters(Builder $query, array $filters, ?string $dateColumn = null): Builder
    {
        return $query
            ->listSearch($filters['q'] ?? null)
            ->listStatus($filters['status'] ?? null)
            ->listRider($filters['rider'] ?? null)
            ->listArea($filters['area_id'] ?? null)
            ->listDateRange($filters['from'] ?? null, $filters['to'] ?? null, $dateColumn)
            ->listSort($filters['sort'] ?? null);
    }

    public function scopeListSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        $searchable = $this->listSearchable();
        $relations = $this->listSearchRelations();

        if ($searchable === [] && $relations === []) {
            return $query;
        }

        $like = '%'.addcslashes($term, '\\\\%_').'%';

        return $query->where(function (Builder $nested) use ($searchable, $relations, $like) {
            foreach ($searchable as $column) {
                $nested->orWhere($column, 'like', $like);
            }

            foreach ($relations as $relation => $columns) {
                $nested->orWhereHas($relation, function (Builder $relationQuery) use ($columns, $like) {
                    $relationQuery->where(function (Builder $columnQuery) use ($columns, $like) {
                        foreach ($columns as $column) {
                            $columnQuery->orWhere($column, 'like', $like);
                        }
                    });
                });
            }
        });
    }

    public function scopeListSort(Builder $query, ?string $sort): Builder
    {
        $sort = trim((string) $sort);

        if ($sort === '') {
            return $query;
        }

        $definition = $this->listSortable()[$sort] ?? null;

        if (! is_array($definition) || count($definition) !== 2) {
            return $query;
        }

        [$column, $direction] = $definition;

        if (! is_string($column) || $column === '') {
            return $query;
        }

        return $query->orderBy($column, strtolower((string) $direction) === 'desc' ? 'desc' : 'asc');
    }

    public function scopeListStatus(Builder $query, ?string $status): Builder
    {
        $status = trim((string) $status);

        if ($status === '') {
            return $query;
        }

        $mapped = $this->listStatusFilters()[$status] ?? null;

        if (is_array($mapped)) {
            foreach ($mapped as $column => $value) {
                $query->where($column, $value);
            }

            return $query;
        }

        $column = $this->listStatusColumn();

        return $column === '' ? $query : $query->where($column, $status);
    }

    public function scopeListRider(Builder $query, mixed $riderId): Builder
    {
        if (! filled($riderId)) {
            return $query;
        }

        $relation = $this->listRiderRelation();

        if ($relation !== '') {
            return $query->whereHas($relation, fn (Builder $related) => $related->where('rider_id', (int) $riderId));
        }

        $column = $this->listRiderColumn();

        return $column === '' ? $query : $query->where($column, (int) $riderId);
    }

    public function scopeListArea(Builder $query, mixed $areaId): Builder
    {
        if (! filled($areaId)) {
            return $query;
        }

        $relation = $this->listAreaRelation();

        if ($relation !== '') {
            return $query->whereHas($relation, fn (Builder $related) => $related->where('area_id', (int) $areaId));
        }

        $column = $this->listAreaColumn();

        return $column === '' ? $query : $query->where($column, (int) $areaId);
    }

    public function scopeListDateRange(Builder $query, mixed $from, mixed $to, ?string $dateColumn = null): Builder
    {
        $column = $dateColumn ?? $this->listDateColumn();

        if ($column === '') {
            return $query;
        }

        try {
            if (filled($from)) {
                $query->whereDate($column, '>=', Carbon::parse((string) $from)->toDateString());
            }

            if (filled($to)) {
                $query->whereDate($column, '<=', Carbon::parse((string) $to)->toDateString());
            }
        } catch (Throwable) {
            return $query;
        }

        return $query;
    }

    /**
     * @return array<int, string>
     */
    protected function listSearchable(): array
    {
        return [];
    }

    /**
     * @return array<string, array<int, string>>
     */
    protected function listSearchRelations(): array
    {
        return [];
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    protected function listSortable(): array
    {
        return [];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    protected function listStatusFilters(): array
    {
        return [];
    }

    protected function listStatusColumn(): string
    {
        return 'status';
    }

    protected function listRiderColumn(): string
    {
        return '';
    }

    protected function listRiderRelation(): string
    {
        return '';
    }

    protected function listAreaColumn(): string
    {
        return '';
    }

    protected function listAreaRelation(): string
    {
        return '';
    }

    protected function listDateColumn(): string
    {
        return 'created_at';
    }
}
