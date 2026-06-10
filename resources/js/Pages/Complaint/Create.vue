<script setup>
import { ref, computed, watch } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const { departments, categories } = usePage().props;

const form = useForm({
    department_id: '',
    category_id: '',
    title: '',
    description: '',
    location: '',
    latitude: '',
    longitude: '',
});

const attachments = ref([]);
const fileInput = ref(null);
const previews = ref([]);

function handleFileInput(e) {
    const files = Array.from(e.target.files || []);
    const remaining = 5 - attachments.value.length;
    const allowed = files.slice(0, remaining);
    attachments.value = [...attachments.value, ...allowed];
    generatePreviews();
    if (files.length > remaining) {
        alert('Maximum 5 images allowed.');
    }
}

function removeFile(index) {
    attachments.value.splice(index, 1);
    previews.value.splice(index, 1);
}

function generatePreviews() {
    previews.value = attachments.value.map(f => URL.createObjectURL(f));
}

function triggerFileInput() {
    fileInput.value?.click();
}

// ✅ Improved: Reset category when department changes
watch(() => form.department_id, () => {
    form.category_id = '';
});

const filteredCategories = computed(() => {
    if (!form.department_id || !categories) return [];

    return categories.filter(cat => {
        return String(cat.department_id) === String(form.department_id);
    });
});

const flashMessage = ref(null);
let flashTimer = null;

watch(() => usePage().props.flash?.success, (val) => {
    if (flashTimer) clearTimeout(flashTimer);
    flashMessage.value = val || null;
    if (flashMessage.value) {
        flashTimer = setTimeout(() => flashMessage.value = null, 4000);
    }
}, { immediate: true });

function resetForm() {
    form.reset();
    form.department_id = '';
    form.category_id = '';
    form.title = '';
    form.description = '';
    form.location = '';
    form.latitude = '';
    form.longitude = '';
    attachments.value = [];
    previews.value = [];
    form.clearErrors();
}

function submitForm() {
    form.attachments = attachments.value;
    form.post(route('complaints.store'), {
        preserveScroll: true,
        onSuccess: () => {
            resetForm();
        },
    });
}
</script>

