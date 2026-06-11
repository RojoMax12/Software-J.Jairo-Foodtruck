<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class Estado_cotizacion extends Model
{

    use HasFactory;

    protected $table = 'estados_cotizacion';

    protected $fillable = [
        'nombre_estado'
    ];  

    public function cotizaciones(): BelongsTo
    {
        return $this->belongsTo(Cotizacion::class, 'id_cotizacion');
    }

}
