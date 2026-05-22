<?php

namespace App\Enums;

/**
 * Permisos disponibles para las API keys de este producto.
 *
 * Cada case es un permiso que puede asignarse a una API key de un client.
 * Olimpo los lee via GET /olimpo/api-keys/permissions para mostrarlos al crear keys.
 *
 * Proteger rutas con el middleware 'apikey':
 *   Route::middleware('apikey')->group(...)                    // solo autenticación
 *   Route::middleware('apikey:resource.action')->get(...)      // autenticación + permiso
 */
enum ApiPermissions: string
{
    // Define los permisos de tu producto aquí. Ejemplos:
    // case ResourceRead   = 'resource.read';
    // case ResourceWrite  = 'resource.write';
    // case ResourceDelete = 'resource.delete';
    // case AdminAccess    = 'admin.access';

    public function description(): string
    {
        return match($this) {
            // self::ResourceRead   => 'Consultar recursos',
            // self::ResourceWrite  => 'Crear y actualizar recursos',
            // self::ResourceDelete => 'Eliminar recursos',
            // self::AdminAccess    => 'Acceso al panel de administración',
        };
    }
}
