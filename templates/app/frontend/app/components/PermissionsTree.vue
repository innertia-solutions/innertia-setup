<script setup>
import { IconChevronDown, IconCheck, IconSearch } from '@tabler/icons-vue'

const props = defineProps({
  groups:     { type: Array, default: () => [] },   // [{ category, category_alias, permissions: [{ name, description }] }]
  modelValue: { type: Array, default: () => [] },   // selected permission names
  readonly:   { type: Boolean, default: false },
})

const emit = defineEmits(['update:modelValue'])

const search = ref('')

// Collapsed state per category
const collapsed = ref({})

const filteredGroups = computed(() => {
  const q = search.value.trim().toLowerCase()
  if (!q) return props.groups
  return props.groups
    .map(g => ({
      ...g,
      permissions: g.permissions.filter(p =>
        p.name.toLowerCase().includes(q) || (p.description ?? '').toLowerCase().includes(q)
      ),
    }))
    .filter(g => g.permissions.length > 0)
})

const visibleGroups = computed(() => {
  if (!props.readonly) return filteredGroups.value
  return filteredGroups.value
    .map(g => ({
      ...g,
      permissions: g.permissions.filter(p => props.modelValue.includes(p.name)),
    }))
    .filter(g => g.permissions.length > 0)
})

// Auto-expand groups that match the search
watch(search, (q) => {
  if (!q.trim()) return
  filteredGroups.value.forEach(g => {
    collapsed.value[g.category] = false
  })
})

function isSelected(name) {
  return props.modelValue.includes(name)
}

function groupState(group) {
  const names = group.permissions.map(p => p.name)
  const selectedCount = names.filter(n => props.modelValue.includes(n)).length
  if (selectedCount === 0) return 'none'
  if (selectedCount === names.length) return 'all'
  return 'partial'
}

function togglePermission(name) {
  const next = isSelected(name)
    ? props.modelValue.filter(n => n !== name)
    : [...props.modelValue, name]
  emit('update:modelValue', next)
}

function toggleGroup(group) {
  const names = group.permissions.map(p => p.name)
  const state = groupState(group)
  const next = state === 'all'
    ? props.modelValue.filter(n => !names.includes(n))
    : [...new Set([...props.modelValue, ...names])]
  emit('update:modelValue', next)
}

function toggleCollapse(category) {
  collapsed.value[category] = !collapsed.value[category]
}

function isCollapsed(category) {
  return collapsed.value[category] ?? false
}

// Indeterminate checkbox ref setter
function setIndeterminate(el, state) {
  if (el) el.indeterminate = state === 'partial'
}
</script>

<template>
  <div class="w-full space-y-2">

    <!-- Search -->
    <div class="relative">
      <IconSearch class="absolute left-3 top-1/2 -translate-y-1/2 size-3.5 text-slate-400 pointer-events-none" />
      <input
        v-model="search"
        type="search"
        placeholder="Buscar permiso…"
        class="w-full pl-8 pr-3 py-2 text-sm bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-700 dark:text-slate-300 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-400"
      />
    </div>

    <!-- Tree -->
    <div class="rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden">

      <!-- Header row -->
      <div class="grid grid-cols-[1fr_auto] sm:grid-cols-[1fr_2fr] bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800">
        <div class="px-4 py-2.5 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Permiso</div>
        <div class="hidden sm:block px-4 py-2.5 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide border-l border-slate-200 dark:border-slate-800">Descripción</div>
      </div>

      <!-- Empty -->
      <div v-if="visibleGroups.length === 0" class="px-4 py-8 text-center text-sm text-slate-400 dark:text-slate-500">
        {{ search ? 'Sin resultados.' : readonly ? 'Sin permisos asignados.' : 'Sin permisos disponibles.' }}
      </div>

      <!-- Groups -->
      <template v-for="(group, gi) in visibleGroups" :key="group.category">

        <!-- Category row -->
        <div
          class="grid grid-cols-[1fr_auto] sm:grid-cols-[1fr_2fr] cursor-pointer select-none"
          :class="[
            gi > 0 ? 'border-t border-slate-200 dark:border-slate-800' : '',
            'bg-slate-50/80 dark:bg-slate-800/40 hover:bg-slate-100 dark:hover:bg-slate-800/70 transition-colors'
          ]"
          @click="toggleCollapse(group.category)"
        >
          <div class="flex items-center gap-2.5 px-4 py-3">
            <!-- Checkbox group (edit mode) -->
            <input
              v-if="!readonly"
              type="checkbox"
              class="shrink-0 size-3.5 rounded border-slate-300 dark:border-slate-600 text-blue-600 focus:ring-blue-500 focus:ring-offset-0 cursor-pointer"
              :checked="groupState(group) === 'all'"
              :ref="el => setIndeterminate(el, groupState(group))"
              @click.stop
              @change="toggleGroup(group)"
            />
            <!-- Expand icon -->
            <IconChevronDown
              class="size-3.5 text-slate-400 transition-transform duration-150 shrink-0"
              :class="isCollapsed(group.category) ? '-rotate-90' : ''"
            />
            <span class="text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wide">
              {{ group.category_alias ?? group.category }}
            </span>
            <span class="ml-auto text-xs text-slate-400 tabular-nums">
              {{ readonly
                ? group.permissions.length
                : `${group.permissions.filter(p => modelValue.includes(p.name)).length}/${group.permissions.length}`
              }}
            </span>
          </div>
          <div class="hidden sm:block border-l border-slate-200 dark:border-slate-800" />
        </div>

        <!-- Permission rows -->
        <template v-if="!isCollapsed(group.category)">
          <div
            v-for="(perm, pi) in group.permissions"
            :key="perm.name"
            class="grid grid-cols-[1fr_auto] sm:grid-cols-[1fr_2fr] border-t border-slate-100 dark:border-slate-800/60"
            :class="[
              !readonly ? 'hover:bg-blue-50/30 dark:hover:bg-blue-500/5 transition-colors' : '',
            ]"
          >
            <div class="flex items-center gap-2.5 px-4 py-2.5 pl-8">
              <!-- Checkbox (edit mode) -->
              <input
                v-if="!readonly"
                type="checkbox"
                class="shrink-0 size-3.5 rounded border-slate-300 dark:border-slate-600 text-blue-600 focus:ring-blue-500 focus:ring-offset-0 cursor-pointer"
                :checked="isSelected(perm.name)"
                @change="togglePermission(perm.name)"
              />
              <!-- Check icon (readonly) -->
              <IconCheck v-else class="shrink-0 size-3.5 text-green-500 dark:text-green-400" />
              <span class="text-xs font-mono text-slate-700 dark:text-slate-300">{{ perm.name }}</span>
            </div>
            <div class="hidden sm:flex items-center px-4 py-2.5 border-l border-slate-100 dark:border-slate-800/60">
              <span class="text-xs text-slate-500 dark:text-slate-400">{{ perm.description ?? '—' }}</span>
            </div>
          </div>
        </template>

      </template>
    </div>
  </div>
</template>
