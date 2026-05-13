<?php

namespace App\Domains\Users\Permissions;

enum SystemPermissions: string
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
