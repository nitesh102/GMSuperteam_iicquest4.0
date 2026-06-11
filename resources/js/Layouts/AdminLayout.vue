<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { usePage } from '@inertiajs/vue3'

import Navbar  from '@/Components/Navbar.vue'
import Sidebar from '@/Components/Sidebar.vue'
import Footer  from '@/Components/Footer.vue'
import VoiceGlobalAssistant from '@/Components/VoiceGlobalAssistant.vue'

const sidebarCollapsed  = ref(false)
const mobileSidebarOpen = ref(false)

const windowWidth = ref(typeof window !== 'undefined' ? window.innerWidth : 1024)
const isMobile    = computed(() => windowWidth.value < 1024)

const SIDEBAR_EXPANDED  = 300
const SIDEBAR_COLLAPSED = 80
const NAVBAR_HEIGHT     = 64

const sidebarWidth = computed(() => {
    if (isMobile.value) return 0
    return sidebarCollapsed.value ? SIDEBAR_COLLAPSED : SIDEBAR_EXPANDED
})

const page = usePage()
const pageKey = computed(() => page.url)

const lockBody = (lock) => {
    if (typeof document !== 'undefined')
        document.body.style.overflow = lock ? 'hidden' : ''
}

const toggleSidebar = () => {
    if (isMobile.value) {
        mobileSidebarOpen.value = !mobileSidebarOpen.value
        lockBody(mobileSidebarOpen.value)
    } else {
        sidebarCollapsed.value = !sidebarCollapsed.value
    }
}

const closeMobileSidebar = () => {
    mobileSidebarOpen.value = false
    lockBody(false)
}

const handleResize = () => {
    windowWidth.value = window.innerWidth
    if (!isMobile.value && mobileSidebarOpen.value) closeMobileSidebar()
    if (isMobile.value) sidebarCollapsed.value = false
}

const handleEscape = (e) => {
    if (e.key === 'Escape' && mobileSidebarOpen.value) closeMobileSidebar()
}

onMounted(() => {
    window.addEventListener('resize', handleResize)
    document.addEventListener('keydown', handleEscape)
})

onUnmounted(() => {
    window.removeEventListener('resize', handleResize)
    document.removeEventListener('keydown', handleEscape)
    lockBody(false)
})
</script>

<template>
    <div class="min-h-screen bg-gray-50">

        <Navbar
            :sidebar-width="sidebarWidth"
            :sidebar-collapsed="sidebarCollapsed"
            :mobile-sidebar-open="mobileSidebarOpen"
            @toggle-sidebar="toggleSidebar"
        />

        <Sidebar
            :collapsed="sidebarCollapsed"
            :mobile-open="mobileSidebarOpen"
            :is-mobile="isMobile"
            @toggle="toggleSidebar"
            @close-mobile="closeMobileSidebar"
        />

        <Transition name="fade">
            <div
                v-if="mobileSidebarOpen && isMobile"
                class="fixed inset-0 z-30 bg-black/40 lg:hidden"
                @click="closeMobileSidebar"
            />
        </Transition>

        <div
            class="flex min-h-screen flex-col transition-all duration-300 ease-in-out"
            :style="{
                paddingBlockStart:  NAVBAR_HEIGHT + 'px',
                paddingInlineStart: sidebarWidth + 'px',
            }"
        >
            <main class="flex-1">
                <div class="p-4 sm:p-6">
                    <slot />
                </div>
            </main>

            <Footer />
        </div>

        <VoiceGlobalAssistant />
    </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active { transition: opacity 0.25s ease; }
.fade-enter-from,
.fade-leave-to     { opacity: 0; }
</style>
