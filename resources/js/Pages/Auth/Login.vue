<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import { route } from 'ziggy-js'

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const showPassword = ref(false);
const currentTime = ref('');
const currentDate = ref('');

const submit = () => {
    form.post(route('login.store'));
};

const togglePassword = () => {
    showPassword.value = !showPassword.value;
};

const updateTime = () => {
    const now = new Date();
    currentTime.value = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
    currentDate.value = now.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
};

onMounted(() => {
    // Check dark mode preference
    if (localStorage.getItem('darkMode') === 'true' || 
        (!localStorage.getItem('darkMode') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.documentElement.classList.add('dark');
    }
    
    updateTime();
    setInterval(updateTime, 1000);
});
</script>

<template>
    <div class="min-h-screen flex bg-gray-50 dark:bg-gray-950">
        <Head title="Login - SAMOSIR" />

        <!-- Left Panel - Branding / Visual -->
        <div class="hidden lg:flex lg:w-[55%] relative overflow-hidden">
            <!-- Gradient Background -->
            <div class="absolute inset-0 bg-gradient-to-br from-blue-700 via-blue-800 to-indigo-900"></div>
            
            <!-- Animated Background Pattern -->
            <div class="absolute inset-0 opacity-10">
                <div class="absolute top-0 left-0 w-full h-full" style="background-image: url('data:image/svg+xml,%3Csvg width=&quot;60&quot; height=&quot;60&quot; viewBox=&quot;0 0 60 60&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot;%3E%3Cg fill=&quot;none&quot; fill-rule=&quot;evenodd&quot;%3E%3Cg fill=&quot;%23ffffff&quot; fill-opacity=&quot;0.4&quot;%3E%3Cpath d=&quot;M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z&quot;/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
            </div>
            
            <!-- Decorative Circles -->
            <div class="absolute -top-20 -left-20 w-72 h-72 bg-blue-500/20 rounded-full blur-3xl animate-pulse"></div>
            <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-indigo-500/20 rounded-full blur-3xl animate-pulse" style="animation-delay: 1s;"></div>
            <div class="absolute top-1/2 left-1/4 w-48 h-48 bg-cyan-400/10 rounded-full blur-2xl animate-pulse" style="animation-delay: 2s;"></div>

            <!-- Content -->
            <div class="relative z-10 flex flex-col justify-between w-full p-12">
                <!-- Top: Logo & Branding -->
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center border border-white/10">
                        <img :src="'/img/logobaru.png'" alt="SAMOSIR Logo" class="h-7 w-7 object-contain" />
                    </div>
                    <span class="text-white/90 font-bold text-lg tracking-wide">SAMOSIR</span>
                </div>

                <!-- Center: Main Content -->
                <div class="flex-1 flex flex-col justify-center max-w-lg">
                    <h1 class="text-5xl font-extrabold text-white leading-tight mb-6">
                        Sistem Informasi
                        <span class="block bg-gradient-to-r from-cyan-300 to-blue-300 bg-clip-text text-transparent">
                            Pelabuhan Perikanan
                        </span>
                    </h1>
                    <p class="text-blue-200/80 text-lg leading-relaxed mb-10">
                        Mengelola data kedatangan, keberangkatan, dan penimbangan ikan pada Pelabuhan Perikanan Nusantara Sibolga secara digital dan terintegrasi.
                    </p>

                    <!-- Stats Cards -->
                    <div class="grid grid-cols-3 gap-4">
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/10 hover:bg-white/15 transition-all duration-300">
                            <div class="flex items-center space-x-2 mb-2">
                                <div class="w-8 h-8 bg-cyan-400/20 rounded-lg flex items-center justify-center">
                                    <i class="ri-ship-2-line text-cyan-300 text-sm"></i>
                                </div>
                            </div>
                            <p class="text-white font-bold text-xl">Real-time</p>
                            <p class="text-blue-300/60 text-xs mt-1">Data Kapal</p>
                        </div>
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/10 hover:bg-white/15 transition-all duration-300">
                            <div class="flex items-center space-x-2 mb-2">
                                <div class="w-8 h-8 bg-emerald-400/20 rounded-lg flex items-center justify-center">
                                    <i class="ri-bar-chart-grouped-line text-emerald-300 text-sm"></i>
                                </div>
                            </div>
                            <p class="text-white font-bold text-xl">Analitik</p>
                            <p class="text-blue-300/60 text-xs mt-1">Dashboard</p>
                        </div>
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/10 hover:bg-white/15 transition-all duration-300">
                            <div class="flex items-center space-x-2 mb-2">
                                <div class="w-8 h-8 bg-violet-400/20 rounded-lg flex items-center justify-center">
                                    <i class="ri-map-pin-2-line text-violet-300 text-sm"></i>
                                </div>
                            </div>
                            <p class="text-white font-bold text-xl">Tracking</p>
                            <p class="text-blue-300/60 text-xs mt-1">Posisi Kapal</p>
                        </div>
                    </div>
                </div>

                <!-- Bottom: Date & Time -->
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-white/40 text-xs uppercase tracking-widest">Indonesia, Sumatera Utara</p>
                        <p class="text-white/70 text-sm mt-1">{{ currentDate }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-white font-mono text-3xl font-light tracking-widest">{{ currentTime }}</p>
                        <p class="text-white/40 text-xs uppercase tracking-widest">WIB</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Panel - Login Form -->
        <div class="w-full lg:w-[45%] flex items-center justify-center p-6 sm:p-12 bg-white dark:bg-gray-900">
            <div class="w-full max-w-md">
                <!-- Mobile Logo (shown only on small screens) -->
                <div class="lg:hidden text-center mb-8">
                    <div class="mx-auto h-16 w-16 flex items-center justify-center mb-4">
                        <img :src="'/img/logobaru.png'" alt="SAMOSIR Logo" class="h-full w-full object-contain" />
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">SAMOSIR</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Pelabuhan Perikanan Nusantara Sibolga</p>
                </div>

                <!-- Welcome Text -->
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                        Selamat Datang 👋
                    </h2>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        Silakan masuk ke akun Anda untuk melanjutkan
                    </p>
                </div>

                <!-- Login Form -->
                <form @submit.prevent="submit" class="space-y-5">
                    <!-- Email Field -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            Alamat Email
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                <i class="ri-mail-line text-gray-400 dark:text-gray-500 text-lg"></i>
                            </div>
                            <input
                                id="email"
                                type="email"
                                v-model="form.email"
                                required
                                autofocus
                                autocomplete="email"
                                class="block w-full pl-11 pr-4 py-3 border border-gray-200 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 bg-gray-50 dark:bg-gray-800/50 transition-all duration-200 text-sm"
                                placeholder="nama@email.com"
                            />
                        </div>
                        <div v-if="form.errors.email" class="mt-1.5 flex items-center text-sm text-red-500 dark:text-red-400">
                            <i class="ri-error-warning-line mr-1 text-xs"></i>
                            {{ form.errors.email }}
                        </div>
                    </div>

                    <!-- Password Field -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            Kata Sandi
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                <i class="ri-lock-2-line text-gray-400 dark:text-gray-500 text-lg"></i>
                            </div>
                            <input
                                id="password"
                                :type="showPassword ? 'text' : 'password'"
                                v-model="form.password"
                                required
                                autocomplete="current-password"
                                class="block w-full pl-11 pr-12 py-3 border border-gray-200 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 bg-gray-50 dark:bg-gray-800/50 transition-all duration-200 text-sm"
                                placeholder="••••••••"
                            />
                            <button 
                                type="button"
                                @click="togglePassword" 
                                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
                            >
                                <i :class="showPassword ? 'ri-eye-off-line' : 'ri-eye-line'" class="text-lg"></i>
                            </button>
                        </div>
                        <div v-if="form.errors.password" class="mt-1.5 flex items-center text-sm text-red-500 dark:text-red-400">
                            <i class="ri-error-warning-line mr-1 text-xs"></i>
                            {{ form.errors.password }}
                        </div>
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center justify-between">
                        <label class="flex items-center cursor-pointer group">
                            <div class="relative">
                                <input
                                    id="remember"
                                    type="checkbox"
                                    v-model="form.remember"
                                    class="sr-only peer"
                                />
                                <div class="w-5 h-5 border-2 border-gray-300 dark:border-gray-600 rounded-md peer-checked:bg-blue-600 peer-checked:border-blue-600 transition-all duration-200 flex items-center justify-center">
                                    <svg class="w-3 h-3 text-white opacity-0 peer-checked:opacity-100 transition-opacity" :class="{ 'opacity-100': form.remember }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                            </div>
                            <span class="ml-2.5 text-sm text-gray-600 dark:text-gray-400 group-hover:text-gray-800 dark:group-hover:text-gray-200 transition-colors">Ingat saya</span>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="relative w-full flex items-center justify-center py-3.5 px-4 border border-transparent text-sm font-semibold rounded-xl text-white bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-gray-900 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-300 shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40 active:scale-[0.98]"
                    >
                        <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <i v-else class="ri-login-box-line mr-2"></i>
                        {{ form.processing ? 'Memproses...' : 'Masuk ke Akun' }}
                    </button>
                </form>

                <!-- Divider -->
                <div class="my-8 flex items-center">
                    <div class="flex-1 border-t border-gray-200 dark:border-gray-800"></div>
                    <span class="px-4 text-xs text-gray-400 dark:text-gray-600 uppercase tracking-wider">Info</span>
                    <div class="flex-1 border-t border-gray-200 dark:border-gray-800"></div>
                </div>

                <!-- Help Text -->
                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-4 border border-blue-100 dark:border-blue-800/30">
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0 w-8 h-8 bg-blue-100 dark:bg-blue-800/40 rounded-lg flex items-center justify-center mt-0.5">
                            <i class="ri-information-line text-blue-600 dark:text-blue-400"></i>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-blue-800 dark:text-blue-300">Butuh bantuan?</p>
                            <p class="text-xs text-blue-600/70 dark:text-blue-400/60 mt-0.5">
                                Hubungi administrator sistem untuk mendapatkan akun atau mereset kata sandi Anda.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="mt-8 text-center">
                    <p class="text-xs text-gray-400 dark:text-gray-600">
                        © 2024-2026 SAMOSIR v3.0 · Pelabuhan Perikanan Nusantara Sibolga
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Smooth entrance animation */
@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

/* Apply animations to form elements */
form > div:nth-child(1) { animation: slideUp 0.5s ease-out 0.1s both; }
form > div:nth-child(2) { animation: slideUp 0.5s ease-out 0.2s both; }
form > div:nth-child(3) { animation: slideUp 0.5s ease-out 0.3s both; }
form > button { animation: slideUp 0.5s ease-out 0.4s both; }
</style>