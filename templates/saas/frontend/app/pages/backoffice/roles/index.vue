<template>
  <div>
    <!-- Page header -->
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-xl font-bold text-gray-900">Roles</h1>
      <button
        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-indigo-600 text-indigo-600 text-sm font-medium hover:bg-indigo-50 transition-colors"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Nuevo rol
      </button>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-100">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
              <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descripción</th>
              <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuarios</th>
              <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Permisos</th>
              <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="role in roles" :key="role.id" class="hover:bg-gray-50 transition-colors">
              <td class="px-5 py-3">
                <span class="text-sm font-semibold text-gray-900">{{ role.name }}</span>
              </td>
              <td class="px-5 py-3 text-sm text-gray-500">{{ role.description }}</td>
              <td class="px-5 py-3 text-sm text-gray-700">{{ role.users }}</td>
              <td class="px-5 py-3 text-sm text-gray-700">{{ role.permissions }}</td>
              <td class="px-5 py-3">
                <div class="flex items-center gap-2">
                  <button class="text-xs text-indigo-600 hover:text-indigo-800 font-medium transition-colors">Ver</button>
                  <button class="text-xs text-gray-500 hover:text-gray-700 font-medium transition-colors">Editar</button>
                  <button class="text-xs text-red-500 hover:text-red-700 font-medium transition-colors">Eliminar</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Permissions per role accordion -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
      <div class="px-5 py-4 border-b border-gray-100">
        <h2 class="text-base font-semibold text-gray-800">Permisos por rol</h2>
      </div>
      <div class="divide-y divide-gray-100">
        <div v-for="role in roles" :key="'acc-' + role.id">
          <button
            class="w-full flex items-center justify-between px-5 py-4 text-left hover:bg-gray-50 transition-colors"
            @click="toggle(role.id)"
          >
            <span class="text-sm font-medium text-gray-800">{{ role.name }}</span>
            <svg
              class="w-4 h-4 text-gray-400 transition-transform"
              :class="{ 'rotate-180': expanded.includes(role.id) }"
              fill="none" stroke="currentColor" viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>
          <div v-if="expanded.includes(role.id)" class="px-5 pb-4">
            <div class="flex flex-wrap gap-2">
              <span
                v-for="perm in role.permissionList"
                :key="perm"
                class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700"
              >
                {{ perm }}
              </span>
            </div>
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

const expanded = ref<number[]>([])

function toggle(id: number) {
  if (expanded.value.includes(id)) {
    expanded.value = expanded.value.filter(i => i !== id)
  } else {
    expanded.value = [...expanded.value, id]
  }
}

const roles = [
  {
    id: 1,
    name: 'Administrador',
    description: 'Acceso total al sistema',
    users: 2,
    permissions: 8,
    permissionList: ['users.view', 'users.manage', 'users.assign_roles', 'users.reset_password', 'roles.view', 'roles.manage', 'permissions.view', 'permissions.sync'],
  },
  {
    id: 2,
    name: 'Supervisor',
    description: 'Gestión de usuarios y visualización',
    users: 5,
    permissions: 4,
    permissionList: ['users.view', 'users.manage', 'roles.view', 'permissions.view'],
  },
  {
    id: 3,
    name: 'Operador',
    description: 'Acceso operativo básico',
    users: 10,
    permissions: 2,
    permissionList: ['users.view', 'roles.view'],
  },
  {
    id: 4,
    name: 'Viewer',
    description: 'Solo lectura',
    users: 7,
    permissions: 2,
    permissionList: ['users.view', 'permissions.view'],
  },
]
</script>
