<script setup>
defineProps({
    value: String,
    for:   String,
})
</script>

<template>
    <label :for="$props.for" class="block text-sm font-medium text-gray-700">
        <slot>{{ value }}</slot>
    </label>
</template>
