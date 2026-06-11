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
  <div class="rounded-2xl bg-white border border-gray-200 shadow-sm transition-all duration-300 hover:shadow-lg">
    <div class="flex flex-col items-center justify-center py-20 px-6">
      <!-- Icon -->
      <div v-if="icon" class="rounded-3xl bg-red-50 p-8 mb-6 shadow-inner">
        <component :is="icon" class="h-16 w-16 text-red-500" />
      </div>

      <!-- Title -->
      <h3 class="text-2xl font-bold text-gray-900 mb-3">{{ title }}</h3>

      <!-- Description -->
      <p class="text-gray-600 text-center mb-8 max-w-md leading-relaxed">{{ description }}</p>

      <!-- Button -->
      <a
        v-if="buttonHref"
        :href="buttonHref"
        class="inline-flex items-center gap-2 rounded-xl bg-red-500 px-6 py-3 text-sm font-medium text-white shadow-sm transition-all duration-200 hover:scale-[1.02] hover:bg-red-600 hover:shadow-md mb-4"
      >
        {{ buttonText }}
      </a>
      <button
        v-else-if="buttonText"
        @click="$emit('button-click')"
        class="inline-flex items-center gap-2 rounded-xl bg-red-500 px-6 py-3 text-sm font-medium text-white shadow-sm transition-all duration-200 hover:scale-[1.02] hover:bg-red-600 hover:shadow-md mb-4"
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
