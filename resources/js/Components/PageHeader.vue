<script setup>
import BackButton from '@/Components/BackButton.vue'

defineProps({
  title: {
    type: String,
    required: true,
  },
  description: {
    type: String,
    default: '',
  },
  showBack: {
    type: Boolean,
    default: true,
  },
})
</script>

<template>
  <div class="mb-8">
    <div v-if="showBack" class="mb-4">
      <BackButton />
    </div>

    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
      <div>
        <h1 class="text-3xl font-bold tracking-tight text-gray-950">
          {{ title }}
        </h1>
        <p v-if="description" class="mt-2 max-w-2xl text-sm leading-6 text-gray-500">
          {{ description }}
        </p>
      </div>

      <div v-if="$slots.actions" class="flex shrink-0 items-center gap-3">
        <slot name="actions" />
      </div>
    </div>
  </div>
</template>
