<script setup>
definePageMeta({ layout: 'backoffice', middleware: ['auth'] })

const search = ref('')

const users = [
  { id: 1, name: 'Ana García',    email: 'ana@empresa.com',    role: 'Administrador', active: true,  created: '01/04/2026' },
  { id: 2, name: 'Carlos López',  email: 'carlos@empresa.com', role: 'Supervisor',    active: true,  created: '03/04/2026' },
  { id: 3, name: 'María Torres',  email: 'maria@empresa.com',  role: 'Operador',      active: true,  created: '05/04/2026' },
  { id: 4, name: 'Luis Ramírez',  email: 'luis@empresa.com',   role: 'Viewer',        active: false, created: '07/04/2026' },
  { id: 5, name: 'Sofía Herrera', email: 'sofia@empresa.com',  role: 'Operador',      active: true,  created: '10/04/2026' },
  { id: 6, name: 'Diego Morales', email: 'diego@empresa.com',  role: 'Supervisor',    active: true,  created: '15/04/2026' },
  { id: 7, name: 'Valentina Cruz',email: 'vcruz@empresa.com',  role: 'Viewer',        active: false, created: '20/04/2026' },
  { id: 8, name: 'Rodrigo Peña',  email: 'rpena@empresa.com',  role: 'Operador',      active: true,  created: '25/04/2026' },
]

const filteredUsers = computed(() => {
  const q = search.value.toLowerCase().trim()
  if (!q) return users
  return users.filter(u => u.name.toLowerCase().includes(q) || u.email.toLowerCase().includes(q))
})
</script>

<template>
  <div>
    <VantageAdminPageHeader title="Usuarios" description="Gestión de usuarios del sistema.">
      <template #actions>
        <VantageButton text="Nuevo usuario" severity="primary" size="sm" />
      </template>
    </VantageAdminPageHeader>

    <div class="mb-5 max-w-sm">
      <VantageInput v-model="search" placeholder="Buscar por nombre o email..." />
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
      <table class="min-w-full">
        <thead class="bg-slate-50 dark:bg-slate-700/50">
          <tr>
            <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Nombre</th>
            <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Email</th>
            <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Rol</th>
            <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Estado</th>
            <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Creado</th>
            <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Acciones</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
          <tr v-for="u in filteredUsers" :key="u.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
            <td class="px-5 py-3 text-sm font-medium text-slate-800 dark:text-slate-200">{{ u.name }}</td>
            <td class="px-5 py-3 text-sm text-slate-500 dark:text-slate-400">{{ u.email }}</td>
            <td class="px-5 py-3">
              <VantageTag :text="u.role" severity="secondary" size="xs" />
            </td>
            <td class="px-5 py-3">
              <VantageTag
                :text="u.active ? 'Activo' : 'Inactivo'"
                :severity="u.active ? 'success' : 'secondary'"
                size="xs"
              />
            </td>
            <td class="px-5 py-3 text-sm text-slate-400 whitespace-nowrap">{{ u.created }}</td>
            <td class="px-5 py-3">
              <div class="flex items-center gap-2">
                <VantageButton text="Ver"    severity="secondary" size="xs" variant="dropdown" />
                <VantageButton text="Editar" severity="secondary" size="xs" variant="dropdown" />
              </div>
            </td>
          </tr>
          <tr v-if="filteredUsers.length === 0">
            <td colspan="6" class="px-5 py-10">
              <VantageEmptyState title="Sin resultados" description="No se encontraron usuarios con esa búsqueda." />
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
