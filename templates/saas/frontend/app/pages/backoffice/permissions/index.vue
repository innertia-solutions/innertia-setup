<template>
  <div>
    <!-- Page header -->
    <div class="mb-6">
      <h1 class="text-xl font-bold text-gray-900">Permisos del sistema</h1>
      <p class="text-sm text-gray-500 mt-1">Listado de todos los permisos disponibles, agrupados por categoría.</p>
    </div>

    <!-- Grouped permissions -->
    <div class="space-y-6">
      <div
        v-for="category in permissionCategories"
        :key="category.alias"
        class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden"
      >
        <!-- Category header -->
        <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-3">
          <div class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center">
            <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
            </svg>
          </div>
          <div>
            <h2 class="text-sm font-semibold text-gray-900">{{ category.alias }}</h2>
            <p class="text-xs text-gray-400">{{ category.permissions.length }} permiso{{ category.permissions.length !== 1 ? 's' : '' }}</p>
          </div>
        </div>

        <!-- Permissions grid -->
        <div class="p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
          <div
            v-for="perm in category.permissions"
            :key="perm.key"
            class="flex flex-col gap-1 p-3 rounded-lg border border-gray-100 bg-gray-50 hover:bg-gray-100 transition-colors"
          >
            <span class="text-xs font-mono font-semibold text-indigo-600">{{ perm.key }}</span>
            <span class="text-xs text-gray-500">{{ perm.description }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  layout: 'backoffice',
  middleware: ['auth'],
})

const permissionCategories = [
  {
    alias: 'Usuarios',
    permissions: [
      { key: 'users.view', description: 'Ver listado y detalle de usuarios' },
      { key: 'users.manage', description: 'Crear y editar usuarios' },
      { key: 'users.assign_roles', description: 'Asignar roles a usuarios' },
      { key: 'users.reset_password', description: 'Restablecer contraseñas de usuarios' },
    ],
  },
  {
    alias: 'Roles',
    permissions: [
      { key: 'roles.view', description: 'Ver listado y detalle de roles' },
      { key: 'roles.manage', description: 'Crear, editar y eliminar roles' },
    ],
  },
  {
    alias: 'Permisos',
    permissions: [
      { key: 'permissions.view', description: 'Ver permisos del sistema' },
      { key: 'permissions.sync', description: 'Sincronizar permisos con la configuración' },
    ],
  },
]
</script>
