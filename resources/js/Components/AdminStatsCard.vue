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
  <div class="card p-5 hover:-translate-y-0.5 hover:shadow-lg transition-all duration-300">
    <div class="flex items-start justify-between">
      <div class="min-w-0">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">{{ title }}</p>
        <p class="text-2xl font-bold text-gray-900 mt-2">{{ value }}</p>
        <div v-if="change" class="mt-2 flex items-center gap-1">
          <ArrowUpIcon v-if="change.startsWith('+')" class="h-3.5 w-3.5 text-green-600" />
          <ArrowDownIcon v-else class="h-3.5 w-3.5 text-red-600" />
          <span :class="change.startsWith('+') ? 'text-green-600' : 'text-red-600'" class="text-xs font-medium">
            {{ change }}
          </span>
        </div>
      </div>
      <div v-if="icon" class="rounded-xl bg-red-50 p-3 flex-shrink-0 ml-3">
        <component :is="icon" class="h-5 w-5 text-red-500" />
      </div>
    </div>
  </div>
</template>
