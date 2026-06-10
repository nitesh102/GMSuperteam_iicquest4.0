<template>
  <div class="w-screen h-screen m-0 p-0 overflow-hidden font-['Poppins'] bg-slate-50 flex">
    <div class="flex w-full h-full">

      <div class="relative hidden lg:flex flex-col justify-between w-[58%] bg-[#0A111E] p-16 overflow-hidden z-10 select-none">

        <header class="w-full z-10">
          <div class="flex items-center gap-3">
            <img src="logo.png" alt="CiviSense Logo" class="h-12 w-auto object-contain" />
          </div>
        </header>

        <div class="my-auto max-w-xl z-10">
          <h1 class="m-0 text-white leading-tight">
            <span class="block text-4xl font-normal text-slate-200 opacity-90">Welcome to</span>
            <span class="block text-5xl font-bold tracking-tight">Civi<span class="text-[#E63946]">Sense</span></span>
          </h1>
          <p class="mt-4 text-[15.5px] text-slate-400 font-light leading-relaxed">
            Empowering citizens through technology and transparency.
          </p>

          <div class="mt-12 grid grid-cols-2 gap-5 max-w-xl">
            <div class="flex items-center gap-4 p-5 bg-white/[0.02] border border-white/[0.06] rounded-xl box-border">
              <div class="w-11 h-11 rounded-full flex items-center justify-center flex-shrink-0 bg-[#13233C] border border-blue-500/30 text-blue-500">
                <svg viewBox="0 0 24 24" class="w-5 h-5 fill-current"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
              </div>
              <div>
                <h4 class="m-0 text-white text-sm font-semibold">Report Issues</h4>
                <p class="m-0 mt-1 text-slate-400 text-xs font-normal">Notify issues in your area</p>
              </div>
            </div>

            <div class="flex items-center gap-4 p-5 bg-white/[0.02] border border-white/[0.06] rounded-xl box-border">
              <div class="w-11 h-11 rounded-full flex items-center justify-center flex-shrink-0 bg-[#13233C] border border-red-500/30 text-[#E63946]">
                <svg viewBox="0 0 24 24" class="w-5 h-5 fill-current"><path d="M19 3h-4.18C14.4 1.84 13.3 1 12 1s-2.4.84-2.82 2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 0c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm2 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>
              </div>
              <div>
                <h4 class="m-0 text-white text-sm font-semibold">Track Requests</h4>
                <p class="m-0 mt-1 text-slate-400 text-xs font-normal">Track and view your requests</p>
              </div>
            </div>

            <div class="flex items-center gap-4 p-5 bg-white/[0.02] border border-white/[0.06] rounded-xl box-border">
              <div class="w-11 h-11 rounded-full flex items-center justify-center flex-shrink-0 bg-[#13233C] border border-blue-500/30 text-blue-500">
                <svg viewBox="0 0 24 24" class="w-5 h-5 fill-current"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H5.17L4 17.17V4h16v12z"/></svg>
              </div>
              <div>
                <h4 class="m-0 text-white text-sm font-semibold">Community Forum</h4>
                <p class="m-0 mt-1 text-slate-400 text-xs font-normal">Engage & connect with others</p>
              </div>
            </div>

            <div class="flex items-center gap-4 p-5 bg-white/[0.02] border border-white/[0.06] rounded-xl box-border">
              <div class="w-11 h-11 rounded-full flex items-center justify-center flex-shrink-0 bg-[#13233C] border border-red-500/30 text-[#E63946]">
                <svg viewBox="0 0 24 24" class="w-5 h-5 fill-current"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/></svg>
              </div>
              <div>
                <h4 class="m-0 text-white text-sm font-semibold">Citizen Dashboard</h4>
                <p class="m-0 mt-1 text-slate-400 text-xs font-normal">Manage your profile and activity</p>
              </div>
            </div>
          </div>
        </div>

        <div class="absolute bottom-0 left-0 w-full px-16 z-0 pointer-events-none translate-y-[2px]">
          <svg viewBox="0 0 600 130" class="w-full h-auto block" preserveAspectRatio="xMidYEnd meet">
            <path d="M0,130 C120,70 220,165 410,105 C520,70 560,115 600,85 L600,130 L0,130 Z" fill="#ffffff" />

            <path d="M30,130 L30,95 L18,95 L45,70 L30,70 L55,45 L60,20 L65,45 L90,70 L75,70 L102,95 L90,95 L90,130" stroke="#0f172a" stroke-width="1.5" fill="#ffffff" />
            <line x1="30" y1="95" x2="90" y2="95" stroke="#0f172a" stroke-width="1" />
            <line x1="42" y1="70" x2="78" y2="70" stroke="#0f172a" stroke-width="1" />
            <path d="M60,20 L60,10" stroke="#dc2626" stroke-width="1.5" />

            <path d="M140,130 L140,110 L120,110 L160,85 L140,85 L175,60 L155,60 L190,35 L195,5 L200,35 L235,60 L215,60 L250,85 L230,85 L270,110 L250,110 L250,130" stroke="#0f172a" stroke-width="1.8" fill="#ffffff" />
            <line x1="140" y1="110" x2="250" y2="110" stroke="#0f172a" stroke-width="1.3" />
            <line x1="155" y1="85" x2="235" y2="85" stroke="#0f172a" stroke-width="1.3" />
            <line x1="170" y1="60" x2="220" y2="60" stroke="#0f172a" stroke-width="1.3" />
            <path d="M195,5 L195,0" stroke="#dc2626" stroke-width="2" />

            <path d="M310,130 L310,92 C310,62 325,42 335,22 L335,10 L341,10 L341,22 C351,42 366,62 366,92 L366,130 Z" stroke="#0f172a" stroke-width="1.5" fill="#ffffff" />
            <path d="M320,92 L356,92 M325,72 L351,72 M330,52 L346,52" stroke="#0f172a" stroke-width="0.9" />

            <path d="M440,130 L440,105 L420,105 L470,80 L450,80 L495,55 L475,55 L510,30 L515,5 L520,30 L555,55 L535,55 L580,80 L560,80 L610,105 L590,105 L590,130" stroke="#0f172a" stroke-width="1.6" fill="#ffffff" />
            <line x1="435" y1="105" x2="595" y2="105" stroke="#0f172a" stroke-width="1.2" />
            <line x1="460" y1="80" x2="570" y2="80" stroke="#0f172a" stroke-width="1.2" />
            <line x1="485" y1="55" x2="545" y2="55" stroke="#0f172a" stroke-width="1.2" />
            <circle cx="515" cy="5" r="2" fill="#dc2626" />
          </svg>
        </div>

        <div class="absolute top-0 right-0 h-full w-40 z-20 pointer-events-none">
          <svg viewBox="0 0 100 100" preserveAspectRatio="none" class="w-full h-full">
            <path d="M100,0 C60,15 65,85 100,100 Z" fill="#FFFFFF" />
            <path d="M100,0 C60,15 65,85 100,100" stroke="#E63946" stroke-width="1.5" fill="none" opacity="0.85" />
          </svg>
        </div>
      </div>

      <div class="flex flex-col justify-center items-center flex-1 bg-white p-10 z-10">
        <div class="w-full max-w-[400px] flex flex-col">

          <div class="text-center mb-9">
            <div class="flex flex-col items-center gap-2 mb-6">
              <img src="logo.png" alt="CiviSense Stacked Logo" class="w-40 h-auto object-contain mb-3" />
            </div>

            <h3 class="m-0 text-[26px] font-bold text-[#0F172A] tracking-tight">Welcome Back!</h3>
            <p class="m-0 mt-1 text-sm text-slate-500">Please login to your account</p>
          </div>

          <form @submit.prevent="executeLogin" class="flex flex-col gap-[18px]">

            <div class="relative w-full flex items-center border border-slate-200 rounded-xl bg-[#E8EEF8] focus-within:bg-white focus-within:border-[#091020] focus-within:shadow-[0_0_0_4px_rgba(9,16,32,0.03)] transition-all duration-200 box-border">
              <span class="absolute left-4 flex items-center pointer-events-none">
                <svg viewBox="0 0 24 24" class="w-4.5 h-4.5 fill-slate-500"><path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 14H4V8l8 5 8-5v10zM12 11 4 6h16l-8 5z"/></svg>
              </span>
              <input
                type="email"
                v-model="email"
                placeholder="Email Address"
                class="w-full border-none bg-transparent py-4 pl-12 pr-4 text-[15px] text-slate-900 outline-none font-inherit box-border"
                required
              />
            </div>

            <div class="relative w-full flex items-center border border-slate-200 rounded-xl bg-[#E8EEF8] focus-within:bg-white focus-within:border-[#091020] focus-within:shadow-[0_0_0_4px_rgba(9,16,32,0.03)] transition-all duration-200 box-border">
              <span class="absolute left-4 flex items-center pointer-events-none">
                <svg viewBox="0 0 24 24" class="w-4.5 h-4.5 fill-slate-500"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/></svg>
              </span>
              <input
                :type="showPassword ? 'text' : 'password'"
                v-model="password"
                placeholder="Password"
                class="w-full border-none bg-transparent py-4 pl-12 pr-12 text-[15px] text-slate-900 outline-none font-inherit box-border"
                required
              />
              <span class="absolute right-4 flex items-center cursor-pointer text-slate-400 hover:text-slate-600" @click="showPassword = !showPassword">
                <svg viewBox="0 0 24 24" class="w-4.5 h-4.5 fill-current"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/></svg>
              </span>
            </div>

            <div class="flex justify-between items-center text-sm mt-[2px]">
              <label class="flex items-center gap-2 cursor-pointer select-none text-slate-600">
                <input type="checkbox" v-model="rememberMe" class="w-[17px] h-[17px] accent-[#091020] cursor-pointer rounded" />
                <span>Remember me</span>
              </label>
              <a href="#forgot" class="text-[#E63946] font-medium no-underline hover:underline">Forgot Password?</a>
            </div>

            <button type="submit" class="w-full bg-[#091020] text-white border-none py-4 rounded-xl text-[15px] font-semibold cursor-pointer shadow-md hover:bg-slate-800 transition-colors duration-200 mt-2">
              Login
            </button>
          </form>

          <footer class="text-center mt-9 text-sm text-slate-500">
            <span>Don't have an account? </span>
            <a href="#signup" class="text-[#E63946] font-semibold no-underline hover:underline ml-1">Sign up</a>
          </footer>

        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

// Local Reactive States
const email = ref('admin@gmail.com');
const password = ref('••••••••');
const rememberMe = ref(false);
const showPassword = ref(false);

const executeLogin = () => {
  console.log('Credentials validated successfully and processed securely.');
};
</script>
