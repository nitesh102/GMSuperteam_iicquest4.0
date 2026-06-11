<script setup>
import { useI18n } from 'vue-i18n'
import { ChevronLeftIcon, ChevronRightIcon } from '@heroicons/vue/24/outline'

const { t } = useI18n()

const props = defineProps({
  currentPage: Number,
  totalPages: Number,
  totalItems: Number,
  pageSize: Number,
})

const emit = defineEmits(['update:currentPage'])

const handlePrevious = () => {
  if (props.currentPage > 1) {
    emit('update:currentPage', props.currentPage - 1)
  }
}

const handleNext = () => {
  if (props.currentPage < props.totalPages) {
    emit('update:currentPage', props.currentPage + 1)
  }
}
</script>

<template>
  <div class="flex items-center justify-between">
    <p class="text-sm text-gray-500">
      {{ t('table.showing', {
        from: (currentPage - 1) * pageSize + 1,
        to: Math.min(currentPage * pageSize, totalItems),
        total: totalItems,
      }) }}
    </p>

    <div class="flex items-center gap-2">
      <button
        @click="handlePrevious"
        :disabled="currentPage === 1"
        class="btn-ghost text-xs disabled:opacity-30 disabled:cursor-not-allowed"
      >
        <ChevronLeftIcon class="h-4 w-4" />
        {{ t('table.previous') }}
      </button>

      <div class="flex items-center gap-1">
        <span class="text-sm text-gray-500">
          {{ t('table.page', { current: currentPage, total: totalPages }) }}
        </span>
      </div>

      <button
        @click="handleNext"
        :disabled="currentPage === totalPages"
        class="btn-ghost text-xs disabled:opacity-30 disabled:cursor-not-allowed"
      >
        {{ t('table.next') }}
        <ChevronRightIcon class="h-4 w-4" />
      </button>
    </div>
  </div>
</template>
