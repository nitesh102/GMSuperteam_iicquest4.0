<!-- resources/js/Components/UserModal.vue -->
<template>
    <Modal :show="show" @close="closeModal">
        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
            <div class="sm:flex sm:items-start">
                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                        {{ isEditMode ? 'Edit User' : 'Create New User' }}
                    </h3>

                    <form @submit.prevent="submitForm">
                        <div class="space-y-4">
                            <!-- Name -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                                <input
                                    type="text"
                                    v-model="form.name"
                                    :class="[
                                        'w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent',
                                        form.errors.name ? 'border-red-300' : 'border-gray-300'
                                    ]"
                                    required
                                >
                                <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                            </div>

                            <!-- Email -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                                <input
                                    type="email"
                                    v-model="form.email"
                                    :class="[
                                        'w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent',
                                        form.errors.email ? 'border-red-300' : 'border-gray-300'
                                    ]"
                                    required
                                >
                                <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
                            </div>

                            <!-- Password (only for create) -->
                            <div v-if="!isEditMode">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Password *</label>
                                <input
                                    type="password"
                                    v-model="form.password"
                                    :class="[
                                        'w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent',
                                        form.errors.password ? 'border-red-300' : 'border-gray-300'
                                    ]"
                                    required
                                >
                                <p v-if="form.errors.password" class="mt-1 text-sm text-red-600">{{ form.errors.password }}</p>
                            </div>

                            <!-- Confirm Password (only for create) -->
                            <div v-if="!isEditMode">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password *</label>
                                <input
                                    type="password"
                                    v-model="form.password_confirmation"
                                    :class="[
                                        'w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent',
                                        form.errors.password_confirmation ? 'border-red-300' : 'border-gray-300'
                                    ]"
                                    required
                                >
                                <p v-if="form.errors.password_confirmation" class="mt-1 text-sm text-red-600">{{ form.errors.password_confirmation }}</p>
                            </div>

                            <!-- Roles -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Roles</label>
                                <div class="space-y-2">
                                    <div v-for="role in availableRoles" :key="role.id" class="flex items-center">
                                        <input
                                            type="checkbox"
                                            :id="`role-${role.id}`"
                                            :value="role.id"
                                            v-model="form.roles"
                                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                        />
                                        <label :for="`role-${role.id}`" class="ml-2 text-sm text-gray-700">
                                            {{ role.name }}
                                        </label>
                                    </div>
                                </div>
                                <p v-if="form.errors.roles" class="mt-1 text-sm text-red-600">{{ form.errors.roles }}</p>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="mt-6 flex justify-end space-x-3">
                            <button
                                type="button"
                                @click="closeModal"
                                class="px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                :class="[
                                    'px-4 py-2 text-sm font-medium rounded-md text-white',
                                    form.processing ? 'bg-indigo-400 cursor-not-allowed' : 'bg-indigo-600 hover:bg-indigo-700'
                                ]"
                            >
                                <span v-if="form.processing">
                                    <i class="fas fa-spinner fa-spin mr-2"></i> Saving...
                                </span>
                                <span v-else>
                                    {{ isEditMode ? 'Update User' : 'Create User' }}
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </Modal>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Modal from './Modal.vue';

const props = defineProps({
    show: Boolean,
    user: Object,
    availableRoles: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(['close', 'success']);

const isEditMode = computed(() => !!props.user);

// Initialize form
const form = useForm({
    name: props.user?.name || '',
    email: props.user?.email || '',
    password: '',
    password_confirmation: '',
    roles: props.user?.roles?.map(role => role.id) || []
});

// Watch for user prop changes (for edit mode)
watch(() => props.user, (newUser) => {
    if (newUser) {
        form.name = newUser.name || '';
        form.email = newUser.email || '';
        form.roles = newUser.roles?.map(role => role.id) || [];
    }
}, { immediate: true });

const closeModal = () => {
    form.reset();
    emit('close');
};

const submitForm = () => {
    if (isEditMode.value) {
        form.put(route('users.update', props.user.id), {
            preserveScroll: true,
            onSuccess: () => {
                emit('success');
                closeModal();
            }
        });
    } else {
        form.post(route('users.store'), {
            preserveScroll: true,
            onSuccess: () => {
                emit('success');
                closeModal();
            }
        });
    }
};
</script>
