<script setup>
import { ArrowLeftIcon } from '@heroicons/vue/24/outline'

function goBack() {
  window.history.back()
}
</script>

<template>
  <button
    type="button"
    aria-label="Go back"
    class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-gray-100 text-gray-700 transition-all duration-200 hover:-translate-x-1 hover:bg-gray-200 hover:text-gray-950 focus:outline-none focus:ring-4 focus:ring-gray-200"
    @click="goBack"
  >
    <ArrowLeftIcon class="h-5 w-5" />
  </button>
</template>