<template>
    <Head title="Submit Complaint" />

    <AuthenticatedLayout>
        <div class="py-6">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
                <!-- Header -->
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Submit Complaint</h1>
                        <p class="mt-1 text-sm text-gray-500">
                            Fill in the details below to submit a new citizen complaint.
                        </p>
                    </div>
                    <a
                        :href="route('complaints.index')"
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors duration-150"
                    >
                        <i class="fas fa-arrow-left text-xs"></i>
                        Back
                    </a>
                </div>

                <!-- Form Card -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                    <div class="px-6 py-6 sm:px-8">
                        <form @submit.prevent="submitForm" class="space-y-6">

                            <!-- Department + Category -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                <!-- Department -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                        Department <span class="text-red-400">*</span>
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fas fa-building text-gray-400 text-sm"></i>
                                        </div>
                                        <select
                                            v-model="form.department_id"
                                            :class="[
                                                'w-full pl-9 pr-3 py-2.5 text-sm border rounded-lg focus:outline-none focus:ring-2 transition-all duration-150 appearance-none bg-white',
                                                form.errors.department_id
                                                    ? 'border-red-300 bg-red-50/50 focus:ring-red-500/20 focus:border-red-400'
                                                    : 'border-gray-200 bg-white focus:ring-indigo-500/20 focus:border-indigo-500'
                                            ]"
                                            required
                                        >
                                            <option value="" disabled>Select a department</option>
                                            <option
                                                v-for="dept in departments"
                                                :key="dept.id"
                                                :value="dept.id"
                                            >
                                                {{ dept.name }}
                                            </option>
                                        </select>
                                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                            <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                                        </div>
                                    </div>
                                    <p v-if="form.errors.department_id" class="mt-1.5 text-sm text-red-600 flex items-center gap-1.5">
                                        <i class="fas fa-exclamation-circle text-xs"></i>
                                        <span>{{ form.errors.department_id }}</span>
                                    </p>
                                </div>

                                <!-- Category -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                        Category <span class="text-red-400">*</span>
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fas fa-tag text-gray-400 text-sm"></i>
                                        </div>
                                        <select
                                            v-model="form.category_id"
                                            :class="[
                                                'w-full pl-9 pr-3 py-2.5 text-sm border rounded-lg focus:outline-none focus:ring-2 transition-all duration-150 appearance-none bg-white',
                                                form.errors.category_id
                                                    ? 'border-red-300 bg-red-50/50 focus:ring-red-500/20 focus:border-red-400'
                                                    : 'border-gray-200 bg-white focus:ring-indigo-500/20 focus:border-indigo-500'
                                            ]"
                                            :disabled="!form.department_id"
                                            required
                                        >
                                            <option value="" disabled>
                                                {{ form.department_id ? 'Select a category' : 'Select a department first' }}
                                            </option>
                                            <option
                                                v-for="cat in filteredCategories"
                                                :key="cat.id"
                                                :value="cat.id"
                                            >
                                                {{ cat.name }}
                                            </option>
                                        </select>
                                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                            <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                                        </div>
                                    </div>
                                    <p v-if="form.errors.category_id" class="mt-1.5 text-sm text-red-600 flex items-center gap-1.5">
                                        <i class="fas fa-exclamation-circle text-xs"></i>
                                        <span>{{ form.errors.category_id }}</span>
                                    </p>
                                </div>
                            </div>

                            <!-- Title -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                    Title <span class="text-red-400">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-heading text-gray-400 text-sm"></i>
                                    </div>
                                    <input
                                        type="text"
                                        v-model="form.title"
                                        :class="[
                                            'w-full pl-9 pr-3 py-2.5 text-sm border rounded-lg focus:outline-none focus:ring-2 transition-all duration-150 placeholder:text-gray-400',
                                            form.errors.title
                                                ? 'border-red-300 bg-red-50/50 focus:ring-red-500/20 focus:border-red-400'
                                                : 'border-gray-200 bg-white focus:ring-indigo-500/20 focus:border-indigo-500'
                                        ]"
                                        placeholder="e.g. Pothole on Main Street"
                                        required
                                    />
                                </div>
                                <p v-if="form.errors.title" class="mt-1.5 text-sm text-red-600 flex items-center gap-1.5">
                                    <i class="fas fa-exclamation-circle text-xs"></i>
                                    <span>{{ form.errors.title }}</span>
                                </p>
                            </div>

                            <!-- Description -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                    Description <span class="text-red-400">*</span>
                                </label>
                                <textarea
                                    v-model="form.description"
                                    rows="6"
                                    :class="[
                                        'w-full px-3 py-2.5 text-sm border rounded-lg focus:outline-none focus:ring-2 transition-all duration-150 resize-none placeholder:text-gray-400',
                                        form.errors.description
                                            ? 'border-red-300 bg-red-50/50 focus:ring-red-500/20 focus:border-red-400'
                                            : 'border-gray-200 bg-white focus:ring-indigo-500/20 focus:border-indigo-500'
                                    ]"
                                    placeholder="Describe the issue in detail..."
                                    maxlength="10000"
                                ></textarea>
                                <div class="flex justify-between mt-1.5">
                                    <p v-if="form.errors.description" class="text-sm text-red-600 flex items-center gap-1.5">
                                        <i class="fas fa-exclamation-circle text-xs"></i>
                                        <span>{{ form.errors.description }}</span>
                                    </p>
                                    <p class="text-xs text-gray-400">{{ form.description.length }} / 10,000</p>
                                </div>
                            </div>

                            <!-- Photos -->
                            <div class="border-t border-gray-100 pt-6">
                                <h4 class="text-sm font-semibold text-gray-900 mb-4">Photos (Optional)</h4>
                                <p class="text-xs text-gray-500 mb-3">Upload up to 5 images (max 5MB each).</p>

                                <div
                                    @click="triggerFileInput"
                                    class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-gray-200 rounded-xl bg-gray-50/50 hover:bg-gray-100 hover:border-indigo-300 cursor-pointer transition-all"
                                >
                                    <i class="fas fa-cloud-upload-alt text-gray-300 text-2xl mb-1.5"></i>
                                    <p class="text-sm text-gray-500">Click to upload images</p>
                                    <input
                                        ref="fileInput"
                                        type="file"
                                        multiple
                                        accept="image/jpeg,image/png,image/webp"
                                        class="hidden"
                                        @change="handleFileInput"
                                    />
                                </div>

                                <div v-if="attachments.length" class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-3 mt-3">
                                    <div
                                        v-for="(file, idx) in attachments"
                                        :key="idx"
                                        class="relative group aspect-square rounded-lg overflow-hidden border border-gray-200 bg-gray-50"
                                    >
                                        <img :src="previews[idx]" class="w-full h-full object-cover" />
                                        <button
                                            type="button"
                                            @click="removeFile(idx)"
                                            class="absolute top-1 right-1 w-6 h-6 rounded-full bg-red-500/80 text-white flex items-center justify-center opacity-0 group-hover:opacity-100 hover:bg-red-600"
                                        >
                                            <i class="fas fa-times text-xs"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Location -->
                            <div class="border-t border-gray-100 pt-6">
                                <h4 class="text-sm font-semibold text-gray-900 mb-4">Location (Optional)</h4>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Address / Location</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fas fa-map-marker-alt text-gray-400 text-sm"></i>
                                        </div>
                                        <input
                                            type="text"
                                            v-model="form.location"
                                            class="w-full pl-9 pr-3 py-2.5 text-sm border rounded-lg focus:outline-none focus:ring-2"
                                            placeholder="e.g. 123 Main Street"
                                        />
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Latitude</label>
                                        <input type="number" step="any" v-model="form.latitude" class="w-full px-3 py-2.5 text-sm border rounded-lg" placeholder="Latitude" />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Longitude</label>
                                        <input type="number" step="any" v-model="form.longitude" class="w-full px-3 py-2.5 text-sm border rounded-lg" placeholder="Longitude" />
                                    </div>
                                </div>
                            </div>

                            <!-- Buttons -->
                            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                                <a :href="route('complaints.index')" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">
                                    Cancel
                                </a>
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="px-6 py-2.5 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg disabled:bg-indigo-400"
                                >
                                    <span v-if="form.processing">Submitting...</span>
                                    <span v-else>Submit Complaint</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- AI Note -->
                <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
                    <div class="flex gap-3">
                        <i class="fas fa-robot text-amber-600 mt-0.5"></i>
                        <div>
                            <p class="text-sm font-medium text-amber-800">AI Analysis</p>
                            <p class="text-sm text-amber-700 mt-0.5">
                                Our AI will automatically analyze the complaint to determine priority, category, and generate a summary.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
