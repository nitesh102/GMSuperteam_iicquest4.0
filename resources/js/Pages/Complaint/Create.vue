<script setup>
import { ref, computed, watch } from 'vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
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

const filteredCategories = computed(() => {
    if (!form.department_id || !categories) return [];
    return categories.filter(c => c.department_id === Number(form.department_id));
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
        },
    });
}
</script>

<template>
    <Head title="Submit Complaint" />

    <AuthenticatedLayout>
        <div class="py-6">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
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

                <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                    <div class="px-6 py-6 sm:px-8">
                        <form @submit.prevent="submitForm" class="space-y-6">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
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
                                            @change="form.category_id = ''"
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
                                    placeholder="Describe the issue in detail. Include any relevant information that will help the department address the problem."
                                    maxlength="10000"
                                ></textarea>
                                <div class="flex items-center justify-between mt-1.5">
                                    <p v-if="form.errors.description" class="text-sm text-red-600 flex items-center gap-1.5">
                                        <i class="fas fa-exclamation-circle text-xs"></i>
                                        <span>{{ form.errors.description }}</span>
                                    </p>
                                    <p v-else></p>
                                    <p class="text-xs text-gray-400">{{ form.description.length }} / 10,000</p>
                                </div>
                            </div>

                            <div class="border-t border-gray-100 pt-6">
                                <h4 class="text-sm font-semibold text-gray-900 mb-4">Photos (Optional)</h4>
                                <p class="text-xs text-gray-500 mb-3">Upload up to 5 images (JPEG, PNG, WebP, max 5MB each).</p>

                                <div
                                    @click="triggerFileInput"
                                    class="relative flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-gray-200 rounded-xl bg-gray-50/50 hover:bg-gray-100 hover:border-indigo-300 cursor-pointer transition-all duration-150"
                                >
                                    <i class="fas fa-cloud-upload-alt text-gray-300 text-2xl mb-1.5"></i>
                                    <p class="text-sm text-gray-500">Click to upload images</p>
                                    <input
                                        ref="fileInput"
                                        type="file"
                                        multiple
                                        accept="image/jpeg,image/png,image/jpg,image/gif,image/webp"
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
                                        <img
                                            :src="previews[idx]"
                                            :alt="file.name"
                                            class="w-full h-full object-cover"
                                        />
                                        <button
                                            type="button"
                                            @click="removeFile(idx)"
                                            class="absolute top-1 right-1 w-6 h-6 rounded-full bg-red-500/80 text-white flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-150 hover:bg-red-600"
                                        >
                                            <i class="fas fa-times text-xs"></i>
                                        </button>
                                        <div class="absolute bottom-0 inset-x-0 bg-gradient-to-t from-black/50 to-transparent p-1.5">
                                            <p class="text-xs text-white truncate leading-tight">{{ file.name }}</p>
                                        </div>
                                    </div>
                                </div>

                                <p v-if="form.errors.attachments" class="mt-1.5 text-sm text-red-600 flex items-center gap-1.5">
                                    <i class="fas fa-exclamation-circle text-xs"></i>
                                    <span>{{ form.errors.attachments }}</span>
                                </p>
                                <p v-if="form.errors['attachments.0']" class="mt-1.5 text-sm text-red-600 flex items-center gap-1.5">
                                    <i class="fas fa-exclamation-circle text-xs"></i>
                                    <span>{{ form.errors['attachments.0'] }}</span>
                                </p>
                            </div>

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
                                            :class="[
                                                'w-full pl-9 pr-3 py-2.5 text-sm border rounded-lg focus:outline-none focus:ring-2 transition-all duration-150 placeholder:text-gray-400',
                                                form.errors.location
                                                    ? 'border-red-300 bg-red-50/50 focus:ring-red-500/20 focus:border-red-400'
                                                    : 'border-gray-200 bg-white focus:ring-indigo-500/20 focus:border-indigo-500'
                                            ]"
                                            placeholder="e.g. 123 Main Street, Downtown"
                                        />
                                    </div>
                                    <p v-if="form.errors.location" class="mt-1.5 text-sm text-red-600 flex items-center gap-1.5">
                                        <i class="fas fa-exclamation-circle text-xs"></i>
                                        <span>{{ form.errors.location }}</span>
                                    </p>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Latitude</label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <i class="fas fa-arrows-alt text-gray-400 text-sm"></i>
                                            </div>
                                            <input
                                                type="number"
                                                step="any"
                                                v-model="form.latitude"
                                                :class="[
                                                    'w-full pl-9 pr-3 py-2.5 text-sm border rounded-lg focus:outline-none focus:ring-2 transition-all duration-150 placeholder:text-gray-400',
                                                    form.errors.latitude
                                                        ? 'border-red-300 bg-red-50/50 focus:ring-red-500/20 focus:border-red-400'
                                                        : 'border-gray-200 bg-white focus:ring-indigo-500/20 focus:border-indigo-500'
                                                ]"
                                                placeholder="-90 to 90"
                                            />
                                        </div>
                                        <p v-if="form.errors.latitude" class="mt-1.5 text-sm text-red-600 flex items-center gap-1.5">
                                            <i class="fas fa-exclamation-circle text-xs"></i>
                                            <span>{{ form.errors.latitude }}</span>
                                        </p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Longitude</label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <i class="fas fa-arrows-alt-h text-gray-400 text-sm"></i>
                                            </div>
                                            <input
                                                type="number"
                                                step="any"
                                                v-model="form.longitude"
                                                :class="[
                                                    'w-full pl-9 pr-3 py-2.5 text-sm border rounded-lg focus:outline-none focus:ring-2 transition-all duration-150 placeholder:text-gray-400',
                                                    form.errors.longitude
                                                        ? 'border-red-300 bg-red-50/50 focus:ring-red-500/20 focus:border-red-400'
                                                        : 'border-gray-200 bg-white focus:ring-indigo-500/20 focus:border-indigo-500'
                                                ]"
                                                placeholder="-180 to 180"
                                            />
                                        </div>
                                        <p v-if="form.errors.longitude" class="mt-1.5 text-sm text-red-600 flex items-center gap-1.5">
                                            <i class="fas fa-exclamation-circle text-xs"></i>
                                            <span>{{ form.errors.longitude }}</span>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                                <a
                                    :href="route('complaints.index')"
                                    class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors duration-150"
                                >
                                    Cancel
                                </a>
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    :class="[
                                        'px-6 py-2.5 text-sm font-medium text-white rounded-lg transition-all duration-150 inline-flex items-center gap-2',
                                        form.processing
                                            ? 'bg-indigo-400 cursor-not-allowed'
                                            : 'bg-indigo-600 hover:bg-indigo-700 hover:shadow-md'
                                    ]"
                                >
                                    <span v-if="form.processing" class="inline-flex items-center gap-2">
                                        <i class="fas fa-spinner fa-spin"></i>
                                        Submitting...
                                    </span>
                                    <span v-else class="inline-flex items-center gap-2">
                                        <i class="fas fa-paper-plane text-xs"></i>
                                        Submit Complaint
                                    </span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
                    <div class="flex gap-3">
                        <i class="fas fa-robot text-amber-600 mt-0.5"></i>
                        <div>
                            <p class="text-sm font-medium text-amber-800">AI Analysis</p>
                            <p class="text-sm text-amber-700 mt-0.5">
                                Upon submission, our AI will automatically analyze the complaint to determine priority,
                                generate a summary, and check for potential spam.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
