<script setup>
import { ref, watch } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const form = useForm({
    title:       '',
    description: '',
    location:    '',
    latitude:    '',
    longitude:   '',
});

const attachments = ref([]);
const fileInput    = ref(null);
const previews     = ref([]);

function handleFileInput(e) {
    const files     = Array.from(e.target.files || []);
    const remaining = 5 - attachments.value.length;
    const allowed   = files.slice(0, remaining);
    attachments.value = [...attachments.value, ...allowed];
    previews.value    = attachments.value.map(f => URL.createObjectURL(f));
    if (files.length > remaining) alert('Maximum 5 images allowed.');
}

function removeFile(index) {
    attachments.value.splice(index, 1);
    previews.value.splice(index, 1);
}

const flashMessage = ref(null);
let flashTimer = null;
watch(() => usePage().props.flash?.success, (val) => {
    if (flashTimer) clearTimeout(flashTimer);
    flashMessage.value = val || null;
    if (flashMessage.value) flashTimer = setTimeout(() => flashMessage.value = null, 4000);
}, { immediate: true });

function submitForm() {
    const data = new FormData();
    data.append('title',       form.title);
    data.append('description', form.description);
    if (form.location)  data.append('location',  form.location);
    if (form.latitude)  data.append('latitude',  form.latitude);
    if (form.longitude) data.append('longitude', form.longitude);
    attachments.value.forEach(f => data.append('attachments[]', f));

    form.post(route('complaints.store'), {
        data,
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            attachments.value = [];
            previews.value    = [];
        },
    });
}

function handleVoiceFillField(field, value) {
    if (field in form) {
        form[field] = value;
    }
}

provide('voiceFormHandlers', {
    onFillField: handleVoiceFillField,
    onSubmitForm: submitForm,
});
</script>

