<?php

namespace App\Domains\Permissions\Enums;

enum PermissionPermissions: string
{
    case View = 'permissions.view';
    case Sync = 'permissions.sync';

    public function description(): string
    {
        return match ($this) {
            self::View => 'Ver permisos disponibles del sistema',
            self::Sync => 'Sincronizar permisos con el sistema',
        };
    }
}
