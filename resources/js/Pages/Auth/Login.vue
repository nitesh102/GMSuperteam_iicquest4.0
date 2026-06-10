<script setup>
import InputError from '@/Components/InputError.vue'
import InputLabel from '@/Components/InputLabel.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import TextInput from '@/Components/TextInput.vue'
import Checkbox from '@/Components/Checkbox.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

defineProps({
    canResetPassword: Boolean,
    status: String,
})

const form = useForm({
    email: '',
    password: '',
    remember: false,
})

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    })
}
</script>

<template>
    <Head title="Login - CiviSense" />

    <div class="min-h-screen flex">

        <!-- LEFT: Brand Panel -->
        <div class="hidden lg:flex lg:w-1/2 bg-[#064789] relative overflow-hidden">
            <div class="absolute inset-0 opacity-[0.07]">
                <div class="absolute top-10 left-10 w-72 h-72 bg-white rounded-full blur-3xl"></div>
                <div class="absolute bottom-10 right-10 w-96 h-96 bg-white rounded-full blur-3xl"></div>
            </div>

            <div class="relative z-10 flex flex-col justify-center px-16 text-white">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-12 h-12 bg-white text-[#064789] rounded-2xl flex items-center justify-center text-2xl font-bold shadow-lg">
                        C
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold">CiviSense</h1>
                        <p class="text-[#ebf2fa] text-sm">Smart Civic. Stronger Communities.</p>
                    </div>
                </div>

                <h2 class="text-4xl font-extrabold leading-tight">
                    Welcome to<br />
                    Civic Management
                </h2>

                <p class="mt-4 text-[#ebf2fa] text-lg max-w-md">
                    Report issues, track progress, and build better communities — all in one platform.
                </p>

                <div class="mt-10 space-y-5">
                    <div v-for="item in [
                        { icon: 'fas fa-robot', text: 'AI-powered complaint categorization' },
                        { icon: 'fas fa-map-marker-alt', text: 'GPS location tagging' },
                        { icon: 'fas fa-chart-line', text: 'Real-time progress tracking' },
                        { icon: 'fas fa-shield-alt', text: 'Secure & transparent platform' },
                    ]" :key="item.text" class="flex items-center gap-4">
                        <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                            <i :class="item.icon" class="text-lg"></i>
                        </div>
                        <span class="text-[#ebf2fa]">{{ item.text }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT: Form Panel -->
        <div class="flex-1 flex items-center justify-center px-6 py-12 bg-[#ebf2fa]">
            <div class="w-full max-w-md">

                <!-- Mobile Logo -->
                <div class="lg:hidden text-center mb-8">
                    <div class="w-14 h-14 mx-auto bg-[#064789] text-white rounded-2xl flex items-center justify-center text-2xl font-bold">
                        C
                    </div>
                    <h1 class="text-2xl font-bold text-[#064789] mt-3">CiviSense</h1>
                    <p class="text-[#8d99ae] text-sm">Smart Civic. Stronger Communities.</p>
                </div>

                <!-- Header -->
                <div class="mb-8">
                    <h2 class="text-3xl font-extrabold text-[#064789]">Welcome Back</h2>
                    <p class="text-[#8d99ae] mt-1">Sign in to your account</p>
                </div>

                <!-- Status -->
                <div v-if="status" class="mb-4 text-sm font-medium text-[#064789] bg-white border border-[#8d99ae] rounded-lg px-4 py-3">
                    {{ status }}
                </div>

                <!-- Form -->
                <form @submit.prevent="submit" class="space-y-5">

                    <div>
                        <InputLabel for="email" value="Email" />
                        <TextInput
                            id="email"
                            type="email"
                            class="mt-1 block w-full rounded-xl border-[#8d99ae] focus:border-[#064789] focus:ring-[#064789]"
                            v-model="form.email"
                            required
                            autofocus
                            autocomplete="username"
                            placeholder="you@example.com"
                        />
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <div>
                        <InputLabel for="password" value="Password" />
                        <TextInput
                            id="password"
                            type="password"
                            class="mt-1 block w-full rounded-xl border-[#8d99ae] focus:border-[#064789] focus:ring-[#064789]"
                            v-model="form.password"
                            required
                            autocomplete="current-password"
                            placeholder="••••••••"
                        />
                        <InputError class="mt-2" :message="form.errors.password" />
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2">
                            <Checkbox name="remember" v-model:checked="form.remember" />
                            <span class="text-sm text-[#8d99ae]">Remember me</span>
                        </label>

                        <Link
                            v-if="canResetPassword"
                            :href="route('password.request')"
                            class="text-sm text-[#064789] hover:opacity-80 font-medium"
                        >
                            Forgot password?
                        </Link>
                    </div>

                    <PrimaryButton
                        class="w-full bg-[#064789] hover:bg-[#064789]/90 justify-center py-3 rounded-xl text-white font-semibold"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                    >
                        <i class="fas fa-spinner fa-spin mr-2" v-if="form.processing"></i>
                        {{ form.processing ? 'Signing in...' : 'Sign in' }}
                    </PrimaryButton>

                </form>

                <p class="text-center text-sm text-[#8d99ae] mt-8">
                    Don't have an account?
                    <Link href="/register" class="text-[#064789] font-semibold hover:opacity-80 hover:underline">
                        Create one
                    </Link>
                </p>

            </div>
        </div>

    </div>
</template>
