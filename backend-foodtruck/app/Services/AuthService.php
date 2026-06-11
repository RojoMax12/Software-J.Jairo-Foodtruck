<?php

namespace App\Services;

use App\Repositories\Usuario_dicremeRepository;
use App\Repositories\Usuario_distribuidoresRepository;
use App\Services\JwtService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AuthService
{
    protected $usuariodicremeRepository;
    protected $usuariodistribuidorRepository;
    protected $jwtService;

    public function __construct(
        Usuario_dicremeRepository $usuariodicremeRepository, 
        Usuario_distribuidoresRepository $usuariodistribuidorRepository,
        JwtService $jwtService
    ) {
        $this->usuariodicremeRepository = $usuariodicremeRepository;
        $this->jwtService = $jwtService;
        $this->usuariodistribuidorRepository = $usuariodistribuidorRepository;
    }

    /**
     * PASO 1: Generar el JWT de recuperación y mandar el correo
     */
    public function enviarEnlaceRecuperacion($email)
    {   

        $usuario = $this->usuariodicremeRepository->getUsuarioDicremeByCorreo($email);
        

        if (!$usuario) {
            $usuario = $this->usuariodistribuidorRepository->getUsuarioDistribuidorByCorreo($email);
        }


        if (!$usuario) {
            return false; 
        }

        $tokenPayload = $this->jwtService->issueForUser($usuario, [
            'purpose' => 'password_reset',
            'email' => $email
        ]);

        $token = $tokenPayload['access_token'];

        // URL hacia tu Frontend en Vue.js pepito
        $urlFrontend = "http://localhost:5173/reset-password?token=" . $token;

        $correoDestinatario = $usuario->correo_electronico; 

        Mail::send('emails.recuperar_password', ['url' => $urlFrontend], function($message) use ($correoDestinatario) {
            $message->to($correoDestinatario);
            $message->subject('Restablecer Contraseña - DiCreme');
        });

        return true;
    }

    /**
     * PASO 2: Validar el JWT de vuelta desde Vue y cambiar la contraseña en la BD
     */
    public function restablecerContrasena($token, $nuevaContrasena)
    {
  
        $tipoUsuario = null; 

        try {
            $claims = $this->jwtService->decode($token);

            if (!isset($claims->purpose) || $claims->purpose !== 'password_reset') {
                throw new \Exception("El token provisto no es válido para esta operación.");
            }

            // Extracción limpia del correo (El bloque tolerante a fallos que armamos antes)
            $emailLimpio = '';
            if (isset($claims->email)) {
                if (is_string($claims->email)) {
                    $emailLimpio = $claims->email;
                } elseif (is_object($claims->email) && isset($claims->email->email)) {
                    $emailLimpio = $claims->email->email;
                } else {
                    $emailLimpio = $claims->email->correo_electronico ?? $claims->email->correo ?? '';
                }
            }

            // BÚSQUEDA
            $usuario = $this->usuariodicremeRepository->getUsuarioDicremeByCorreo($emailLimpio);
            $tipoUsuario = 'dicreme'; 

            if (!$usuario) {
                $usuario = $this->usuariodistribuidorRepository->getUsuarioDistribuidorByCorreo($emailLimpio);
                $tipoUsuario = 'distribuidor'; 
            }

            if (!$usuario) {
                throw new \Exception("El usuario asociado a este correo ($emailLimpio) ya no existe en el sistema.");
            }


            $nuevaContrasenaHasheada = \Illuminate\Support\Facades\Hash::make($nuevaContrasena);

            if ($tipoUsuario === 'dicreme') {
                $this->usuariodicremeRepository->updateUsuarioDicreme($usuario->id, [
                    'contrasena' => $nuevaContrasenaHasheada
                ]);
            } else {
                $this->usuariodistribuidorRepository->updateUsuarioDistribuidor($usuario->id, [
                    'contrasena' => $nuevaContrasenaHasheada
                ]);
            }

            $this->jwtService->blacklist($claims);

            return true;

        } catch (\Exception $e) {

            throw new \Exception("Error al procesar la solicitud: " . $e->getMessage());
        }
    }
}