<template>
    <Head :title="t('complaint.submitTitle')" />

    <AuthenticatedLayout>
        <!-- Flash toast -->
        <teleport to="body">
            <div v-if="flashMessage" class="fixed top-5 right-5 z-[100] animate-slide-in" @click="flashMessage = null">
                <div class="bg-white border border-green-200 rounded-xl shadow-lg px-5 py-3.5 flex items-center gap-3 cursor-pointer">
                    <div class="w-8 h-8 rounded-full bg-green-50 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-check-circle text-green-500 text-sm"></i>
                    </div>
                    <p class="text-sm font-medium text-gray-800">{{ flashMessage }}</p>
                </div>
            </div>
        </teleport>

        <div class="py-6">
            <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

                <!-- Header -->
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">{{ t('complaint.submitTitle') }}</h1>
                        <p class="mt-1 text-sm text-gray-500">
                            Describe the issue — our AI will handle category and priority automatically.
                        </p>
                    </div>
                    <a
                        :href="route('complaints.index')"
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors duration-150"
                    >
                        <i class="fas fa-arrow-left text-xs"></i>
                        {{ t('complaint.back') }}
                    </a>
                </div>

                <!-- AI badge -->
                <div class="bg-indigo-50 border border-indigo-100 rounded-xl px-4 py-3 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-robot text-indigo-600 text-sm"></i>
                    </div>
                    <p class="text-sm text-indigo-700">
                        <span class="font-semibold">AI-powered routing</span> — department, category, and priority are detected automatically from your submission.
                    </p>
                </div>

                <!-- Form Card -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                    <div class="px-6 py-6 sm:px-8">
                        <form @submit.prevent="submitForm" class="space-y-6">

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                    {{ t('complaint.title') }} <span class="text-red-400">*</span>
                                </label>
                                <div class="relative flex gap-2">
                                    <div class="relative flex-1">
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
                                            :placeholder="t('complaint.titlePlaceholder')"
                                            required
                                        />
                                    </div>
                                    <VoiceInputButton v-model="form.title" :append="false" />
                                </div>
                                <p v-if="form.errors.title" class="mt-1.5 text-sm text-red-600 flex items-center gap-1.5">
                                    <i class="fas fa-exclamation-circle text-xs"></i>
                                    <span>{{ form.errors.title }}</span>
                                </p>
                            </div>

                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label class="block text-sm font-medium text-gray-700">
                                        {{ t('complaint.description') }} <span class="text-red-400">*</span>
                                    </label>
                                    <VoiceInputButton v-model="form.description" />
                                </div>
                                <textarea
                                    v-model="form.description"
                                    rows="6"
                                    :class="[
                                        'w-full px-3 py-2.5 text-sm border rounded-lg focus:outline-none focus:ring-2 transition-all duration-150 resize-none placeholder:text-gray-400',
                                        form.errors.description
                                            ? 'border-red-300 bg-red-50/50 focus:ring-red-500/20 focus:border-red-400'
                                            : 'border-gray-200 bg-white focus:ring-indigo-500/20 focus:border-indigo-500'
                                    ]"
                                    placeholder="Describe the issue in detail — what, where, and when..."
                                    maxlength="10000"
                                ></textarea>
                                <div class="flex justify-between mt-1.5">
                                    <p v-if="form.errors.description" class="text-sm text-red-600 flex items-center gap-1.5">
                                        <i class="fas fa-exclamation-circle text-xs"></i>
                                        <span>{{ form.errors.description }}</span>
                                    </p>
                                    <p v-else></p>
                                    <p class="text-xs text-gray-400">{{ form.description.length }} / 10,000</p>
                                </div>
                            </div>

                            <div class="border-t border-gray-100 pt-6">
                                <div class="flex items-center justify-between mb-3">
                                    <div>
                                        <h4 class="text-sm font-semibold text-gray-900">Photos
                                            <span class="ml-1 text-xs font-normal text-gray-400">(optional, up to 5)</span>
                                        </h4>
                                        <p class="text-xs text-gray-500 mt-0.5">Photos help AI better understand the issue.</p>
                                    </div>
                                    <span v-if="attachments.length" class="text-xs text-gray-400">{{ attachments.length }}/5</span>
                                </div>
                                <p v-if="form.errors.attachments" class="mb-3 text-sm text-red-600 flex items-center gap-1.5 bg-red-50 border border-red-200 rounded-lg px-3 py-2">
                                    <i class="fas fa-ban text-xs flex-shrink-0"></i>
                                    <span>{{ form.errors.attachments }}</span>
                                </p>

                                <div
                                    v-if="attachments.length < 5"
                                    @click="fileInput?.click()"
                                    class="flex flex-col items-center justify-center w-full h-28 border-2 border-dashed border-gray-200 rounded-xl bg-gray-50/50 hover:bg-indigo-50/40 hover:border-indigo-300 cursor-pointer transition-all"
                                >
                                    <i class="fas fa-cloud-upload-alt text-gray-300 text-2xl mb-1.5"></i>
                                    <p class="text-sm text-gray-500">Click to upload</p>
                                    <p class="text-xs text-gray-400 mt-0.5">JPEG, PNG, WebP — max 5 MB each</p>
                                    <input
                                        ref="fileInput"
                                        type="file"
                                        multiple
                                        accept="image/jpeg,image/png,image/webp,image/gif"
                                        class="hidden"
                                        @change="handleFileInput"
                                    />
                                </div>

                                <div v-if="attachments.length" class="grid grid-cols-3 sm:grid-cols-5 gap-3 mt-3">
                                    <div
                                        v-for="(_, idx) in attachments"
                                        :key="idx"
                                        class="relative group aspect-square rounded-lg overflow-hidden border border-gray-200 bg-gray-50"
                                    >
                                        <img :src="previews[idx]" class="w-full h-full object-cover" />
                                        <button
                                            type="button"
                                            @click="removeFile(idx)"
                                            class="absolute top-1 right-1 w-6 h-6 rounded-full bg-red-500/80 text-white flex items-center justify-center opacity-0 group-hover:opacity-100 hover:bg-red-600 transition-opacity"
                                        >
                                            <i class="fas fa-times text-xs"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="border-t border-gray-100 pt-6">
                                <h4 class="text-sm font-semibold text-gray-900 mb-4">
                                    Location
                                    <span class="ml-1 text-xs font-normal text-gray-400">(optional)</span>
                                </h4>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Address</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fas fa-map-marker-alt text-gray-400 text-sm"></i>
                                        </div>
                                        <input
                                            type="text"
                                            v-model="form.location"
                                            class="w-full pl-9 pr-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 placeholder:text-gray-400"
                                            placeholder="e.g. 123 Main Street, Downtown"
                                        />
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-4 mt-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Latitude</label>
                                        <input
                                            type="number" step="any" v-model="form.latitude"
                                            class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 placeholder:text-gray-400"
                                            placeholder="-90 to 90"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Longitude</label>
                                        <input
                                            type="number" step="any" v-model="form.longitude"
                                            class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 placeholder:text-gray-400"
                                            placeholder="-180 to 180"
                                        />
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                                <a
                                    :href="route('complaints.index')"
                                    class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
                                >
                                    Cancel
                                </a>
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg disabled:bg-indigo-400 disabled:cursor-not-allowed transition-colors"
                                >
                                    <i v-if="form.processing" class="fas fa-spinner fa-spin text-xs"></i>
                                    <i v-else class="fas fa-paper-plane text-xs"></i>
                                    {{ form.processing ? 'Submitting...' : 'Submit Complaint' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
@keyframes slide-in {
    from { transform: translateX(100%); opacity: 0; }
    to   { transform: translateX(0);    opacity: 1; }
}
.animate-slide-in { animation: slide-in 0.3s ease-out; }
</style>
