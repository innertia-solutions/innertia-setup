<script setup>
const config = useRuntimeConfig()

onMounted(() => {
  sessionStorage.removeItem('auth-entered')
})
</script>

<template>
  <div class="bg-slate-50 dark:bg-slate-900">
    <div class="absolute top-4 right-4 z-20">
      <AppSwitchColorTheme />
    </div>
    <main class="flex min-h-full">
      <!-- Columna izquierda: imagen de fondo -->
      <div class="relative hidden min-h-screen lg:w-200 xl:w-[65%] bg-slate-100 lg:flex flex-col justify-between p-6 dark:bg-slate-950 overflow-hidden">
        <img
          src="/login-bg.jpg"
          alt=""
          class="absolute inset-0 w-full h-full object-cover pointer-events-none z-0"
        />
        <div class="absolute inset-0 bg-gradient-to-b from-black/30 to-transparent z-0 pointer-events-none" />

        <!-- Texto de bienvenida -->
        <div class="z-0 p-20 pt-[max(180px,10vh)]">
          <LoginWelcomeText />
        </div>
      </div>

      <!-- Columna derecha: formulario -->
      <div class="grow px-12 z-10 -ml-6 pr-6 bg-white dark:bg-slate-900 rounded-tl-[1.5rem] rounded-bl-[1.5rem]">
        <div class="h-full min-h-screen sm:w-112 flex flex-col justify-center mx-auto space-y-5 p-4">
          <!-- Logo -->
          <div class="flex justify-center mb-6">
            <span class="text-2xl font-bold text-slate-900 dark:text-white">
              {{ config.public.appName }}
            </span>
          </div>

          <!-- Contenido de la página -->
          <Transition name="auth-content" mode="out-in">
            <slot />
          </Transition>

          <div class="absolute bottom-4 right-4 text-xs text-slate-400 dark:text-slate-500">
            v1.0.0
          </div>
        </div>
      </div>
    </main>
  </div>
</template>
