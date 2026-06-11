<script setup>
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import {
  HomeIcon,
  BuildingOffice2Icon,
  ListBulletIcon,
  ClipboardDocumentListIcon,
  UserCircleIcon,
  QuestionMarkCircleIcon,
} from '@heroicons/vue/24/outline'

defineProps({
  mobileOpen: Boolean,
})

defineEmits(['closeMobile'])

const { t } = useI18n()
const page = usePage()

const menuItems = computed(() => [
  { name: t('nav.dashboard'), href: route('dashboard'), icon: HomeIcon },
  { name: t('nav.departments'), href: route('departments.index'), icon: BuildingOffice2Icon },
  { name: t('nav.categories'), href: route('complaint-categories.index'), icon: ListBulletIcon },
  { name: t('nav.complaints'), href: route('complaints.index'), icon: ClipboardDocumentListIcon },
  { name: t('nav.profile'), href: route('profile.edit'), icon: UserCircleIcon },
])

const isActive = (href) => {
  const target = new URL(href, window.location.origin).pathname
  return window.location.pathname === target || window.location.pathname.startsWith(`${target}/`)
}
</script>

<template>
  <!-- Desktop Sidebar -->
  <aside class="hidden lg:fixed lg:left-0 lg:top-0 lg:flex lg:h-screen lg:w-60 lg:flex-col lg:bg-white lg:border-r lg:border-gray-200 lg:z-40">
    <div class="flex h-20 items-center justify-center border-b border-gray-200">
      <span class="text-2xl font-bold text-red-500">CiviSense</span>
    </div>

    <nav class="flex-1 space-y-1 px-4 py-6">
      <Link
        v-for="item in menuItems"
        :key="item.href"
        :href="item.href"
        class="group flex items-center gap-3 rounded-lg px-4 py-3 text-sm font-medium transition-colors"
        :class="isActive(item.href)
          ? 'bg-red-50 text-red-500'
          : 'text-gray-600 hover:bg-gray-50'"
      >
        <component :is="item.icon" class="h-5 w-5" />
        <span>{{ item.name }}</span>
      </Link>
    </nav>

    <div class="border-t border-gray-200 p-4">
      <div class="rounded-lg bg-blue-50 p-4">
        <div class="flex items-center gap-2 mb-3">
          <QuestionMarkCircleIcon class="h-5 w-5 text-blue-500" />
          <span class="font-semibold text-gray-900">{{ t('dashboard.needHelp') }}</span>
        </div>
        <button class="w-full rounded-lg bg-blue-500 px-3 py-2 text-sm font-medium text-white hover:bg-blue-600 transition">
          {{ t('dashboard.viewSupport') }}
        </button>
      </div>
    </div>
  </aside>

  <!-- Mobile Sidebar -->
  <aside
    v-if="mobileOpen"
    class="fixed inset-y-0 left-0 z-40 w-60 bg-white border-r border-gray-200 lg:hidden flex flex-col"
  >
    <div class="flex h-20 items-center justify-center border-b border-gray-200">
      <span class="text-2xl font-bold text-red-500">CiviSense</span>
    </div>

    <nav class="flex-1 space-y-1 px-4 py-6">
      <Link
        v-for="item in menuItems"
        :key="item.href"
        :href="item.href"
        class="group flex items-center gap-3 rounded-lg px-4 py-3 text-sm font-medium transition-colors"
        :class="isActive(item.href)
          ? 'bg-red-50 text-red-500'
          : 'text-gray-600 hover:bg-gray-50'"
        @click="$emit('closeMobile')"
      >
        <component :is="item.icon" class="h-5 w-5" />
        <span>{{ item.name }}</span>
      </Link>
    </nav>

    <div class="border-t border-gray-200 p-4">
      <div class="rounded-lg bg-blue-50 p-4">
        <div class="flex items-center gap-2 mb-3">
          <QuestionMarkCircleIcon class="h-5 w-5 text-blue-500" />
          <span class="font-semibold text-gray-900">{{ t('dashboard.needHelp') }}</span>
        </div>
        <button class="w-full rounded-lg bg-blue-500 px-3 py-2 text-sm font-medium text-white hover:bg-blue-600 transition">
          {{ t('dashboard.viewSupport') }}
        </button>
      </div>
    </div>
  </aside>
</template>

<style scoped>
.sidebar-logo :deep(.logo-image) {
  height: 34px;
}

.sidebar-logo :deep(.brand-text) {
  font-size: 22px;
}

.sidebar-slide-enter-active,
.sidebar-slide-leave-active {
  transition: transform 0.25s ease, opacity 0.25s ease;
}

.sidebar-slide-enter-from,
.sidebar-slide-leave-to {
  opacity: 0;
  transform: translateX(-100%);
}
</style>
