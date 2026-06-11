<script setup>
import { ref, computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue'
import {
  MagnifyingGlassIcon,
  BellIcon,
  QuestionMarkCircleIcon,
  Bars3Icon,
} from '@heroicons/vue/24/outline'

const { t } = useI18n()

defineEmits(['toggleSidebar'])

const page = usePage()
const searchQuery = ref('')

const user = computed(() => page.props?.auth?.user || {})

const handleSearch = () => {
  // Search functionality can be added here
}
</script>

<template>
  <nav class="sticky top-0 z-30 h-20 bg-white border-b border-gray-200">
    <div class="h-full px-4 sm:px-6 lg:px-8 flex items-center justify-between">
      <!-- Left: Hamburger (Mobile) -->
      <button
        @click="$emit('toggleSidebar')"
        class="lg:hidden p-2 rounded-lg hover:bg-gray-100 transition"
      >
        <Bars3Icon class="h-6 w-6 text-gray-600" />
      </button>

      <!-- Center: Search -->
      <div class="hidden sm:block flex-1 max-w-md mx-4">
        <div class="relative">
          <input
            v-model="searchQuery"
            type="text"
            :placeholder="t('nav.search')"
            class="w-full rounded-lg border border-gray-200 bg-gray-50 py-2 pl-4 pr-10 text-sm placeholder-gray-400 focus:border-red-500 focus:bg-white focus:outline-none"
            @keyup.enter="handleSearch"
          />
          <MagnifyingGlassIcon class="absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
        </div>
      </div>

      <!-- Right: Icons and User -->
      <div class="flex items-center gap-4">
        <LanguageSwitcher />

        <!-- Notification -->
        <button class="relative p-2 rounded-lg hover:bg-gray-100 transition">
          <BellIcon class="h-5 w-5 text-gray-600" />
          <span class="absolute top-1 right-1 h-2 w-2 rounded-full bg-red-500" />
        </button>

        <!-- Help -->
        <button class="p-2 rounded-lg hover:bg-gray-100 transition">
          <QuestionMarkCircleIcon class="h-5 w-5 text-gray-600" />
        </button>

        <!-- User Avatar and Dropdown -->
        <div class="hidden sm:flex items-center gap-3 pl-3 border-l border-gray-200">
          <div class="h-10 w-10 rounded-full bg-red-500 flex items-center justify-center text-white font-semibold">
            {{ user.name?.charAt(0).toUpperCase() || 'U' }}
          </div>
          <div class="flex flex-col">
            <span class="text-sm font-medium text-gray-900">{{ user.name || 'User' }}</span>
            <span class="text-xs text-gray-500">Admin</span>
          </div>
        </div>
      </div>
    </div>
  </nav>
</template>
