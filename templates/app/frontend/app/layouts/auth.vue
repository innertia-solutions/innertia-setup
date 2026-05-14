<script setup>
const config = useRuntimeConfig()

onMounted(() => {
  sessionStorage.removeItem('auth-entered')
})
</script>

<template>
  <LayoutAuth>
    <template #logo>
      <span class="text-2xl font-bold text-slate-900 dark:text-white">
        {{ config.public.appName }}
      </span>
    </template>
    <template #theme-switch>
      <AppSwitchColorTheme />
    </template>
    <slot />
  </LayoutAuth>
</template>
