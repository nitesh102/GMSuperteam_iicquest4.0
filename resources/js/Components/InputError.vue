<script setup>
defineProps({ message: String })
</script>

<template>
  <p v-if="message" class="text-sm text-red-600 mt-1">{{ message }}</p>
</template>