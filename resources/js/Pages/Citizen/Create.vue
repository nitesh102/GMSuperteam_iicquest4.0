<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useVoicePageHandlers } from '@/Composables/useVoiceContext';

const { t } = useI18n();
const page = usePage();

const categories = computed(() => page.props.categories ?? []);
const departments = computed(() => page.props.departments ?? []);

const selectedDepartment = computed(() => {
    if (!form.category_id) return null;
    const cat = categories.value.find(c => c.id === Number(form.category_id));
    if (!cat) return null;
    if (cat.department) return cat.department;
    return departments.value.find(d => d.id === cat.department_id) ?? null;
});

const form = useForm({
    title:       '',
    description: '',
    category_id: '',
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

const geolocating = ref(false);
const geoError    = ref(null);

function getCurrentLocation() {
    if (!navigator.geolocation) {
        geoError.value = 'Geolocation is not supported by your browser.';
        return;
    }
    geoError.value = null;
    geolocating.value = true;
    navigator.geolocation.getCurrentPosition(
        (pos) => {
            form.latitude  = pos.coords.latitude.toFixed(7);
            form.longitude = pos.coords.longitude.toFixed(7);
            geolocating.value = false;
        },
        (err) => {
            geolocating.value = false;
            switch (err.code) {
                case err.PERMISSION_DENIED:
                    geoError.value = 'Location permission denied. Please enable location access in your browser settings.';
                    break;
                case err.POSITION_UNAVAILABLE:
                    geoError.value = 'Location information is unavailable. Try again later.';
                    break;
                case err.TIMEOUT:
                    geoError.value = 'The request to get your location timed out. Please try again.';
                    break;
                default:
                    geoError.value = 'An unknown error occurred while getting your location.';
            }
        },
        { enableHighAccuracy: true, timeout: 10000, maximumAge: 60000 }
    );
}

function getGoogleMapsLink(lat, lng) {
    return `https://www.google.com/maps?q=${lat},${lng}`;
}

function submitForm() {
    const data = new FormData();
    data.append('title',       form.title);
    data.append('description', form.description);
    if (form.category_id) data.append('category_id', form.category_id);
    if (form.location)    data.append('location',    form.location);
    if (form.latitude)    data.append('latitude',    form.latitude);
    if (form.longitude)   data.append('longitude',   form.longitude);
    attachments.value.forEach(f => data.append('attachments[]', f));

    form.post(route('citizen.complaints.store'), {
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

useVoicePageHandlers({
    onFillField: handleVoiceFillField,
    onSubmitForm: submitForm,
}, 'complaint');

onMounted(() => {
    const pending = sessionStorage.getItem('voice_fill');
    if (!pending) return;
    try {
        const { field, value } = JSON.parse(pending);
        handleVoiceFillField(field, value);
    } finally {
        sessionStorage.removeItem('voice_fill');
    }
});
</script>

<template>
    <Head :title="t('complaint.submitTitle')" />

    <AdminLayout>
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

        <div class="min-h-[calc(100vh-64px)] flex flex-col -m-4 sm:-m-6">
            <div class="flex-1 w-full px-4 sm:px-6 lg:px-8 py-4 sm:py-6 space-y-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">{{ t('complaint.submitTitle') }}</h1>
                        <p class="mt-1 text-sm text-gray-500">{{ t('complaint.describeIssue') }}</p>
                    </div>
                    <a
                        :href="route('citizen.complaints.index')"
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50"
                    >
                        <i class="fas fa-arrow-left text-xs"></i>
                        {{ t('complaint.back') }}
                    </a>
                </div>

                <div class="bg-indigo-50 border border-indigo-100 rounded-xl px-4 py-3 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-robot text-indigo-600 text-sm"></i>
                    </div>
                    <p class="text-sm text-indigo-700">
                        <span class="font-semibold">{{ t('complaint.aiRouting') }}</span> &mdash; {{ t('complaint.aiRoutingDesc') }}
                    </p>
                </div>

                <div
                    v-if="form.errors.spam"
                    class="bg-red-50 border border-red-300 rounded-xl px-4 py-4 flex items-start gap-3"
                >
                    <div class="w-8 h-8 rounded-lg bg-red-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                        <i class="fas fa-shield-alt text-red-600 text-sm"></i>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-red-800">Spam Detected by CiviSense AI</p>
                        <p class="text-sm text-red-700 mt-0.5">{{ form.errors.spam }}</p>
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="p-5 sm:p-8">
                        <form @submit.prevent="submitForm" class="space-y-8">
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                <div class="lg:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                        {{ t('complaint.title') }} <span class="text-red-400">*</span>
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
                                            :placeholder="t('complaint.titlePlaceholder')"
                                            required
                                        />
                                    </div>
                                    <p v-if="form.errors.title" class="mt-1.5 text-sm text-red-600 flex items-center gap-1.5">
                                        <i class="fas fa-exclamation-circle text-xs"></i>
                                        <span>{{ form.errors.title }}</span>
                                    </p>
                                </div>

                                <div class="lg:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                        {{ t('complaint.description') }} <span class="text-red-400">*</span>
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
                                        placeholder="Describe the issue in detail &mdash; what, where, and when..."
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

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                        {{ t('complaint.category') }}
                                        <span class="ml-1 text-xs font-normal text-gray-400">({{ t('complaint.optionalAi') }})</span>
                                    </label>
                                    <select v-model="form.category_id" class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 bg-white">
                                        <option value="">{{ t('complaint.aiAutoRoute') }}</option>
                                        <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                                    </select>
                                    <div v-if="selectedDepartment" class="mt-2 flex items-center gap-2">
                                        <span class="text-xs text-gray-500">{{ t('complaint.routedTo') }}:</span>
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-3 py-1 text-xs font-medium text-blue-700">
                                            <i class="fas fa-building text-[10px]"></i>
                                            {{ selectedDepartment.name }}
                                        </span>
                                    </div>
                                </div>

                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <h4 class="text-sm font-medium text-gray-700">
                                            Photos
                                            <span class="ml-1 text-xs font-normal text-gray-400">({{ t('complaint.optionalAi') }})</span>
                                        </h4>
                                        <span v-if="attachments.length" class="text-xs text-gray-400">{{ attachments.length }}/5</span>
                                    </div>
                                    <div
                                        v-if="attachments.length < 5"
                                        @click="fileInput?.click()"
                                        class="flex flex-col items-center justify-center w-full h-28 border-2 border-dashed border-gray-200 rounded-xl bg-gray-50/50 hover:bg-indigo-50/40 hover:border-indigo-300 cursor-pointer transition-all"
                                    >
                                        <i class="fas fa-cloud-upload-alt text-gray-300 text-xl mb-1"></i>
                                        <p class="text-sm text-gray-500">Click to upload</p>
                                        <p class="text-xs text-gray-400 mt-0.5">JPEG, PNG, WebP</p>
                                        <input
                                            ref="fileInput"
                                            type="file"
                                            multiple
                                            accept="image/jpeg,image/png,image/webp,image/gif"
                                            class="hidden"
                                            @change="handleFileInput"
                                        />
                                    </div>
                                    <div v-if="attachments.length" class="grid grid-cols-3 gap-2 mt-2">
                                        <div v-for="(_, idx) in attachments" :key="idx" class="relative group aspect-square rounded-lg overflow-hidden border border-gray-200 bg-gray-50">
                                            <img :src="previews[idx]" class="w-full h-full object-cover" />
                                            <button type="button" @click="removeFile(idx)" class="absolute top-1 right-1 w-5 h-5 rounded-full bg-red-500/80 text-white flex items-center justify-center opacity-0 group-hover:opacity-100 hover:bg-red-600 transition-opacity">
                                                <i class="fas fa-times text-xs"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="border-t border-gray-100 pt-8">
                                <div class="flex items-center justify-between mb-4">
                                    <h4 class="text-sm font-semibold text-gray-900">
                                        Location
                                        <span class="ml-1 text-xs font-normal text-gray-400">(optional)</span>
                                    </h4>
                                    <button
                                        type="button"
                                        @click="getCurrentLocation"
                                        :disabled="geolocating"
                                        class="inline-flex items-center gap-2 px-3.5 py-2 text-xs font-medium rounded-lg border transition-all duration-150"
                                        :class="geolocating
                                            ? 'bg-gray-100 border-gray-200 text-gray-400 cursor-not-allowed'
                                            : 'bg-white border-indigo-200 text-indigo-600 hover:bg-indigo-50 hover:border-indigo-300'"
                                    >
                                        <i v-if="geolocating" class="fas fa-spinner fa-spin text-xs"></i>
                                        <svg v-else class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        {{ geolocating ? 'Detecting...' : 'Use Current Location' }}
                                    </button>
                                </div>
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Address</label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <i class="fas fa-map-marker-alt text-gray-400 text-sm"></i>
                                            </div>
                                            <input type="text" v-model="form.location" class="w-full pl-9 pr-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 placeholder:text-gray-400" placeholder="e.g. 123 Main Street, Downtown" />
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Latitude</label>
                                            <input type="number" step="any" v-model="form.latitude" class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 placeholder:text-gray-400" placeholder="-90 to 90" />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Longitude</label>
                                            <input type="number" step="any" v-model="form.longitude" class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 placeholder:text-gray-400" placeholder="-180 to 180" />
                                        </div>
                                    </div>
                                </div>
                                <div v-if="form.latitude && form.longitude" class="mt-3 flex items-center gap-2 p-3 bg-green-50 border border-green-200 rounded-lg">
                                    <i class="fas fa-check-circle text-green-500 text-xs"></i>
                                    <span class="text-xs text-green-700">Location captured: {{ form.latitude }}, {{ form.longitude }}</span>
                                    <a :href="getGoogleMapsLink(form.latitude, form.longitude)" target="_blank" class="ml-auto inline-flex items-center gap-1 text-xs font-medium text-green-600 hover:text-green-700">
                                        <i class="fas fa-external-link-alt text-[10px]"></i>
                                        View on Maps
                                    </a>
                                </div>
                                <p v-if="geoError" class="mt-3 text-xs text-red-600 flex items-center gap-1.5 bg-red-50 border border-red-200 rounded-lg px-3 py-2">
                                    <i class="fas fa-exclamation-circle text-xs flex-shrink-0"></i>
                                    {{ geoError }}
                                </p>
                            </div>

                            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                                <a :href="route('citizen.complaints.index')" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
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
    </AdminLayout>
</template>

<style scoped>
@keyframes slide-in {
    from { transform: translateX(100%); opacity: 0; }
    to   { transform: translateX(0);    opacity: 1; }
}
.animate-slide-in { animation: slide-in 0.3s ease-out; }
</style>
