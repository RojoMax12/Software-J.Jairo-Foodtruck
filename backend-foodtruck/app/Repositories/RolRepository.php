<?php

namespace App\Repositories; //se define el nombre del espacio en este caso se trabaja en App\Repositories
use App\Models\Rol; //Se importa el modelo Rol

class RolRepository
{   
    //Se definen los métodos para interactuar con la base de datos a través del modelo Rol
    //Se utiliza la sintexis de Eloquent para realizar las operaciones CRUD (Crear, Leer, Actualizar, Eliminar) en la tabla roles
    //Se puede utilizar otra sintaxis de consulta
    
    public function getAllRoles()
    {
        return Rol::all();
    }

    public function getRoleById($id)
    {
        return Rol::find($id);
    }

    public function createRole($data)
    {
        return Rol::create($data);
    }

    public function updateRole($id, $data)
    {
        $role = Rol::find($id);
        if ($role) {
            $role->update($data);
            return $role;
        }
        return null;
    }

    public function deleteRole($id)
    {
        $role = Rol::find($id);
        if ($role) {
            $role->delete();
            return true;
        }
        return false;
    }
}