<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
    modelValue: { type: String, default: '' },
    placeholder: { type: String, default: 'Search...' },
})

const emit = defineEmits(['update:modelValue'])

const local = ref(props.modelValue)
let timer = null

watch(local, (val) => {
    clearTimeout(timer)
    timer = setTimeout(() => emit('update:modelValue', val), 350)
})
</script>

<template>
    <div class="relative">
        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
        <input
            v-model="local"
            type="text"
            :placeholder="placeholder"
            class="pl-9 pr-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent w-56 sm:w-72"
        />
    </div>
</template>
