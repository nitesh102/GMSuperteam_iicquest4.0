<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import Sidebar from '@/Components/AdminSidebar.vue'
import TopNavbar from '@/Components/AdminTopNavbar.vue'
import VoiceGlobalAssistant from '@/Components/VoiceGlobalAssistant.vue'

const mobileSidebarOpen = ref(false)
const windowWidth = ref(typeof window !== 'undefined' ? window.innerWidth : 1024)

const isMobile = computed(() => windowWidth.value < 1024)

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
  <div class="flex h-screen bg-gray-50">
    <!-- Sidebar -->
    <Sidebar
      :mobile-open="mobileSidebarOpen"
      @close-mobile="closeMobileSidebar"
    />

    <!-- Mobile Sidebar Overlay -->
    <div
      v-if="mobileSidebarOpen && isMobile"
      class="fixed inset-0 bg-black/50 z-30 lg:hidden"
      @click="closeMobileSidebar"
    />

    <!-- Main Content -->
    <div class="flex-1 flex flex-col lg:ml-60">
      <!-- Top Navbar -->
      <TopNavbar @toggle-sidebar="toggleMobileSidebar" />

      <!-- Content Area -->
      <main class="flex-1 overflow-auto">
        <div class="pt-8 px-4 sm:px-6 lg:px-8 pb-12">
          <slot />
        </div>
      </main>
    </div>

    <VoiceGlobalAssistant />
  </div>
</template>

<style scoped>
</style>
