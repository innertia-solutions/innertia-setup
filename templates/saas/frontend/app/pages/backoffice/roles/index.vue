<script setup>
definePageMeta({ layout: 'backoffice', middleware: ['auth'] })

const expanded = ref([])

function toggle(id) {
  expanded.value = expanded.value.includes(id)
    ? expanded.value.filter(i => i !== id)
    : [...expanded.value, id]
}

const roles = [
  {
    id: 1,
    name: 'Administrador',
    description: 'Acceso total al sistema',
    users: 2,
    permissionList: ['users.view', 'users.manage', 'users.assign_roles', 'users.reset_password', 'roles.view', 'roles.manage', 'permissions.view', 'permissions.sync'],
  },
  {
    id: 2,
    name: 'Supervisor',
    description: 'Gestión de usuarios y visualización',
    users: 5,
    permissionList: ['users.view', 'users.manage', 'roles.view', 'permissions.view'],
  },
  {
    id: 3,
    name: 'Operador',
    description: 'Acceso operativo básico',
    users: 10,
    permissionList: ['users.view', 'roles.view'],
  },
  {
    id: 4,
    name: 'Viewer',
    description: 'Solo lectura',
    users: 7,
    permissionList: ['users.view', 'permissions.view'],
  },
]
</script>

<template>
  <div>
    <VantageAdminPageHeader title="Roles" description="Gestión de roles y sus permisos.">
      <template #actions>
        <VantageButton text="Nuevo rol" severity="primary" size="sm" />
      </template>
    </VantageAdminPageHeader>

    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden mb-6">
      <table class="min-w-full">
        <thead class="bg-slate-50 dark:bg-slate-700/50">
          <tr>
            <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Nombre</th>
            <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Descripción</th>
            <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Usuarios</th>
            <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Permisos</th>
            <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Acciones</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
          <tr v-for="role in roles" :key="role.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
            <td class="px-5 py-3 text-sm font-semibold text-slate-800 dark:text-slate-200">{{ role.name }}</td>
            <td class="px-5 py-3 text-sm text-slate-500 dark:text-slate-400">{{ role.description }}</td>
            <td class="px-5 py-3 text-sm text-slate-700 dark:text-slate-300">{{ role.users }}</td>
            <td class="px-5 py-3 text-sm text-slate-700 dark:text-slate-300">{{ role.permissionList.length }}</td>
            <td class="px-5 py-3">
              <div class="flex items-center gap-2">
                <VantageButton text="Ver"      severity="secondary" size="xs" variant="dropdown" />
                <VantageButton text="Editar"   severity="secondary" size="xs" variant="dropdown" />
                <VantageButton text="Eliminar" severity="danger"    size="xs" variant="dropdown" />
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Permissions per role -->
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
      <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700">
        <h2 class="text-sm font-semibold text-slate-800 dark:text-slate-200">Permisos por rol</h2>
      </div>
      <div class="divide-y divide-slate-100 dark:divide-slate-700">
        <div v-for="role in roles" :key="'acc-' + role.id">
          <button
            class="w-full flex items-center justify-between px-5 py-4 text-left hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors"
            @click="toggle(role.id)"
          >
            <span class="text-sm font-medium text-slate-800 dark:text-slate-200">{{ role.name }}</span>
            <Icon
              name="i-lucide-chevron-down"
              class="size-4 text-slate-400 transition-transform"
              :class="{ 'rotate-180': expanded.includes(role.id) }"
            />
          </button>
          <div v-if="expanded.includes(role.id)" class="px-5 pb-4 flex flex-wrap gap-2">
            <VantageTag
              v-for="perm in role.permissionList"
              :key="perm"
              :text="perm"
              severity="secondary"
              size="xs"
            />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
