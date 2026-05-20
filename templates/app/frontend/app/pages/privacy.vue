<script setup>
definePageMeta({ layout: false })
const config = useRuntimeConfig()
</script>

<template>
  <div class="min-h-screen bg-white dark:bg-slate-900">
    <div class="max-w-3xl mx-auto px-6 py-16">
      <NuxtLink
        :to="config.public.loginPath"
        class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-300 mb-10"
      >
        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M19 12H5M12 5l-7 7 7 7"/>
        </svg>
        Volver
      </NuxtLink>

      <h1 class="text-3xl font-bold text-slate-900 dark:text-white mb-2">
        Política de Privacidad
      </h1>
      <p class="text-sm text-slate-500 dark:text-slate-400 mb-10">Última actualización: {{ new Date().getFullYear() }}</p>

      <div class="prose prose-slate dark:prose-invert max-w-none space-y-8 text-slate-700 dark:text-slate-300">

        <section>
          <h2 class="text-xl font-semibold text-slate-800 dark:text-slate-200 mb-3">1. Información que recopilamos</h2>
          <p>Recopilamos información que nos proporcionas directamente al crear una cuenta o usar el servicio, incluyendo:</p>
          <ul class="list-disc pl-6 mt-2 space-y-1">
            <li>Nombre y dirección de correo electrónico</li>
            <li>Información de la organización o empresa</li>
            <li>Datos de uso y actividad dentro de la plataforma</li>
          </ul>
        </section>

        <section>
          <h2 class="text-xl font-semibold text-slate-800 dark:text-slate-200 mb-3">2. Cómo usamos tu información</h2>
          <p>Utilizamos la información recopilada para:</p>
          <ul class="list-disc pl-6 mt-2 space-y-1">
            <li>Proporcionar, operar y mejorar el servicio</li>
            <li>Enviar comunicaciones relacionadas con el servicio</li>
            <li>Responder a solicitudes de soporte</li>
            <li>Cumplir con obligaciones legales</li>
          </ul>
        </section>

        <section>
          <h2 class="text-xl font-semibold text-slate-800 dark:text-slate-200 mb-3">3. Compartir información</h2>
          <p>No vendemos ni alquilamos tu información personal a terceros. Podemos compartir información con proveedores de servicios que nos ayudan a operar la plataforma, bajo estrictas obligaciones de confidencialidad.</p>
        </section>

        <section>
          <h2 class="text-xl font-semibold text-slate-800 dark:text-slate-200 mb-3">4. Seguridad de los datos</h2>
          <p>Implementamos medidas técnicas y organizativas apropiadas para proteger tu información contra acceso no autorizado, pérdida o alteración.</p>
        </section>

        <section>
          <h2 class="text-xl font-semibold text-slate-800 dark:text-slate-200 mb-3">5. Cookies</h2>
          <p>Utilizamos cookies y tecnologías similares para mantener tu sesión activa y mejorar la experiencia de uso. Puedes configurar tu navegador para rechazar cookies, aunque esto puede afectar algunas funcionalidades.</p>
        </section>

        <section>
          <h2 class="text-xl font-semibold text-slate-800 dark:text-slate-200 mb-3">6. Tus derechos</h2>
          <p>Tienes derecho a acceder, corregir o eliminar tu información personal. Para ejercer estos derechos, contáctanos a través de la plataforma.</p>
        </section>

        <section>
          <h2 class="text-xl font-semibold text-slate-800 dark:text-slate-200 mb-3">7. Retención de datos</h2>
          <p>Conservamos tu información mientras tu cuenta esté activa o según sea necesario para proporcionar el servicio. Puedes solicitar la eliminación de tu cuenta y datos en cualquier momento.</p>
        </section>

        <section>
          <h2 class="text-xl font-semibold text-slate-800 dark:text-slate-200 mb-3">8. Cambios en esta política</h2>
          <p>Podemos actualizar esta política periódicamente. Te notificaremos sobre cambios significativos a través del servicio o por correo electrónico.</p>
        </section>

      </div>
    </div>
  </div>
</template>
