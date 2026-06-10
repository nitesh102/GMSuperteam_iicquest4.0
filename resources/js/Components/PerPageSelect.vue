<script setup>
defineProps({ modelValue: { type: Number, default: 10 } })
defineEmits(['update:modelValue'])
</script>

<template>
    <div class="flex items-center gap-2 text-sm text-gray-600">
        <span class="whitespace-nowrap hidden sm:inline">Rows per page</span>
        <select
            :value="modelValue"
            @change="$emit('update:modelValue', Number($event.target.value))"
            class="min-w-[110px] border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer"
        >
            <option :value="10">10 </option>
            <option :value="25">25 </option>
            <option :value="50">50 </option>
            <option :value="100">100 </option>
        </select>
    </div>
</template>
