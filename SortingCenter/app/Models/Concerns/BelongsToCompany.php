<?php

namespace App\Models\Concerns;

use App\Support\CompanyContext;
use Illuminate\Database\Eloquent\Builder;

trait BelongsToCompany
{
    public static function bootBelongsToCompany(): void
    {
        static::addGlobalScope('company', function (Builder $builder) {
            $builder->where($builder->getModel()->getTable().'.company_id', CompanyContext::id());
        });

        static::creating(function ($model) {
            if ($model->company_id === null) {
                $model->company_id = CompanyContext::id();
            }
        });
    }

    public function scopeForCompany(Builder $query, int $companyId): Builder
    {
        return $query->withoutGlobalScope('company')->where($this->getTable().'.company_id', $companyId);
    }
}
