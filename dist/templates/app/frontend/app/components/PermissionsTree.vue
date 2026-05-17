<script setup>
import { IconSearch, IconFolder, IconFolderOpen, IconKey } from '@tabler/icons-vue'

const props = defineProps({
  groups:     { type: Array, default: () => [] },
  modelValue: { type: Array, default: () => [] },
  readonly:   { type: Boolean, default: false },
})

const emit = defineEmits(['update:modelValue'])

const search = ref('')

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

const collapsed = ref({})

watch(search, (q) => {
  if (!q.trim()) return
  filteredGroups.value.forEach(g => { collapsed.value[g.category] = false })
})

function isCollapsed(cat) { return collapsed.value[cat] ?? false }
function toggleCollapse(cat) { collapsed.value[cat] = !collapsed.value[cat] }

function isSelected(name) { return props.modelValue.includes(name) }

function groupState(group) {
  const names = group.permissions.map(p => p.name)
  const n = names.filter(n => props.modelValue.includes(n)).length
  return n === 0 ? 'none' : n === names.length ? 'all' : 'partial'
}

function togglePermission(name) {
  const next = isSelected(name)
    ? props.modelValue.filter(n => n !== name)
    : [...props.modelValue, name]
  emit('update:modelValue', next)
}

function toggleGroup(group) {
  const names = group.permissions.map(p => p.name)
  const next = groupState(group) === 'all'
    ? props.modelValue.filter(n => !names.includes(n))
    : [...new Set([...props.modelValue, ...names])]
  emit('update:modelValue', next)
}

function setIndeterminate(el, state) {
  if (el) el.indeterminate = state === 'partial'
}
</script>

<template>
  <div class="w-full space-y-2">

    <!-- Search -->
    <div class="relative">
      <IconSearch class="absolute left-2.5 top-1/2 -translate-y-1/2 size-3 text-slate-400 pointer-events-none" />
      <input
        v-model="search"
        type="search"
        placeholder="Buscar permiso…"
        class="w-full pl-7 pr-3 py-1.5 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-md text-slate-700 dark:text-slate-300 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-blue-500/40 focus:border-blue-400"
      />
    </div>

    <!-- Tree -->
    <div class="rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 overflow-hidden text-xs">

      <!-- Empty -->
      <div v-if="visibleGroups.length === 0" class="px-3 py-6 text-center text-slate-400 dark:text-slate-500">
        {{ search ? 'Sin resultados.' : readonly ? 'Sin permisos asignados.' : 'Sin permisos disponibles.' }}
      </div>

      <template v-for="(group, gi) in visibleGroups" :key="group.category">

        <!-- Category row -->
        <div
          class="flex items-center gap-1 px-2 py-1 cursor-pointer select-none border-t border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
          :class="gi === 0 ? 'border-t-0' : ''"
          @click="toggleCollapse(group.category)"
        >
          <!-- group checkbox -->
          <input
            v-if="!readonly"
            type="checkbox"
            class="size-3 rounded border-slate-300 dark:border-slate-600 text-blue-600 focus:ring-0 cursor-pointer shrink-0"
            :checked="groupState(group) === 'all'"
            :ref="el => setIndeterminate(el, groupState(group))"
            @click.stop
            @change="toggleGroup(group)"
          />

          <!-- folder icon + label -->
          <IconFolderOpen v-if="!isCollapsed(group.category)" class="size-3.5 text-amber-400 shrink-0" />
          <IconFolder     v-else                               class="size-3.5 text-amber-400 shrink-0" />

          <span class="font-semibold text-slate-600 dark:text-slate-300 flex-1 truncate">
            {{ group.category_alias ?? group.category }}
          </span>

          <span class="text-slate-400 dark:text-slate-500 tabular-nums shrink-0">
            {{ readonly
              ? group.permissions.length
              : `${group.permissions.filter(p => modelValue.includes(p.name)).length}/${group.permissions.length}`
            }}
          </span>
        </div>

        <!-- Permission rows -->
        <template v-if="!isCollapsed(group.category)">
          <div
            v-for="(perm, pi) in group.permissions"
            :key="perm.name"
            class="flex items-center gap-1 border-t border-slate-100 dark:border-slate-800 hover:bg-blue-50/50 dark:hover:bg-blue-500/5 transition-colors"
            :class="!readonly ? 'cursor-pointer' : ''"
            @click="!readonly && togglePermission(perm.name)"
          >
            <!-- tree line -->
            <div class="flex items-center shrink-0 pl-2" style="width:28px">
              <div class="flex flex-col items-center h-full" style="width:10px">
                <div class="w-px flex-1 bg-slate-200 dark:bg-slate-700" />
                <div class="w-2.5 h-px bg-slate-200 dark:bg-slate-700" />
                <div
                  class="w-px flex-1 bg-slate-200 dark:bg-slate-700"
                  :class="pi === group.permissions.length - 1 ? 'opacity-0' : ''"
                />
              </div>
            </div>

            <!-- checkbox or check -->
            <div class="shrink-0 flex items-center justify-center w-3.5">
              <input
                v-if="!readonly"
                type="checkbox"
                class="size-3 rounded border-slate-300 dark:border-slate-600 text-blue-600 focus:ring-0 cursor-pointer"
                :checked="isSelected(perm.name)"
                @click.stop
                @change="togglePermission(perm.name)"
              />
              <span v-else class="size-1.5 rounded-full bg-green-500 block" />
            </div>

            <!-- key icon + name -->
            <IconKey class="size-3 text-slate-400 dark:text-slate-500 shrink-0" />
            <span class="font-mono text-slate-700 dark:text-slate-300 truncate flex-1 py-1">{{ perm.name }}</span>

            <!-- description -->
            <span class="text-slate-400 dark:text-slate-500 truncate hidden sm:block pr-2" style="max-width:260px">
              {{ perm.description ?? '' }}
            </span>
          </div>
        </template>

      </template>
    </div>
  </div>
</template>
