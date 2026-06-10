<script setup>
import { onMounted, ref } from 'vue'

defineProps({
    modelValue: String,
    type: { type: String, default: 'text' },
})

defineEmits(['update:modelValue'])

const input = ref(null)

onMounted(() => {
    if (input.value?.hasAttribute('autofocus')) input.value.focus()
})

defineExpose({ focus: () => input.value?.focus() })
</script>

<template>
    <input
        v-bind="$attrs"
        :type="type"
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
        ref="input"
        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
    />
</template>
