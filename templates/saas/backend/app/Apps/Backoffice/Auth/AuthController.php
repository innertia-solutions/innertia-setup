<?php

namespace App\Apps\Backoffice\Auth;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends \Innertia\Auth\Http\Controllers\AuthController
{
    /**
     * GET /auth/me
     *
     * Returns the authenticated user with their permissions and available contexts.
     * Response shape expected by the frontend's fetchMe():
     *   { user, permissions, availableContexts }
     */
    public function me(Request $request): JsonResponse
    {
        $user     = $request->user();
        $userData = $user->toArray();

        if (method_exists($user, 'appKeys')) {
            $userData['apps'] = $user->appKeys();
        }

        // Permissions — from roles + direct grants
        $permissions = [];
        if (method_exists($user, 'roles')) {
            $viaRoles = $user->roles()
                ->with('permissions')
                ->get()
                ->flatMap(fn ($role) => $role->permissions->pluck('name'));

            $direct = method_exists($user, 'directPermissions')
                ? $user->directPermissions()->pluck('name')
                : collect();

            $permissions = $viaRoles->merge($direct)->unique()->values()->all();
        }

        // Available contexts (app keys the user has access to)
        $availableContexts = method_exists($user, 'appKeys') ? $user->appKeys() : [];

        return response()->json([
            'user'              => $userData,
            'permissions'       => $permissions,
            'availableContexts' => $availableContexts,
        ]);
    }
}
