<script setup>
defineProps({
  icon: Object,
  title: String,
  description: String,
  buttonText: String,
  linkText: String,
  buttonHref: String,
  linkHref: String,
})

defineEmits(['button-click', 'link-click'])
</script>

<template>
  <div class="rounded-xl bg-white border border-gray-200 shadow-sm">
    <div class="flex flex-col items-center justify-center py-16 px-6">
      <!-- Icon -->
      <div v-if="icon" class="rounded-full bg-gray-100 p-6 mb-4">
        <component :is="icon" class="h-16 w-16 text-gray-400" />
      </div>

      <!-- Title -->
      <h3 class="text-2xl font-semibold text-gray-900 mb-2">{{ title }}</h3>

      <!-- Description -->
      <p class="text-gray-500 text-center mb-8 max-w-md">{{ description }}</p>

      <!-- Button -->
      <a
        v-if="buttonHref"
        :href="buttonHref"
        class="inline-flex items-center gap-2 rounded-lg bg-red-500 px-6 py-3 text-sm font-medium text-white hover:bg-red-600 transition mb-4"
      >
        {{ buttonText }}
      </a>
      <button
        v-else-if="buttonText"
        @click="$emit('button-click')"
        class="inline-flex items-center gap-2 rounded-lg bg-red-500 px-6 py-3 text-sm font-medium text-white hover:bg-red-600 transition mb-4"
      >
        {{ buttonText }}
      </button>

      <!-- Link -->
      <a
        v-if="linkHref"
        :href="linkHref"
        class="text-red-500 hover:text-red-600 font-medium text-sm transition"
      >
        {{ linkText }}
      </a>
      <button
        v-else-if="linkText"
        @click="$emit('link-click')"
        class="text-red-500 hover:text-red-600 font-medium text-sm transition"
      >
        {{ linkText }}
      </button>
    </div>
  </div>
</template>
