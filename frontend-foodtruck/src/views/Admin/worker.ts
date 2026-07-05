export interface Role {

    id: number
    nombre: string

}

export interface Worker {

    id: number
    nombre: string
    rol: Role
    activo: boolean

}