<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = ['code', 'name', 'region'];

    public function branches()
    {
        return $this->hasMany(Branch::class);
    }
}
