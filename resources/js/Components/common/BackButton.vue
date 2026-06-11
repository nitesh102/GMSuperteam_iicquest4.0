<script setup>
import { computed } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  href: { type: String, default: null },
})

const goBack = () => {
  if (props.href) {
    router.visit(props.href)
    return
  }
  if (window.history.length > 1) {
    window.history.back()
  } else {
    router.visit(route('dashboard'))
  }
}
</script>

<template>
  <button
    @click="goBack"
    class="inline-flex items-center gap-2 bg-white border border-gray-200 rounded-xl px-[18px] h-[44px] text-sm font-medium text-gray-700 shadow-sm hover:-translate-y-0.5 hover:shadow-[0_12px_30px_rgba(0,0,0,0.08)] transition-all duration-200 flex-shrink-0"
  >
    <svg class="w-4 h-4 text-[#1D4ED8]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
      <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
    </svg>
    <span>Back</span>
  </button>
</template>
