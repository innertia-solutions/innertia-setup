<script setup>
definePageMeta({ layout: 'backoffice', middleware: ['auth'] })

const permissionCategories = [
  {
    alias: 'Usuarios',
    icon: 'i-lucide-users',
    permissions: [
      { key: 'users.view',           description: 'Ver listado y detalle de usuarios' },
      { key: 'users.manage',         description: 'Crear y editar usuarios' },
      { key: 'users.assign_roles',   description: 'Asignar roles a usuarios' },
      { key: 'users.reset_password', description: 'Restablecer contraseñas de usuarios' },
    ],
  },
  {
    alias: 'Roles',
    icon: 'i-lucide-shield',
    permissions: [
      { key: 'roles.view',   description: 'Ver listado y detalle de roles' },
      { key: 'roles.manage', description: 'Crear, editar y eliminar roles' },
    ],
  },
  {
    alias: 'Permisos',
    icon: 'i-lucide-key',
    permissions: [
      { key: 'permissions.view', description: 'Ver permisos del sistema' },
      { key: 'permissions.sync', description: 'Sincronizar permisos con la configuración' },
    ],
  },
]
</script>

<template>
  <div>
    <VantageAdminPageHeader
      title="Permisos del sistema"
      description="Listado de todos los permisos disponibles, agrupados por categoría."
    />

    <div class="space-y-6">
      <div
        v-for="category in permissionCategories"
        :key="category.alias"
        class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden"
      >
        <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700 flex items-center gap-3">
          <div class="size-8 rounded-lg bg-slate-100 dark:bg-slate-700 flex items-center justify-center">
            <Icon :name="category.icon" class="size-4 text-slate-600 dark:text-slate-300" />
          </div>
          <div>
            <h2 class="text-sm font-semibold text-slate-800 dark:text-slate-200">{{ category.alias }}</h2>
            <p class="text-xs text-slate-400">{{ category.permissions.length }} permisos</p>
          </div>
        </div>

        <div class="p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
          <div
            v-for="perm in category.permissions"
            :key="perm.key"
            class="flex flex-col gap-1 p-3 rounded-lg border border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-700/30"
          >
            <span class="text-xs font-mono font-semibold text-blue-600 dark:text-blue-400">{{ perm.key }}</span>
            <span class="text-xs text-slate-500 dark:text-slate-400">{{ perm.description }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
