<?php

namespace App\Domains\Users\Permissions;

enum UserPermissions: string
{
    case View          = 'users.view';
    case Manage        = 'users.manage';
    case AssignRoles   = 'users.assign_roles';
    case ResetPassword = 'users.reset_password';

    public function description(): string
    {
        return match ($this) {
            self::View          => 'Ver lista de usuarios y detalles',
            self::Manage        => 'Crear, editar y eliminar usuarios',
            self::AssignRoles   => 'Asignar roles a usuarios',
            self::ResetPassword => 'Restablecer contraseñas de usuarios',
        };
    }
}
