<!-- resources/js/Components/Modal.vue -->
<template>
    <teleport to="body">
        <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto">
            <!-- Background overlay -->
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="$emit('close')"></div>

            <!-- Modal container -->
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <!-- Modal panel -->
                <div class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
                    <!-- Close button -->
                    <div class="absolute right-0 top-0 pr-4 pt-4">
                        <button
                            type="button"
                            class="rounded-md bg-white text-gray-400 hover:text-gray-500 focus:outline-none"
                            @click="$emit('close')"
                        >
                            <span class="sr-only">Close</span>
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <!-- Modal content -->
                    <slot></slot>
                </div>
            </div>
        </div>
    </teleport>
</template>

<script setup>
defineProps({
    show: Boolean
});

defineEmits(['close']);
</script>
