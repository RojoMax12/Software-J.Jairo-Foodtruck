<?php

namespace App\Services;

use App\Repositories\UsuarioRepository;
use App\Services\JwtService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

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

    //Generar el JWT de recuperación y mandar el correo
    public function enviarEnlaceRecuperacion($identificador)
    {   
        //Buscamos el usuario por el identificador (nombre)
        $usuario = $this->usuarioRepository->getUsuarioByNombre($identificador);

        if (!$usuario) {
            return false; 
        }

        $tokenPayload = $this->jwtService->issueForUser($usuario, [
            'purpose' => 'password_reset',
            'identificador' => $identificador
        ]);

        $token = $tokenPayload['access_token'];

        // URL hacia tu Frontend en Vue.js pepito
        $urlFrontend = "http://localhost:5173/reset-password?token=" . $token;

        $correoDestinatario = $usuario->nombre; // Usamos el nombre asumiendo que guarda el formato de correo

        Mail::send('emails.recuperar_password', ['url' => $urlFrontend], function($message) use ($correoDestinatario) {
            $message->to($correoDestinatario);
            $message->subject('Restablecer Contraseña - DiCreme');
        });

        return true;
    }

    //Validar el JWT de vuelta desde Vue y cambiar la contraseña en la BD
    public function restablecerContrasena($token, $nuevaContrasena)
    {
        try {
            $claims = $this->jwtService->decode($token);

            if (!isset($claims->purpose) || $claims->purpose !== 'password_reset') {
                throw new \Exception("El token provisto no es válido para esta operación.");
            }

            // Extracción limpia del identificador
            $identificador = '';
            if (isset($claims->identificador)) {
                $identificador = $claims->identificador;
            } elseif (isset($claims->email)) {
                // Compatibilidad hacia atrás si hay tokens viejos
                $identificador = is_object($claims->email) ? ($claims->email->email ?? '') : $claims->email;
            }

            // BÚSQUEDA
            $usuario = $this->usuarioRepository->getUsuarioByNombre($identificador);

            if (!$usuario) {
                throw new \Exception("El usuario asociado a este identificador ($identificador) ya no existe en el sistema.");
            }

            $nuevaContrasenaHasheada = Hash::make($nuevaContrasena);

            $this->usuarioRepository->updateUsuario($usuario->id, [
                'contrasena' => $nuevaContrasenaHasheada
            ]);

            $this->jwtService->blacklist($claims);

            return true;

        } catch (\Exception $e) {
            throw new \Exception("Error al procesar la solicitud: " . $e->getMessage());
        }
    }
}