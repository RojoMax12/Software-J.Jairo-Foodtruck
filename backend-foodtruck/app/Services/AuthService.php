<?php

namespace App\Services;

use App\Repositories\UsuarioRepository;
use App\Services\JwtService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class AuthService
{
    protected $usuarioRepository;
    protected $jwtService;

    public function __construct(
        UsuarioRepository $usuarioRepository, 
        JwtService $jwtService
    ) {
        $this->usuarioRepository = $usuarioRepository;
        $this->jwtService = $jwtService;
    }

    public function enviarEnlaceRecuperacion(string $correo): string
    {
        return Password::sendResetLink([
            'correo' => $correo,
        ]);
    }

    //Validar el JWT de vuelta desde Vue y cambiar la contraseña en la BD
    public function restablecerContrasena(
        string $token,
        string $correo,
        string $nuevaContrasena
    ): string {
        return Password::reset(
            [
                'correo' => $correo,
                'password' => $nuevaContrasena,
                'password_confirmation' => $nuevaContrasena,
                'token' => $token,
            ],
            function ($usuario, $password) {
                $usuario->contrasena = Hash::make($password);
                $usuario->save();
            }
        );
    }
    
}