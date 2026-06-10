<script setup>
import { ArrowUpIcon, ArrowDownIcon } from '@heroicons/vue/24/outline'

defineProps({
  title: String,
  value: {
    type: [String, Number],
    required: true,
  },
  change: String,
  icon: Object,
})
</script>

<template>
  <div class="rounded-xl bg-white border border-gray-200 p-6 shadow-sm hover:shadow-md transition">
    <div class="flex items-start justify-between">
      <div>
        <p class="text-sm text-gray-500">{{ title }}</p>
        <p class="text-3xl font-semibold text-gray-900 mt-2">{{ value }}</p>
        <div v-if="change" class="mt-2 flex items-center gap-1">
          <ArrowUpIcon v-if="change.startsWith('+')" class="h-4 w-4 text-green-600" />
          <ArrowDownIcon v-else class="h-4 w-4 text-red-600" />
          <span :class="change.startsWith('+') ? 'text-green-600' : 'text-red-600'" class="text-xs font-medium">
            {{ change }}
          </span>
        </div>
      </div>
      <div v-if="icon" class="rounded-lg bg-red-50 p-3">
        <component :is="icon" class="h-6 w-6 text-red-500" />
      </div>
    </div>
  </div>
</template>
