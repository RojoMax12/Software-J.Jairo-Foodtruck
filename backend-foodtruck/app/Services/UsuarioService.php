<?php
namespace App\Services;

use App\Repositories\UsuarioRepository;
use App\Models\HistorialMovimiento;
use Illuminate\Support\Facades\Hash;
use App\Exceptions\DuplicateEmailException;

class UsuarioService
{
    protected $usuarioRepository;

    public function __construct(UsuarioRepository $usuarioRepository)
    {
        $this->usuarioRepository = $usuarioRepository;
    }

    public function createUsuario($data)
    {

        if ($this->usuarioRepository->existsByEmail($data['correo'])) {
            throw new DuplicateEmailException('El correo electrónico ya está registrado.');
        }

        $data['contrasena'] = Hash::make($data['contrasena']);
        
        $usuario = $this->usuarioRepository->createUsuario($data);
        if ($usuario) {
            $nombre = trim(($usuario->nombre ?? 'Usuario') . ' ' . ($usuario->apellido ?? ''));
            $rol = (int)($usuario->id_rol ?? 0) === 1 ? 'Administrador' : ((int)($usuario->id_rol ?? 0) === 3 ? 'Trabajador / Personal' : 'Cliente');
            HistorialMovimiento::registrar(
                'trabajador',
                'crear',
                'Trabajador registrado en el sistema',
                $nombre,
                "Nuevo usuario {$nombre} registrado con rol {$rol} y correo {$usuario->correo}."
            );
        }
        return $usuario;
    }

    public function getAllUsuarios()
    {
        return $this->usuarioRepository->getAllUsuarios();
    }

    public function getUsuarioById($id)
    {
        return $this->usuarioRepository->getUsuarioById($id);
    }

    public function getUsuarioByNombre($nombre)
    {
        return $this->usuarioRepository->getUsuarioByNombre($nombre);
    }

    public function getUsuariosByRolId($idRol)
    {
        return $this->usuarioRepository->getUsuariosByRolId($idRol);
    }

    public function getUsuariosActivos()
    {
        return $this->usuarioRepository->getUsuariosActivos();
    }

    public function getPedidosByUsuarioId($id)
    {
        return $this->usuarioRepository->getPedidosByUsuarioId($id);
    }

    public function getCajasByUsuarioId($id)
    {
        return $this->usuarioRepository->getCajasByUsuarioId($id);
    }

    public function getUsuariosAdministrativos()
    {
        return $this->usuarioRepository->getUsuariosAdministrativos();
    }

    public function updateUsuario($id, $data)
    {
        // Evitar que el administrador se desactive a sí mismo
        $authUser = auth()->user();
        if ($authUser && (int)$authUser->id_usuario === (int)$id) {
            $desactivando = (isset($data['estado']) && !(bool)$data['estado']) ||
                            (isset($data['id_estado_usuario']) && (int)$data['id_estado_usuario'] === 2);
            if ($desactivando) {
                throw new \InvalidArgumentException('Un administrador no puede desactivar su propia cuenta.');
            }
        }

        if (
            isset($data['correo']) &&
            $this->usuarioRepository->existsByEmailExceptUser(
                $data['correo'],
                $id
            )
        ) {
            throw new DuplicateEmailException(
                'El correo electrónico ya está registrado.'
            );
        }

        if (!empty($data['contrasena'])) {
            $data['contrasena'] = Hash::make($data['contrasena']);
        } else {
            unset($data['contrasena']);
        }

        $usuarioAnterior = $this->usuarioRepository->getUsuarioById($id);
        $resultado = $this->usuarioRepository->updateUsuario($id, $data);
        $usuarioActualizado = $this->usuarioRepository->getUsuarioById($id);

        if ($usuarioAnterior && $usuarioActualizado) {
            $nombre = trim(($usuarioActualizado->nombre ?? 'Usuario') . ' ' . ($usuarioActualizado->apellido ?? ''));

            // 1. Cambio de estado (Activar / Desactivar)
            $cambioEstado = false;
            $nuevoEstadoTexto = '';
            if (isset($data['estado']) && (bool)$data['estado'] !== (bool)$usuarioAnterior->estado) {
                $cambioEstado = true;
                $nuevoEstadoTexto = (bool)$data['estado'] ? 'ACTIVADO' : 'DESACTIVADO';
            } elseif (isset($data['id_estado_usuario']) && (int)$data['id_estado_usuario'] !== (int)$usuarioAnterior->id_estado_usuario) {
                $cambioEstado = true;
                $nuevoEstadoTexto = (int)$data['id_estado_usuario'] === 1 ? 'ACTIVADO' : 'DESACTIVADO';
            }

            if ($cambioEstado) {
                HistorialMovimiento::registrar(
                    'trabajador',
                    'estado',
                    "Trabajador {$nuevoEstadoTexto}",
                    $nombre,
                    "El estado de acceso para {$nombre} fue cambiado a {$nuevoEstadoTexto}."
                );
            } else {
                // 2. Modificación de datos generales
                HistorialMovimiento::registrar(
                    'trabajador',
                    'editar',
                    'Datos de trabajador actualizados',
                    $nombre,
                    "Se modificaron los datos del trabajador {$nombre} ({$usuarioActualizado->correo})."
                );
            }
        }

        return $resultado;
    }

    public function deleteUsuarioById($id)
    {
        $authUser = auth()->user();
        if ($authUser && (int)$authUser->id_usuario === (int)$id) {
            throw new \InvalidArgumentException('Un administrador no puede eliminar su propia cuenta.');
        }

        $usuario = $this->usuarioRepository->getUsuarioById($id);
        $nombre = $usuario ? trim(($usuario->nombre ?? 'Usuario') . ' ' . ($usuario->apellido ?? '')) : "ID #{$id}";
        HistorialMovimiento::registrar(
            'trabajador',
            'eliminar',
            'Trabajador eliminado',
            $nombre,
            "Se eliminó el perfil del trabajador {$nombre} del sistema."
        );
        return $this->usuarioRepository->deleteUsuarioById($id);
    }
}
