<script setup>
import { Link } from '@inertiajs/vue3'
import AppLogo from '@/Components/common/AppLogo.vue'
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

const sections = [
  {
    title: 'Main',
    items: [
      { name: 'Dashboard', href: route('dashboard'), icon: HomeIcon },
      { name: 'Complaints', href: route('complaints.index'), icon: ClipboardDocumentListIcon },
    ],
  },
  {
    title: 'Management',
    items: [
      { name: 'Departments', href: route('departments.index'), icon: BuildingOffice2Icon },
      { name: 'Categories', href: route('complaint-categories.index'), icon: ListBulletIcon },
    ],
  },
  {
    title: 'Account',
    items: [
      { name: 'Profile', href: route('profile.edit'), icon: UserCircleIcon },
    ],
  },
]

const isActive = (href) => {
  const target = new URL(href, window.location.origin).pathname
  return window.location.pathname === target || window.location.pathname.startsWith(`${target}/`)
}
</script>

<template>
  <!-- Desktop Sidebar -->
  <aside class="hidden lg:fixed lg:left-0 lg:top-0 lg:z-50 lg:flex lg:h-screen lg:w-72 lg:flex-col lg:border-r lg:border-gray-200/80 lg:bg-white/95 lg:shadow-[10px_0_30px_rgba(15,23,42,0.03)] lg:backdrop-blur-xl">
    <!-- Logo -->
    <div class="flex h-20 items-center px-6">
      <AppLogo class="sidebar-logo" />
    </div>

    <!-- Menu Items -->
    <nav class="flex-1 space-y-8 px-4 py-4">
      <div v-for="section in sections" :key="section.title">
        <p class="mb-3 px-3 text-xs font-bold uppercase tracking-widest text-gray-400">
          {{ section.title }}
        </p>
        <div class="space-y-1">
          <Link
            v-for="item in section.items"
            :key="item.name"
            :href="item.href"
            class="group relative flex items-center gap-3 rounded-xl px-4 py-3 text-sm transition-all duration-200 hover:translate-x-1 hover:bg-gray-50"
            :class="isActive(item.href)
              ? 'bg-indigo-50 font-semibold text-indigo-600 shadow-sm'
              : 'font-medium text-gray-600 hover:text-gray-900'"
          >
            <span
              v-if="isActive(item.href)"
              class="absolute left-0 h-8 w-1 rounded-r-full bg-indigo-600 transition-all duration-200"
            />
            <component
              :is="item.icon"
              class="h-5 w-5 transition-colors duration-200"
              :class="isActive(item.href) ? 'text-indigo-600' : 'text-gray-400 group-hover:text-gray-600'"
            />
            <span>{{ item.name }}</span>
          </Link>
        </div>
      </div>
    </nav>

    <!-- Help Card -->
    <div class="p-4">
      <div class="rounded-2xl border border-indigo-100 bg-gradient-to-br from-indigo-50 to-white p-4 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-lg">
        <div class="flex items-center gap-2 mb-3">
          <QuestionMarkCircleIcon class="h-5 w-5 text-indigo-500" />
          <span class="font-semibold text-gray-900">Need Help?</span>
        </div>
        <button class="w-full rounded-xl bg-red-500 px-3 py-2 text-sm font-medium text-white shadow-sm transition-all duration-200 hover:scale-[1.02] hover:bg-red-600 hover:shadow-md">
          View Support
        </button>
      </div>
    </div>
  </aside>

  <!-- Mobile Sidebar -->
  <Transition name="sidebar-slide">
    <aside
      v-if="mobileOpen"
      class="fixed inset-y-0 left-0 z-50 flex w-72 flex-col border-r border-gray-200 bg-white shadow-2xl lg:hidden"
    >
      <div class="flex h-20 items-center px-6">
        <AppLogo class="sidebar-logo" />
      </div>

      <nav class="flex-1 space-y-8 px-4 py-4">
        <div v-for="section in sections" :key="section.title">
          <p class="mb-3 px-3 text-xs font-bold uppercase tracking-widest text-gray-400">
            {{ section.title }}
          </p>
          <div class="space-y-1">
            <Link
              v-for="item in section.items"
              :key="item.name"
              :href="item.href"
              class="group relative flex items-center gap-3 rounded-xl px-4 py-3 text-sm transition-all duration-200 hover:translate-x-1 hover:bg-gray-50"
              :class="isActive(item.href)
                ? 'bg-indigo-50 font-semibold text-indigo-600 shadow-sm'
                : 'font-medium text-gray-600 hover:text-gray-900'"
              @click="$emit('closeMobile')"
            >
              <span
                v-if="isActive(item.href)"
                class="absolute left-0 h-8 w-1 rounded-r-full bg-indigo-600 transition-all duration-200"
              />
              <component
                :is="item.icon"
                class="h-5 w-5 transition-colors duration-200"
                :class="isActive(item.href) ? 'text-indigo-600' : 'text-gray-400 group-hover:text-gray-600'"
              />
              <span>{{ item.name }}</span>
            </Link>
          </div>
        </div>
      </nav>
    </aside>
  </Transition>
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
