<?php

namespace App\Support;

use App\Models\Company;

class CompanyContext
{
    public static function id(): int
    {
        $sessionId = session('company_id');

        if ($sessionId !== null && $sessionId !== '') {
            return (int) $sessionId;
        }

        return (int) config('sorting.default_company_id', 1);
    }

    public static function set(int $companyId): void
    {
        session(['company_id' => $companyId]);
    }

    public static function current(): ?Company
    {
        return Company::find(static::id());
    }
}
