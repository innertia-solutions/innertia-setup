<script setup>
const props = defineProps({
  open: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['close'])

const store = useNotificationsStore()
const notifications = useNotifications()

watch(() => props.open, (val) => {
  if (val) {
    notifications.fetchNotifications()
  }
})

function handleItemClick(n) {
  if (!n.read_at) {
    notifications.markAsRead(n.id)
  }
}

function formatDate(dateStr) {
  if (!dateStr) return ''
  const date = new Date(dateStr)
  const now = new Date()
  const diffMs = now - date
  const diffSec = Math.floor(diffMs / 1000)
  const diffMin = Math.floor(diffSec / 60)
  const diffHour = Math.floor(diffMin / 60)
  const diffDay = Math.floor(diffHour / 24)

  if (diffSec < 60) return 'Justo ahora'
  if (diffMin < 60) return `Hace ${diffMin} min`
  if (diffHour < 24) return `Hace ${diffHour}h`
  if (diffDay < 7) return `Hace ${diffDay}d`
  return date.toLocaleDateString('es', { day: 'numeric', month: 'short' })
}

const hasUnread = computed(() => store.notifications.some(n => !n.read_at))
</script>

<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition-opacity duration-200 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-opacity duration-150 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="open"
        class="fixed inset-0 bg-black/40 z-40"
        @click="emit('close')"
      />
    </Transition>

    <Transition
      enter-active-class="transition-transform duration-250 ease-out"
      enter-from-class="translate-x-full"
      enter-to-class="translate-x-0"
      leave-active-class="transition-transform duration-200 ease-in"
      leave-from-class="translate-x-0"
      leave-to-class="translate-x-full"
    >
      <div
        v-if="open"
        class="fixed top-0 right-0 h-full w-80 sm:w-96 bg-white dark:bg-slate-900 border-l border-slate-200 dark:border-slate-700 z-50 flex flex-col shadow-xl"
      >
        <!-- Header -->
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-200 dark:border-slate-700 shrink-0">
          <h2 class="text-sm font-semibold text-slate-800 dark:text-slate-200">Notificaciones</h2>
          <div class="flex items-center gap-2">
            <button
              v-if="hasUnread"
              class="text-xs text-blue-600 dark:text-blue-400 hover:underline"
              @click="notifications.markAllAsRead()"
            >
              Marcar todas como leídas
            </button>
            <button
              class="p-1 rounded-md text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors"
              @click="emit('close')"
            >
              <Icon name="i-lucide-x" class="size-4" />
            </button>
          </div>
        </div>

        <!-- List -->
        <div class="flex-1 overflow-y-auto">
          <div v-if="store.loading" class="flex items-center justify-center py-16">
            <AppLoadingState />
          </div>

          <div
            v-else-if="!store.notifications.length"
            class="flex flex-col items-center justify-center py-16 px-6 text-center"
          >
            <Icon name="i-lucide-bell-off" class="size-10 text-slate-300 dark:text-slate-600 mb-3" />
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">No tienes notificaciones</p>
            <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Cuando haya novedades, aparecerán aquí.</p>
          </div>

          <ul v-else class="divide-y divide-slate-100 dark:divide-slate-800">
            <li
              v-for="n in store.notifications"
              :key="n.id"
              class="flex items-start gap-3 px-5 py-4 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors"
              @click="handleItemClick(n)"
            >
              <!-- Indicador no leído -->
              <span
                class="mt-1.5 shrink-0 size-2 rounded-full transition-colors"
                :class="n.read_at ? 'bg-transparent' : 'bg-blue-500'"
              />
              <div class="min-w-0 flex-1">
                <p class="text-sm font-medium text-slate-800 dark:text-slate-200 truncate">{{ n.title }}</p>
                <p v-if="n.message" class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 line-clamp-2">{{ n.message }}</p>
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">{{ formatDate(n.created_at) }}</p>
              </div>
            </li>
          </ul>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>
