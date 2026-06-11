<template>
    <transition name="slide">
        <aside
            v-if="isMobile && mobileOpen"
            class="fixed left-0 top-16 z-50 flex h-screen w-[300px] flex-col border-r border-gray-200 bg-white text-gray-900 shadow-xl lg:hidden"
        >
            <SidebarContent :collapsed="false" :is-mobile="true" @close="$emit('close-mobile')" />
        </aside>
    </transition>

    <aside
        class="fixed left-0 top-0 z-40 hidden h-screen flex-col border-r border-gray-200 bg-white text-gray-900 transition-all duration-300 ease-in-out lg:flex"
        :class="collapsed ? 'w-[80px]' : 'w-[300px]'"
    >
        <SidebarContent :collapsed="collapsed" :is-mobile="false" @toggle="$emit('toggle')" />
    </aside>
</template>

<script setup>
import SidebarContent from '@/Components/SidebarContent.vue'

defineProps({
    collapsed: Boolean,
    mobileOpen: Boolean,
    isMobile: Boolean,
})

defineEmits(['toggle', 'close-mobile'])
</script>

<style scoped>
.slide-enter-active,
.slide-leave-active { transition: transform 0.25s ease; }
.slide-enter-from,
.slide-leave-to { transform: translateX(-100%); }
</style>
