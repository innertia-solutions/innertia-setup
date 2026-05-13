<script setup>
const open = ref(false)
const store = useNotificationsStore()
</script>

<template>
  <div class="relative">
    <button
      @click="open = !open"
      class="relative p-2 rounded-lg text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors"
    >
      <Icon name="i-lucide-bell" class="size-5" />
      <span
        v-if="store.unreadCount > 0"
        class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] px-1 flex items-center justify-center rounded-full bg-red-500 text-white text-[10px] font-bold leading-none"
      >
        {{ store.unreadCount > 99 ? '99+' : store.unreadCount }}
      </span>
    </button>
    <NotificationDrawer :open="open" @close="open = false" />
  </div>
</template>
