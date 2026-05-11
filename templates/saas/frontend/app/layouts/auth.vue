<template>
  <div class="min-h-screen bg-gray-950 flex flex-col items-center justify-center px-4">
    <!-- App name -->
    <div class="mb-8 text-center">
      <h1 class="text-2xl font-bold text-white tracking-tight">
        {{ appName }}
      </h1>
    </div>

    <!-- Card -->
    <div class="w-full max-w-md bg-white rounded-2xl shadow-xl p-8">
      <slot />
    </div>

    <!-- Branding -->
    <p class="mt-8 text-sm text-gray-500">
      Desarrollado por <span class="font-medium text-gray-400">{{PROJECT_NAME}}</span>
    </p>
  </div>
</template>

<script setup lang="ts">
const config = useRuntimeConfig()
const appName = config.public.appName as string
</script>
