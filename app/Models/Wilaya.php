<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wilaya extends Model
{
    public $incrementing = false;

    protected $guarded = [];

    public function communes(): HasMany
    {
        return $this->hasMany(Commune::class);
    }
}
