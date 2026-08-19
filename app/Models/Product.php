<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['active' => 'boolean', 'free_shipping' => 'boolean', 'track_inventory' => 'boolean'];
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }
}
