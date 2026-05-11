<template>
  <div>
    <!-- Page header -->
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-xl font-bold text-gray-900">Usuarios</h1>
      <button
        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-indigo-600 text-indigo-600 text-sm font-medium hover:bg-indigo-50 transition-colors"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Nuevo usuario
      </button>
    </div>

    <!-- Search -->
    <div class="mb-5">
      <input
        v-model="search"
        type="text"
        placeholder="Buscar por nombre o email..."
        class="w-full max-w-sm px-4 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-white"
      />
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-100">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
              <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
              <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rol</th>
              <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
              <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Creado</th>
              <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="u in filteredUsers" :key="u.id" class="hover:bg-gray-50 transition-colors">
              <td class="px-5 py-3 text-sm text-gray-900 font-medium">{{ u.name }}</td>
              <td class="px-5 py-3 text-sm text-gray-500">{{ u.email }}</td>
              <td class="px-5 py-3">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-700">
                  {{ u.role }}
                </span>
              </td>
              <td class="px-5 py-3">
                <span
                  class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                  :class="u.active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'"
                >
                  {{ u.active ? 'Activo' : 'Inactivo' }}
                </span>
              </td>
              <td class="px-5 py-3 text-sm text-gray-400 whitespace-nowrap">{{ u.created }}</td>
              <td class="px-5 py-3">
                <div class="flex items-center gap-2">
                  <button class="text-xs text-indigo-600 hover:text-indigo-800 font-medium transition-colors">Ver</button>
                  <button class="text-xs text-gray-500 hover:text-gray-700 font-medium transition-colors">Editar</button>
                </div>
              </td>
            </tr>
            <tr v-if="filteredUsers.length === 0">
              <td colspan="6" class="px-5 py-8 text-center text-sm text-gray-400">
                No se encontraron usuarios.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  layout: 'backoffice',
  middleware: ['auth'],
})

const search = ref('')

const users = [
  { id: 1, name: 'Ana García', email: 'ana@empresa.com', role: 'Administrador', active: true, created: '01/04/2026' },
  { id: 2, name: 'Carlos López', email: 'carlos@empresa.com', role: 'Supervisor', active: true, created: '03/04/2026' },
  { id: 3, name: 'María Torres', email: 'maria@empresa.com', role: 'Operador', active: true, created: '05/04/2026' },
  { id: 4, name: 'Luis Ramírez', email: 'luis@empresa.com', role: 'Viewer', active: false, created: '07/04/2026' },
  { id: 5, name: 'Sofía Herrera', email: 'sofia@empresa.com', role: 'Operador', active: true, created: '10/04/2026' },
  { id: 6, name: 'Diego Morales', email: 'diego@empresa.com', role: 'Supervisor', active: true, created: '15/04/2026' },
  { id: 7, name: 'Valentina Cruz', email: 'vcruz@empresa.com', role: 'Viewer', active: false, created: '20/04/2026' },
  { id: 8, name: 'Rodrigo Peña', email: 'rpeña@empresa.com', role: 'Operador', active: true, created: '25/04/2026' },
]

const filteredUsers = computed(() => {
  const q = search.value.toLowerCase().trim()
  if (!q) return users
  return users.filter(
    u =>
      u.name.toLowerCase().includes(q) ||
      u.email.toLowerCase().includes(q)
  )
})
</script>
