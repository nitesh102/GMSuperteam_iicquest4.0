<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'

import Navbar  from '@/Components/Navbar.vue'
import Sidebar from '@/Components/Sidebar.vue'
import Footer  from '@/Components/Footer.vue'
import VoiceGlobalAssistant from '@/Components/VoiceGlobalAssistant.vue'

/* ── Sidebar state ── */
const sidebarCollapsed  = ref(false)
const mobileSidebarOpen = ref(false)

/* ── Responsive breakpoint ── */
const windowWidth = ref(typeof window !== 'undefined' ? window.innerWidth : 1024)
const isMobile    = computed(() => windowWidth.value < 1024)

/* ── Sidebar width values (must match Sidebar.vue) ── */
const SIDEBAR_EXPANDED  = 256   // w-64  = 16rem = 256px
const SIDEBAR_COLLAPSED = 64    // w-16  = 4rem  = 64px
const NAVBAR_HEIGHT     = 64    // h-16  = 4rem  = 64px

const sidebarWidth = computed(() => {
    if (isMobile.value) return 0                              // no offset on mobile (drawer is overlay)
    return sidebarCollapsed.value ? SIDEBAR_COLLAPSED : SIDEBAR_EXPANDED
})

/* ── Body scroll lock on mobile drawer ── */
const lockBody = (lock) => {
    if (typeof document !== 'undefined')
        document.body.style.overflow = lock ? 'hidden' : ''
}

/* ── Toggle handlers ── */
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

/* ── Window resize ── */
const handleResize = () => {
    windowWidth.value = window.innerWidth
    if (!isMobile.value && mobileSidebarOpen.value) closeMobileSidebar()
    if (isMobile.value) sidebarCollapsed.value = false
}

/* ── Escape key ── */
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

        <!-- ══════════════════════════════════════
             FIXED NAVBAR  (z-50, full width, h-16)
        ══════════════════════════════════════ -->
        <Navbar
            :sidebar-collapsed="sidebarCollapsed"
            :mobile-sidebar-open="mobileSidebarOpen"
            @toggle-sidebar="toggleSidebar"
        />

        <!-- ══════════════════════════════════════
             FIXED SIDEBAR  (z-40, below navbar)
             Sidebar.vue handles mobile vs desktop.
        ══════════════════════════════════════ -->
        <Sidebar
            :collapsed="sidebarCollapsed"
            :mobile-open="mobileSidebarOpen"
            :is-mobile="isMobile"
            @toggle="toggleSidebar"
            @close-mobile="closeMobileSidebar"
        />

        <!-- ══════════════════════════════════════
             MOBILE BACKDROP
             Shown when mobile drawer is open.
        ══════════════════════════════════════ -->
        <Transition name="fade">
            <div
                v-if="mobileSidebarOpen && isMobile"
                class="fixed inset-0 z-30 bg-black/40 lg:hidden"
                @click="closeMobileSidebar"
            />
        </Transition>

        <!-- ══════════════════════════════════════
             MAIN CONTENT
             Offset top by navbar height.
             Offset left by sidebar width (desktop only).
             Both via inline style for pixel-perfect sync
             with the fixed sidebar widths.
        ══════════════════════════════════════ -->
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
