<script setup>
import * as icons from '@tabler/icons-vue'
const { IconSearch } = icons

const { logout } = useAuth()
const authStore = useAuthStore()
const config = useRuntimeConfig()
const { isDark, toggle } = useTheme()

const menuItems = [
  {
    label: 'Dashboard',
    icon: 'IconLayoutDashboard',
    route: '/backoffice',
    pattern: '/backoffice',
  },
  {
    label: 'Administración',
    icon: 'IconUsers',
    pattern: '/backoffice/admin/*',
    children: [
      { label: 'Usuarios',  route: '/backoffice/admin/users',    icon: 'IconUsers',         pattern: '/backoffice/admin/users*'    },
      { label: 'Roles',     route: '/backoffice/admin/roles',    icon: 'IconShieldCheck',   pattern: '/backoffice/admin/roles*'    },
      { label: 'Sesiones',  route: '/backoffice/admin/sessions', icon: 'IconDeviceDesktop', pattern: '/backoffice/admin/sessions*' },
    ],
  },
]

const route = useRoute()

const matchPattern = (pattern) => {
  if (!pattern) return false
  const regex = new RegExp('^' + pattern.replace(/\*/g, '.*') + '$')
  return regex.test(route.path)
}

const isAnyChildActive = (children) =>
  children?.some(c => matchPattern(c.pattern || c.route + '/*'))

const openAccordions = ref({})

const toggleAccordion = (index) => {
  openAccordions.value[index] = !openAccordions.value[index]
}

watch(() => route.path, () => {
  let activeIndex = -1
  menuItems.forEach((item, index) => {
    if (item.children && (matchPattern(item.pattern) || isAnyChildActive(item.children))) {
      activeIndex = index
    }
  })
  openAccordions.value = activeIndex !== -1 ? { [activeIndex]: true } : {}
}, { immediate: true })

const showAnimation = ref(false)
onMounted(() => {
  const seen = sessionStorage.getItem('backoffice-entered')
  if (!seen) {
    showAnimation.value = true
    sessionStorage.setItem('backoffice-entered', 'true')
  }
  useUserRealtime().start()
})
</script>

<template>
  <div class="bg-slate-50 dark:bg-slate-950 min-h-screen" :class="{ 'animate-entrance': showAnimation }">

    <Admin.Base :floating="true" :user="authStore.user" @logout="logout">

      <!-- Logo -->
      <template #logo>
        <NuxtLink to="/backoffice" class="px-3 py-1 flex items-center">
          <span class="text-base font-bold text-foreground truncate">{{ config.public.appName }}</span>
        </NuxtLink>
      </template>

      <!-- Search -->
      <template #search>
        <Forms.Input type="search" placeholder="Buscar…" :icon-left="IconSearch" />
      </template>

      <!-- Nav -->
      <template #menu>
        <nav class="pb-3 w-full flex flex-col">
          <ul class="flex flex-col gap-y-1">
            <li v-for="(item, index) in menuItems" :key="'menu-' + index" class="px-3">

              <!-- Simple link -->
              <NuxtLink v-if="!item.children" :to="item.route"
                class="flex items-center gap-x-3 py-2 px-3 text-sm text-muted-foreground-1 rounded-lg hover:bg-muted-hover transition-all border border-transparent"
                :class="{ 'bg-surface text-foreground font-bold': matchPattern(item.pattern || item.route) }"
              >
                <component :is="icons[item.icon]" class="shrink-0 size-4" />
                {{ item.label }}
              </NuxtLink>

              <!-- Accordion -->
              <div v-else class="flex flex-col">
                <button type="button" @click="toggleAccordion(index)"
                  class="w-full text-start flex items-center gap-x-3 py-2 px-3 text-sm text-muted-foreground-1 rounded-lg hover:bg-muted-hover transition-all font-medium"
                  :class="{ 'bg-muted': openAccordions[index] }"
                >
                  <component :is="icons[item.icon]" class="shrink-0 size-4" />
                  <span class="flex-1">{{ item.label }}</span>
                  <icons.IconChevronDown class="shrink-0 size-4 transition-transform duration-300"
                    :class="{ '-rotate-180': openAccordions[index] }" />
                </button>

                <div class="overflow-hidden transition-all duration-300 ease-in-out" :style="{
                  maxHeight: openAccordions[index] ? '500px' : '0px',
                  opacity: openAccordions[index] ? '1' : '0',
                  marginTop: openAccordions[index] ? '4px' : '0px',
                }">
                  <ul class="ps-8 flex flex-col gap-y-1 relative before:absolute before:start-4.5 before:w-px before:h-full before:bg-sidebar-line">
                    <li v-for="(child, cIndex) in item.children" :key="cIndex">
                      <NuxtLink :to="child.route"
                        class="flex items-center gap-x-4 py-2 px-3 text-sm text-muted-foreground rounded-lg hover:bg-muted-hover transition-colors border border-transparent"
                        :class="{ 'bg-surface text-foreground font-bold': matchPattern(child.pattern || child.route + '/*') }"
                      >
                        <component :is="icons[child.icon]" class="shrink-0 size-4" />
                        {{ child.label }}
                      </NuxtLink>
                    </li>
                  </ul>
                </div>
              </div>

            </li>
          </ul>
        </nav>
      </template>

      <!-- Controls: dark mode + alertas -->
      <template #user-controls>
        <label class="flex-1 inline-flex justify-center items-center gap-x-1.5 px-2.5 py-1.5 rounded-lg bg-sidebar border border-sidebar-line text-muted-foreground cursor-pointer select-none" title="Modo oscuro" @click.prevent="toggle">
          <icons.IconSun class="size-3.5 shrink-0" />
          <div class="relative inline-block w-8 h-4">
            <span class="absolute inset-0 rounded-full transition-colors duration-200" :class="isDark ? 'bg-primary' : 'bg-surface-1'"></span>
            <span class="absolute top-0.5 start-0.5 size-3 bg-white rounded-full shadow-sm transition-transform duration-200" :class="isDark ? 'translate-x-4' : ''"></span>
          </div>
        </label>
        <button type="button" class="flex-1 inline-flex justify-center items-center gap-x-1.5 px-2.5 py-1.5 rounded-lg bg-sidebar border border-sidebar-line text-muted-foreground hover:bg-muted-hover transition-colors">
          <icons.IconBell class="size-3.5 shrink-0" />
          <span class="text-xs">Alertas</span>
          <span class="size-1.5 bg-red-500 rounded-full shrink-0"></span>
        </button>
      </template>

      <!-- Main content -->
      <slot />

    </Admin.Base>

  </div>
</template>

<style scoped>
.animate-entrance { animation: fadeInScale 0.5s cubic-bezier(0.4, 0, 0.2, 1); }
@keyframes fadeInScale {
  from { opacity: 0; transform: scale(0.96); filter: blur(4px); }
  to   { opacity: 1; transform: scale(1);    filter: blur(0);   }
}
</style>
