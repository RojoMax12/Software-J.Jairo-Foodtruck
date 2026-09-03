<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistorialMovimiento extends Model
{
    use HasFactory;

    protected $table = 'historial_movimientos';

    protected $primaryKey = 'id_historial';

    protected $fillable = [
        'tipo',
        'accion',
        'descripcion',
        'entidad',
        'detalle',
        'usuario',
        'id_usuario',
        'monto',
        'fecha',
    ];

    protected $casts = [
        'monto' => 'float',
        'fecha' => 'datetime',
    ];

    public function usuarioRel()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    /**
     * Registrar un movimiento de auditoría en la plataforma
     */
    public static function registrar(
        string $tipo,
        string $accion,
        string $descripcion,
        string $entidad,
        ?string $detalle = null,
        $monto = 0,
        $user = null
    ) {
        try {
            if (!$user) {
                $user = \Illuminate\Support\Facades\Auth::user();
                if (!$user && function_exists('request') && request()) {
                    $user = request()->user();
                }

                if (!$user && function_exists('request') && request() && request()->bearerToken()) {
                    try {
                        $jwtService = app(\App\Services\JwtService::class);
                        $claims = $jwtService->decode(request()->bearerToken());
                        if ($claims && !empty($claims->sub)) {
                            $user = \App\Models\Usuario::find($claims->sub);
                        }
                    } catch (\Throwable) {
                        // Ignorar fallo de decodificación de token
                    }
                }
            }

            $nombreUsuario = 'Sistema / Cliente Online';
            $idUsuario = null;

            if ($user) {
                $rol = (int)($user->id_rol ?? 0) === 1 ? 'Admin' : ((int)($user->id_rol ?? 0) === 3 ? 'Personal' : 'Cliente');
                $nombreUsuario = trim(($user->nombre ?? 'Usuario') . ' ' . ($user->apellido ?? '')) . ' (' . $rol . ')';
                $idUsuario = $user->id_usuario ?? null;
            }

            // Evitar registros duplicados en ráfaga (mismo tipo, acción, entidad y usuario en menos de 10 segundos)
            $duplicadoReciente = self::where('tipo', $tipo)
                ->where('accion', $accion)
                ->where('entidad', $entidad)
                ->where('id_usuario', $idUsuario)
                ->where('created_at', '>=', now()->subSeconds(10))
                ->latest('id_historial')
                ->first();

            if ($duplicadoReciente) {
                if ($detalle && $duplicadoReciente->detalle !== $detalle) {
                    $duplicadoReciente->update([
                        'detalle' => $detalle,
                        'monto' => $monto ? (float)$monto : $duplicadoReciente->monto,
                        'fecha' => now(),
                    ]);
                }
                return $duplicadoReciente;
            }

            return self::create([
                'tipo' => $tipo,
                'accion' => $accion,
                'descripcion' => $descripcion,
                'entidad' => $entidad,
                'detalle' => $detalle,
                'usuario' => $nombreUsuario,
                'id_usuario' => $idUsuario,
                'monto' => $monto ? (float)$monto : 0,
                'fecha' => now(),
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Error al registrar auditoría en historial_movimientos: ' . $e->getMessage());
            return null;
        }
    }
}