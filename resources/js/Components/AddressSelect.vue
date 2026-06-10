<script setup>
import { computed, watch, onMounted } from "vue";

const props = defineProps({
    provinces: Array,
    districts: Array,
    municipals: Array,
    form: Object,
});

// ✅ Ensure default values
onMounted(() => {
    if (!props.form.province_id) props.form.province_id = "";
    if (!props.form.district_id) props.form.district_id = "";
    if (!props.form.municipal_id) props.form.municipal_id = "";
});

const filteredDistricts = computed(() =>
    props.districts.filter((d) => d.province_id == props.form.province_id),
);

const filteredMunicipals = computed(() =>
    props.municipals.filter((m) => m.district_id == props.form.district_id),
);

watch(
    () => props.form.province_id,
    () => {
        props.form.district_id = "";
        props.form.municipal_id = "";
    },
);

watch(
    () => props.form.district_id,
    () => {
        props.form.municipal_id = "";
    },
);
</script>

<template>
    <div>
        <h3 class="section-title">Location Details</h3>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Province -->
            <div>
                <label class="label">Province</label>
                <div class="relative">
                    <select
                        v-model="form.province_id"
                        class="select appearance-none pr-10"
                        :key="provinces.length"
                    >
                        <option disabled value="">
                            {{
                                provinces.length
                                    ? "Select Province"
                                    : "Loading provinces..."
                            }}
                        </option>

                        <option
                            v-for="p in provinces"
                            :key="p.id"
                            :value="p.id"
                        >
                            {{ p.province_name }}
                        </option>
                    </select>

                    <!-- Font Awesome Arrow -->
                    <div
                        class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-gray-500"
                    >
                        <i class="fas fa-chevron-down text-xs"></i>
                    </div>
                </div>
            </div>

            <!-- District -->
            <div>
                <label class="label">District</label>
                <div class="relative">
                    <select
                        v-model="form.district_id"
                        class="select appearance-none pr-10"
                        :disabled="!form.province_id"
                        :key="filteredDistricts.length"
                    >
                        <option disabled value="">
                            {{
                                filteredDistricts.length
                                    ? "Select District"
                                    : "Select Province first"
                            }}
                        </option>

                        <option
                            v-for="d in filteredDistricts"
                            :key="d.id"
                            :value="d.id"
                        >
                            {{ d.district_name }}
                        </option>
                    </select>

                    <!-- Font Awesome Arrow -->
                    <div
                        class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-gray-500"
                    >
                        <i class="fas fa-chevron-down text-xs"></i>
                    </div>
                </div>
            </div>

            <!-- Municipality -->
            <div>
                <label class="label">Municipality</label>
                <div class="relative">
                    <select
                        v-model="form.municipal_id"
                        class="select appearance-none pr-10"
                        :disabled="!form.district_id"
                        :key="filteredMunicipals.length"
                    >
                        <option disabled value="">
                            {{
                                filteredMunicipals.length
                                    ? "Select Municipality"
                                    : "Select District first"
                            }}
                        </option>

                        <option
                            v-for="m in filteredMunicipals"
                            :key="m.id"
                            :value="m.id"
                        >
                            {{ m.municipal_name }}
                        </option>
                    </select>

                    <!-- Font Awesome Arrow -->
                    <div
                        class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-gray-500"
                    >
                        <i class="fas fa-chevron-down text-xs"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.label {
    font-size: 0.875rem;
    font-weight: 500;
    color: #4b5563;
    display: block;
    margin-bottom: 4px;
}

.select {
    width: 100%;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
    background: #ffffff;
    outline: none;
    transition: all 0.2s ease;
}

.select:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
}

.select:disabled {
    background: #f3f4f6;
    cursor: not-allowed;
}

.section-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.75rem;
}
</style>
