<template>
  <div class="min-h-screen flex bg-white font-sans overflow-hidden">
    <!-- Left Panel -->
    <div class="hidden lg:flex lg:w-1/2 bg-[#0F172A] text-white relative flex-col p-12 overflow-hidden">
      <!-- Logo -->
      <div class="flex items-center gap-x-3 mb-16">
        <div class="w-11 h-11 bg-white rounded-full flex items-center justify-center shadow-inner">
          <div class="w-8 h-8 bg-[#DC2626] rounded-full flex items-center justify-center relative">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.25" d="M19 21V5a2 2 0 01-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
          </div>
        </div>
        <span class="text-3xl font-semibold tracking-tight">CiviSense</span>
      </div>

      <!-- Welcome Text -->
      <div class="max-w-md">
        <h1 class="text-6xl font-bold leading-[1.05] tracking-tighter mb-4">
          Welcome to<br>
          <span class="text-[#DC2626]">CiviSense</span>
        </h1>
        <p class="text-xl text-slate-300 mt-3 leading-relaxed">
          Empowering citizens through<br>technology and transparency.
        </p>
      </div>

      <!-- Feature Cards -->
      <div class="grid grid-cols-2 gap-4 mt-12 max-w-md">
        <!-- Report Issues -->
        <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-2xl p-5 hover:bg-white/15 transition-all duration-200">
          <div class="w-10 h-10 bg-[#3B82F6] rounded-xl flex items-center justify-center mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.25" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314-11.314z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.25" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
          </div>
          <h3 class="font-semibold text-lg">Report Issues</h3>
          <p class="text-sm text-slate-300 mt-1">Notify issues in your area</p>
        </div>

        <!-- Track Requests -->
        <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-2xl p-5 hover:bg-white/15 transition-all duration-200">
          <div class="w-10 h-10 bg-[#DC2626] rounded-xl flex items-center justify-center mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.25" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2m2-2a2 2 0 012-2" />
            </svg>
          </div>
          <h3 class="font-semibold text-lg">Track Requests</h3>
          <p class="text-sm text-slate-300 mt-1">Track and view your requests</p>
        </div>

        <!-- Community Forum -->
        <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-2xl p-5 hover:bg-white/15 transition-all duration-200">
          <div class="w-10 h-10 bg-[#3B82F6] rounded-xl flex items-center justify-center mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.25" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" />
            </svg>
          </div>
          <h3 class="font-semibold text-lg">Community Forum</h3>
          <p class="text-sm text-slate-300 mt-1">Engage & connect with others</p>
        </div>

        <!-- Citizen Dashboard -->
        <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-2xl p-5 hover:bg-white/15 transition-all duration-200">
          <div class="w-10 h-10 bg-[#DC2626] rounded-xl flex items-center justify-center mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.25" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
          </div>
          <h3 class="font-semibold text-lg">Citizen Dashboard</h3>
          <p class="text-sm text-slate-300 mt-1">Manage your profile and activity</p>
        </div>
      </div>

      <!-- City Skyline -->
      <div class="absolute bottom-0 left-0 right-0 h-28 opacity-30">
        <svg viewBox="0 0 1200 120" preserveAspectRatio="none" class="w-full h-full">
          <path fill="#1E2937" d="M0,120 L0,70 L40,70 L40,45 L70,45 L70,70 L110,70 L110,30 L140,30 L140,70 L180,70 L180,55 L210,55 L210,70 L260,70 L260,25 L290,25 L290,70 L330,70 L330,40 L360,40 L360,70 L400,70 L400,15 L430,15 L430,70 L470,70 L470,50 L500,50 L500,70 L540,70 L540,35 L570,35 L570,70 L610,70 L610,20 L640,20 L640,70 L680,70 L680,45 L710,45 L710,70 L750,70 L750,30 L780,30 L780,70 L820,70 L820,55 L850,55 L850,70 L890,70 L890,25 L920,25 L920,70 L960,70 L960,40 L990,40 L990,70 L1030,70 L1030,15 L1060,15 L1060,70 L1100,70 L1100,50 L1130,50 L1130,70 L1170,70 L1170,35 L1200,35 L1200,120 Z"/>
        </svg>
      </div>
    </div>

    <!-- Right Panel - Login Form -->
    <div class="w-full lg:w-1/2 flex items-center justify-center p-8 bg-slate-50">
      <div class="w-full max-w-md">
        <!-- Login Card -->
        <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/70 p-10 border border-slate-100">

          <!-- Logo -->
          <div class="flex justify-center mb-8">
            <div class="flex items-center gap-x-3">
              <div class="w-10 h-10 bg-[#DC2626] rounded-full flex items-center justify-center shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.25" d="M19 21V5a2 2 0 01-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
              </div>
              <span class="text-3xl font-semibold tracking-tight text-slate-900">CiviSense</span>
            </div>
          </div>

          <!-- Header -->
          <div class="text-center mb-8">
            <h2 class="text-3xl font-semibold text-slate-900 tracking-tight">Welcome Back!</h2>
            <p class="text-slate-500 mt-2">Please login to your account</p>
          </div>

          <!-- Form -->
          <form @submit.prevent="handleLogin" class="space-y-5">

            <!-- Email -->
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1.5">Email Address</label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2.009 2.009 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                  </svg>
                </div>
                <input
                  v-model="form.email"
                  type="email"
                  required
                  placeholder="you@example.com"
                  class="w-full pl-11 pr-4 py-3.5 bg-white border border-slate-200 focus:border-slate-400 rounded-2xl text-sm placeholder:text-slate-400 focus:outline-none focus:ring-1 focus:ring-slate-200 transition-all"
                >
              </div>
            </div>

            <!-- Password -->
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1.5">Password</label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V9a4 4 0 00-8 0v2" />
                  </svg>
                </div>
                <input
                  v-model="form.password"
                  :type="showPassword ? 'text' : 'password'"
                  required
                  placeholder="••••••••"
                  class="w-full pl-11 pr-12 py-3.5 bg-white border border-slate-200 focus:border-slate-400 rounded-2xl text-sm placeholder:text-slate-400 focus:outline-none focus:ring-1 focus:ring-slate-200 transition-all"
                >
                <button
                  type="button"
                  @click="showPassword = !showPassword"
                  class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-slate-600 transition-colors"
                >
                  <svg v-if="!showPassword" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                  <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                  </svg>
                </button>
              </div>
            </div>

            <!-- Remember Me + Forgot Password -->
            <div class="flex items-center justify-between pt-1">
              <label class="flex items-center cursor-pointer">
                <input
                  v-model="form.rememberMe"
                  type="checkbox"
                  class="w-4 h-4 text-[#DC2626] border-slate-300 rounded focus:ring-[#DC2626]"
                >
                <span class="ml-2.5 text-sm text-slate-600">Remember me</span>
              </label>
              <a href="#" class="text-sm text-[#DC2626] hover:text-[#B91C1C] font-medium transition-colors">Forgot Password?</a>
            </div>

            <!-- Login Button -->
            <button
              type="submit"
              class="w-full mt-2 py-4 bg-[#0F172A] hover:bg-slate-900 active:bg-black transition-all text-white font-semibold rounded-2xl text-base shadow-lg shadow-slate-900/30 flex items-center justify-center gap-x-2"
            >
              Login
            </button>
          </form>

          <!-- Sign Up Link -->
          <div class="mt-7 text-center">
            <p class="text-sm text-slate-600">
              Don't have an account?
              <a href="#" class="font-semibold text-[#DC2626] hover:text-[#B91C1C] transition-colors">Sign up</a>
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'

const showPassword = ref(false)

const form = reactive({
  email: '',
  password: '',
  rememberMe: false
})

const handleLogin = () => {
  if (!form.email || !form.password) {
    alert('Please enter email and password')
    return
  }

  console.log('Login attempt:', {
    email: form.email,
    password: form.password,
    rememberMe: form.rememberMe
  })

  // Demo success
  alert(`Welcome back! Logged in as ${form.email}`)

  // TODO: Call your actual login API here
}
</script>

<style scoped>
input:focus {
  box-shadow: 0 0 0 3px rgba(15, 23, 42, 0.08);
}
</style>
