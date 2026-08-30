<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use HasFactory, CanResetPassword, Notifiable;

    protected $table = 'usuarios';

    protected $primaryKey = 'id_usuario';

    protected $fillable = [
        'id_rol',
        'nombre',
        'estado',
        'contrasena',
        'correo',
    ];

    protected $hidden = [
        'contrasena',
    ];
 
    protected $casts = [
        'estado' => 'boolean',
    ];
 
    // Un usuario pertenece a un rol
    public function rol()
    {
        return $this->belongsTo(Rol::class, 'id_rol');
    }
 
    // Un usuario tiene muchos pedidos
    public function pedido()
    {
        return $this->hasMany(Pedido::class, 'id_usuario');
    }
 
    // Un usuario tiene muchos registros de caja (solo admin), obs: también habría que dejar que la vendedora tenga acceso a la caja.
    public function caja()
    {
        return $this->hasMany(Caja::class, 'id_usuario');
    }

    // Funciones para que Laravel reconozca la contraseña del usuario
    public function getAuthPasswordName()
    {
        return 'contrasena';
    }

    public function getAuthPassword()
    {
        return $this->contrasena;
    }

    public function getAuthIdentifierName()
    {
        return 'id_usuario';
    }

    // Funciones para recuperación de contraseña
    public function getEmailForPasswordReset()
    {
        return $this->correo;
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new \App\Notifications\ResetPasswordNotification($token));
    }

    public function routeNotificationForMail(): string
    {
        return $this->correo;
    }
}