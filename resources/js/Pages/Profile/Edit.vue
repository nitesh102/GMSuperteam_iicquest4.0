<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';
import PageHeader from '@/Components/PageHeader.vue';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});
</script>

<template>
    <Head title="Profile" />

    <AdminLayout>
        <PageHeader
            title="Profile"
            description="Manage your account information, password, and preferences."
        />

        <div>
            <div class="max-w-7xl space-y-6">
                <div
                    class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-lg sm:p-8"
                >
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                        class="max-w-xl"
                    />
                </div>

                <div
                    class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-lg sm:p-8"
                >
                    <UpdatePasswordForm class="max-w-xl" />
                </div>

<!--                <div-->
<!--                    class="bg-white p-4 shadow sm:rounded-lg sm:p-8"-->
<!--                >-->
<!--                    <DeleteUserForm class="max-w-xl" />-->
<!--                </div>-->
            </div>
        </div>
    </AdminLayout>
</template>
