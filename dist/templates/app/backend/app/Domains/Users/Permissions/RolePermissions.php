<?php

namespace App\Domains\Users\Permissions;

enum RolePermissions: string
{
    case View   = 'roles.view';
    case Manage = 'roles.manage';

    public function description(): string
    {
        return match ($this) {
            self::View   => 'Ver roles y sus permisos',
            self::Manage => 'Crear, editar y eliminar roles',
        };
    }
}
