<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Stock extends Model
{
    use HasFactory;

    protected $table = 'stocks';

    protected $fillable = [
        'cantidad_stock'
    ];

    protected $casts = [
        'cantidad_stock' => 'integer',
    ];

    public function lotes(): HasMany
    {
        return $this->hasMany(Lote::class, 'id_stock');
    }

}