<script setup>
const config = useRuntimeConfig()

onMounted(() => {
  sessionStorage.removeItem('vantage-entered')
})
</script>

<template>
  <vantage-auth>
    <template #logo>
      <span class="text-2xl font-bold text-slate-900 dark:text-white">
        {{ config.public.appName }}
      </span>
    </template>
    <template #theme-switch>
      <VantageSwitchColorTheme />
    </template>
  </vantage-auth>
</template>
