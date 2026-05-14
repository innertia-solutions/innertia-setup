<script setup>
definePageMeta({ layout: false })
const config = useRuntimeConfig()
</script>

<template>
  <div class="min-h-screen bg-white dark:bg-slate-900">
    <div class="max-w-3xl mx-auto px-6 py-16">
      <NuxtLink
        to="/login"
        class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-300 mb-10"
      >
        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M19 12H5M12 5l-7 7 7 7"/>
        </svg>
        Volver
      </NuxtLink>

      <h1 class="text-3xl font-bold text-slate-900 dark:text-white mb-2">
        Términos y Condiciones
      </h1>
      <p class="text-sm text-slate-500 dark:text-slate-400 mb-10">Última actualización: {{ new Date().getFullYear() }}</p>

      <div class="prose prose-slate dark:prose-invert max-w-none space-y-8 text-slate-700 dark:text-slate-300">

        <section>
          <h2 class="text-xl font-semibold text-slate-800 dark:text-slate-200 mb-3">1. Aceptación de los términos</h2>
          <p>Al acceder y utilizar {{ config.public.appName }}, aceptas estar sujeto a estos Términos y Condiciones. Si no estás de acuerdo con alguna parte de los términos, no podrás acceder al servicio.</p>
        </section>

        <section>
          <h2 class="text-xl font-semibold text-slate-800 dark:text-slate-200 mb-3">2. Uso del servicio</h2>
          <p>{{ config.public.appName }} es una plataforma de gestión empresarial. Al utilizar el servicio, te comprometes a:</p>
          <ul class="list-disc pl-6 mt-2 space-y-1">
            <li>Proporcionar información veraz y actualizada</li>
            <li>Mantener la seguridad de tu cuenta y contraseña</li>
            <li>No utilizar el servicio para actividades ilegales o no autorizadas</li>
            <li>No intentar acceder a cuentas de otros usuarios</li>
          </ul>
        </section>

        <section>
          <h2 class="text-xl font-semibold text-slate-800 dark:text-slate-200 mb-3">3. Cuentas de usuario</h2>
          <p>Eres responsable de mantener la confidencialidad de tu cuenta y contraseña. Acepta notificarnos inmediatamente sobre cualquier uso no autorizado de tu cuenta.</p>
        </section>

        <section>
          <h2 class="text-xl font-semibold text-slate-800 dark:text-slate-200 mb-3">4. Propiedad intelectual</h2>
          <p>El servicio y su contenido original, características y funcionalidad son propiedad de {{ config.public.appName }} y están protegidos por leyes de propiedad intelectual.</p>
        </section>

        <section>
          <h2 class="text-xl font-semibold text-slate-800 dark:text-slate-200 mb-3">5. Limitación de responsabilidad</h2>
          <p>En ningún caso seremos responsables por daños indirectos, incidentales, especiales o consecuentes que resulten del uso o la imposibilidad de usar el servicio.</p>
        </section>

        <section>
          <h2 class="text-xl font-semibold text-slate-800 dark:text-slate-200 mb-3">6. Cambios en los términos</h2>
          <p>Nos reservamos el derecho de modificar estos términos en cualquier momento. Te notificaremos sobre cambios significativos a través del servicio o por correo electrónico.</p>
        </section>

        <section>
          <h2 class="text-xl font-semibold text-slate-800 dark:text-slate-200 mb-3">7. Contacto</h2>
          <p>Si tienes preguntas sobre estos términos, contáctanos en la plataforma.</p>
        </section>

      </div>
    </div>
  </div>
</template>
