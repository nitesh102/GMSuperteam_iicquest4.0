<script setup>
defineProps({
  title: String,
  value: {
    type: [String, Number],
    required: true,
  },
  icon: Object,
})
</script>

<template>
  <div class="rounded-xl bg-white border border-gray-200 p-6 shadow-sm hover:shadow-md transition">
    <div class="flex items-start justify-between">
      <div>
        <p class="text-sm text-gray-500">{{ title }}</p>
        <p class="text-3xl font-semibold text-gray-900 mt-2">{{ value }}</p>
      </div>
      <div v-if="icon" class="rounded-lg bg-red-50 p-3">
        <component :is="icon" class="h-6 w-6 text-red-500" />
      </div>
    </div>
  </div>
</template>
