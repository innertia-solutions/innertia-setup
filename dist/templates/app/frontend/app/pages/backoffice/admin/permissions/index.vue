<script setup>
definePageMeta({ layout: 'backoffice', middleware: ['auth'] })

const api = useApi()
const toast = useToast()
const loading = ref(false)
const groups = ref([])

async function fetchPermissions() {
  loading.value = true
  try {
    const data = await api.get('backoffice/permissions')
    groups.value = data?.data ?? data ?? []
  } catch {
    toast.error('Error al cargar los permisos.')
  } finally {
    loading.value = false
  }
}

onMounted(fetchPermissions)
</script>

<template>
  <div>
    <AdminPageHeader
      title="Permisos"
      description="Permisos disponibles en el sistema."
    />

    <div v-if="loading" class="flex items-center justify-center py-20">
      <AppLoadingState />
    </div>

    <AppEmptyState
      v-else-if="!groups.length"
      title="Sin permisos"
      description="No hay permisos registrados en el sistema."
    />

    <div v-else class="space-y-4 max-w-3xl">
      <div
        v-for="group in groups"
        :key="group.category"
        class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden"
      >
        <!-- Cabecera del grupo -->
        <div class="px-5 py-3.5 bg-slate-50 dark:bg-slate-700/50 border-b border-slate-200 dark:border-slate-700">
          <h2 class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
            {{ group.category_alias }}
          </h2>
        </div>

        <!-- Lista de permisos -->
        <ul class="divide-y divide-slate-100 dark:divide-slate-700/60">
          <li
            v-for="perm in group.permissions"
            :key="perm.id"
            class="flex items-start gap-3 px-5 py-3"
          >
            <span class="font-mono text-sm text-slate-800 dark:text-slate-200 shrink-0">{{ perm.name }}</span>
            <span v-if="perm.description" class="text-sm text-slate-400 dark:text-slate-500">{{ perm.description }}</span>
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>
