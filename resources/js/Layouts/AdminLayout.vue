<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { usePage } from '@inertiajs/vue3'
import Sidebar from '@/Components/AdminSidebar.vue'
import TopNavbar from '@/Components/AdminTopNavbar.vue'

const mobileSidebarOpen = ref(false)
const windowWidth = ref(typeof window !== 'undefined' ? window.innerWidth : 1024)
const page = usePage()

const isMobile = computed(() => windowWidth.value < 1024)
const pageKey = computed(() => page.url)

const toggleMobileSidebar = () => {
  mobileSidebarOpen.value = !mobileSidebarOpen.value
  if (mobileSidebarOpen.value) {
    document.body.style.overflow = 'hidden'
  } else {
    document.body.style.overflow = ''
  }
}

const closeMobileSidebar = () => {
  mobileSidebarOpen.value = false
  document.body.style.overflow = ''
}

const handleResize = () => {
  windowWidth.value = window.innerWidth
  if (!isMobile.value && mobileSidebarOpen.value) {
    closeMobileSidebar()
  }
}

const handleEscape = (e) => {
  if (e.key === 'Escape' && mobileSidebarOpen.value) {
    closeMobileSidebar()
  }
}

onMounted(() => {
  window.addEventListener('resize', handleResize)
  document.addEventListener('keydown', handleEscape)
})

onUnmounted(() => {
  window.removeEventListener('resize', handleResize)
  document.removeEventListener('keydown', handleEscape)
  document.body.style.overflow = ''
})
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Sidebar -->
    <Sidebar
      :mobile-open="mobileSidebarOpen"
      @close-mobile="closeMobileSidebar"
    />

    <!-- Mobile Sidebar Overlay -->
    <Transition name="overlay-fade">
      <div
        v-if="mobileSidebarOpen && isMobile"
        class="fixed inset-0 bg-gray-950/40 backdrop-blur-sm z-40 lg:hidden"
        @click="closeMobileSidebar"
      />
    </Transition>

    <!-- Main Content -->
    <div class="min-h-screen flex-1 flex flex-col transition-all duration-300 lg:ml-72">
      <!-- Top Navbar -->
      <TopNavbar @toggle-sidebar="toggleMobileSidebar" />

      <!-- Content Area -->
      <Transition name="page-fade" mode="out-in">
        <main :key="pageKey" class="flex-1">
          <div class="px-4 py-8 sm:px-6 lg:px-8 xl:px-10">
            <slot />
          </div>
        </main>
      </Transition>
    </div>
  </div>
</template>

<style scoped>
.overlay-fade-enter-active,
.overlay-fade-leave-active,
.page-fade-enter-active,
.page-fade-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}

.overlay-fade-enter-from,
.overlay-fade-leave-to {
  opacity: 0;
}

.page-fade-enter-from,
.page-fade-leave-to {
  opacity: 0;
  transform: translateY(6px);
}
</style>
