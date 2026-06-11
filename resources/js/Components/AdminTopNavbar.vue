<script setup>
import { ref, computed } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import {
  MagnifyingGlassIcon,
  BellIcon,
  QuestionMarkCircleIcon,
  Bars3Icon,
  ChevronDownIcon,
} from '@heroicons/vue/24/outline'

defineEmits(['toggleSidebar'])

const page = usePage()
const searchQuery = ref('')
const userMenuOpen = ref(false)

const user = computed(() => page.props.auth?.user || {})
const initials = computed(() => (user.value.name || 'User').charAt(0).toUpperCase())

const handleSearch = () => {
  // Search functionality can be added here
}

function logout() {
  router.post('/logout')
}
</script>

<template>
  <nav class="sticky top-0 z-40 h-20 border-b border-gray-200/80 bg-white/80 backdrop-blur-md">
    <div class="h-full px-4 sm:px-6 lg:px-8 flex items-center justify-between">
      <!-- Left: Hamburger (Mobile) -->
      <button
        @click="$emit('toggleSidebar')"
        class="lg:hidden rounded-xl p-2 text-gray-600 transition-all duration-200 hover:bg-gray-100 hover:text-gray-900"
      >
        <Bars3Icon class="h-6 w-6 text-gray-600" />
      </button>

      <!-- Center: Search -->
      <div class="hidden sm:block flex-1 max-w-xl mx-4">
        <div class="relative">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search..."
            class="w-full rounded-2xl border border-gray-200 bg-gray-50/80 py-3 pl-4 pr-11 text-sm placeholder-gray-400 shadow-sm transition-all duration-200 focus:border-indigo-300 focus:bg-white focus:outline-none focus:ring-4 focus:ring-indigo-500/10"
            @keyup.enter="handleSearch"
          />
          <MagnifyingGlassIcon class="absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
        </div>
      </div>

      <!-- Right: Icons and User -->
      <div class="flex items-center gap-4">
        <!-- Notification -->
        <button class="relative rounded-xl p-2 text-gray-600 transition-all duration-200 hover:scale-[1.04] hover:bg-gray-100 hover:text-gray-900">
          <BellIcon class="h-5 w-5 text-gray-600" />
          <span class="absolute top-1 right-1 h-2 w-2 rounded-full bg-red-500" />
        </button>

        <!-- Help -->
        <button class="rounded-xl p-2 text-gray-600 transition-all duration-200 hover:scale-[1.04] hover:bg-gray-100 hover:text-gray-900">
          <QuestionMarkCircleIcon class="h-5 w-5 text-gray-600" />
        </button>

        <!-- User Avatar and Dropdown -->
        <div class="relative hidden sm:block pl-3 border-l border-gray-200">
          <button
            class="flex items-center gap-3 rounded-2xl px-2 py-1.5 transition-all duration-200 hover:bg-gray-100"
            @click="userMenuOpen = !userMenuOpen"
          >
            <div class="h-10 w-10 rounded-full bg-gradient-to-br from-indigo-600 to-red-500 flex items-center justify-center text-white font-semibold shadow-sm">
              {{ initials }}
            </div>
            <div class="flex flex-col text-left">
              <span class="text-sm font-semibold text-gray-900">{{ user.name || 'User' }}</span>
              <span class="text-xs text-gray-500">Administrator</span>
            </div>
            <ChevronDownIcon class="h-4 w-4 text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': userMenuOpen }" />
          </button>

          <Transition name="menu-fade">
            <div
              v-if="userMenuOpen"
              class="absolute right-0 mt-2 w-48 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-xl"
            >
              <a :href="route('profile.edit')" class="block px-4 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
                Profile
              </a>
              <button
                type="button"
                class="block w-full px-4 py-3 text-left text-sm font-medium text-red-600 transition hover:bg-red-50"
                @click="logout"
              >
                Log out
              </button>
            </div>
          </Transition>
        </div>
      </div>
    </div>
  </nav>
</template>

<style scoped>
.menu-fade-enter-active,
.menu-fade-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}

.menu-fade-enter-from,
.menu-fade-leave-to {
  opacity: 0;
  transform: translateY(-6px);
}
</style>
