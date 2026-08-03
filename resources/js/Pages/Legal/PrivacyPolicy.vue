<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import { route } from 'ziggy-js';

const lastUpdated = '3 Agustus 2026';
const darkMode = ref(false);

const toggleDarkMode = () => {
    darkMode.value = !darkMode.value;
    if (darkMode.value) {
        document.documentElement.classList.add('dark');
        localStorage.setItem('darkMode', 'true');
    } else {
        document.documentElement.classList.remove('dark');
        localStorage.setItem('darkMode', 'false');
    }
};

onMounted(() => {
    darkMode.value = localStorage.getItem('darkMode') === 'true' || 
        (!localStorage.getItem('darkMode') && window.matchMedia('(prefers-color-scheme: dark)').matches);
    if (darkMode.value) {
        document.documentElement.classList.add('dark');
    }
});
</script>

<template>
    <Head title="Kebijakan Privasi - SAMOSIR" />

    <div class="min-h-screen bg-gray-50 dark:bg-gray-950 text-gray-800 dark:text-gray-200 transition-colors duration-300">
        <!-- Sticky Navbar Header -->
        <header class="sticky top-0 z-50 bg-white/80 dark:bg-gray-900/80 backdrop-blur-md border-b border-gray-200 dark:border-gray-800">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <Link :href="route('login')" class="p-2 rounded-lg text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors" title="Kembali ke Login">
                        <i class="ri-arrow-left-line text-xl"></i>
                    </Link>
                    <div class="flex items-center space-x-2.5">
                        <img :src="'/img/logobaru.webp'" alt="SAMOSIR Logo" class="h-9 w-9 object-contain" />
                        <div>
                            <span class="font-black text-lg text-gray-900 dark:text-white tracking-wide block leading-tight">SAMOSIR</span>
                            <span class="text-[10px] text-gray-500 dark:text-gray-400 font-medium block">PPN Sibolga</span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center space-x-3">
                    <button 
                        @click="toggleDarkMode" 
                        class="p-2 rounded-lg text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                        :title="darkMode ? 'Mode Terang' : 'Mode Gelap'"
                    >
                        <i :class="darkMode ? 'ri-sun-line' : 'ri-moon-line'" class="text-xl"></i>
                    </button>
                    <Link :href="route('login')" class="hidden sm:inline-flex items-center px-4 py-2 text-xs font-semibold rounded-lg text-white bg-blue-600 hover:bg-blue-700 transition-colors shadow-sm">
                        <i class="ri-login-box-line mr-1.5 text-sm"></i>
                        Masuk Sistem
                    </Link>
                </div>
            </div>
        </header>

        <!-- Hero Header -->
        <div class="bg-gradient-to-br from-blue-900 via-blue-800 to-indigo-900 text-white py-12 px-4 sm:px-6 lg:px-8 relative overflow-hidden">
            <div class="absolute inset-0 opacity-10">
                <div class="absolute top-0 left-0 w-full h-full" style="background-image: url('data:image/svg+xml,%3Csvg width=&quot;60&quot; height=&quot;60&quot; viewBox=&quot;0 0 60 60&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot;%3E%3Cg fill=&quot;none&quot; fill-rule=&quot;evenodd&quot;%3E%3Cg fill=&quot;%23ffffff&quot; fill-opacity=&quot;0.4&quot;%3E%3Cpath d=&quot;M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z&quot;/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
            </div>
            <div class="max-w-4xl mx-auto relative z-10 text-center">
                <div class="inline-flex items-center space-x-2 bg-blue-500/20 backdrop-blur-md px-3.5 py-1.5 rounded-full border border-blue-400/30 text-blue-200 text-xs font-medium mb-4">
                    <i class="ri-shield-user-line text-cyan-300"></i>
                    <span>Dokumen Kerahasiaan & Privasi</span>
                </div>
                <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight">Kebijakan Privasi</h1>
                <p class="mt-3 text-blue-100/80 text-sm sm:text-base max-w-2xl mx-auto">
                    Komitmen kami dalam melindungi dan mengelola data pribadi serta informasi operasional kapal di Pelabuhan Perikanan Nusantara Sibolga.
                </p>
                <p class="mt-4 text-xs text-blue-200/60">Terakhir Diperbarui: {{ lastUpdated }}</p>
            </div>
        </div>

        <!-- Main Content -->
        <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-xl shadow-gray-200/50 dark:shadow-none border border-gray-100 dark:border-gray-800 p-6 sm:p-10 space-y-8">
                
                <!-- Section 1 -->
                <section class="space-y-3">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center text-blue-600 dark:text-blue-400">
                            <i class="ri-information-line text-lg"></i>
                        </div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">1. Pendahuluan</h2>
                    </div>
                    <p class="text-sm leading-relaxed text-gray-600 dark:text-gray-300 pl-11">
                        Sistem Informasi dan Monitoring Aktivitas Kapal Perikanan (SAMOSIR) yang dioperasikan di Pelabuhan Perikanan Nusantara (PPN) Sibolga berkomitmen untuk menjaga kerahasiaan dan keamanan informasi serta data pengguna. Kebijakan Privasi ini menjelaskan bagaimana kami mengumpulkan, menggunakan, menyimpan, dan melindungi informasi yang Anda berikan saat menggunakan aplikasi ini.
                    </p>
                </section>

                <hr class="border-gray-100 dark:border-gray-800" />

                <!-- Section 2 -->
                <section class="space-y-3">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center text-blue-600 dark:text-blue-400">
                            <i class="ri-database-2-line text-lg"></i>
                        </div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">2. Informasi yang Kami Kumpulkan</h2>
                    </div>
                    <div class="pl-11 text-sm leading-relaxed text-gray-600 dark:text-gray-300 space-y-3">
                        <p>Untuk mendukung aktivitas operasional perikanan dan pelayanan publik, aplikasi SAMOSIR mengumpulkan jenis informasi berikut:</p>
                        <ul class="list-disc pl-5 space-y-2">
                            <li><strong class="text-gray-900 dark:text-white">Data Akun Pengguna:</strong> Nama lengkap, alamat email, nomor telepon, peran (role) penguna (Admin, Syahbandar, Petugas, Pengelola Kapal), dan kata sandi yang tersimpan dalam bentuk terenkripsi.</li>
                            <li><strong class="text-gray-900 dark:text-white">Data Operasional Kapal:</strong> Nama kapal, nomor tanda selar/GT, pemilik/pengelola kapal, data nahkoda, jumlah ABK, serta dokumen pendukung keberangkatan dan kedatangan kapal (SPB, logbook).</li>
                            <li><strong class="text-gray-900 dark:text-white">Data Hasil Tangkapan & Jasa:</strong> Rincian penimbangan jenis ikan, berat tangkapan, serta penggunaan layanan pelabuhan (peminjaman peralatan, pengisian es, dan air bersih).</li>
                            <li><strong class="text-gray-900 dark:text-white">Data Log & Posisi (VMS):</strong> Catatan waktu akses (timestamp), IP address, log aktivitas sistem, dan data posisi geografis kapal jika terhubung dengan sistem pelacakan.</li>
                        </ul>
                    </div>
                </section>

                <hr class="border-gray-100 dark:border-gray-800" />

                <!-- Section 3 -->
                <section class="space-y-3">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center text-blue-600 dark:text-blue-400">
                            <i class="ri-settings-4-line text-lg"></i>
                        </div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">3. Penggunaan Informasi</h2>
                    </div>
                    <div class="pl-11 text-sm leading-relaxed text-gray-600 dark:text-gray-300 space-y-2">
                        <p>Informasi yang dikumpulkan digunakan semata-mata untuk kepentingan berikut:</p>
                        <ul class="list-disc pl-5 space-y-2">
                            <li>Memproses verifikasi dan persetujuan (approval) kedatangan/keberangkatan kapal perikanan.</li>
                            <li>Mengelola layanan pelabuhan meliputi penyediaan alat, fasilitas air tawar, dan es.</li>
                            <li>Penyusunan laporan rekapitulasi statistik perikanan resmi PPN Sibolga.</li>
                            <li>Meningkatkan efisiensi komunikasi serta keamanan operasional di wilayah pelabuhan.</li>
                            <li>Memenuhi kewajiban hukum dan regulasi Kementerian Kelautan dan Perikanan (KKP).</li>
                        </ul>
                    </div>
                </section>

                <hr class="border-gray-100 dark:border-gray-800" />

                <!-- Section 4 -->
                <section class="space-y-3">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center text-blue-600 dark:text-blue-400">
                            <i class="ri-lock-password-line text-lg"></i>
                        </div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">4. Keamanan & Perlindungan Data</h2>
                    </div>
                    <p class="text-sm leading-relaxed text-gray-600 dark:text-gray-300 pl-11">
                        Kami menerapkan standar teknis dan organisasi yang ketat untuk menjamin keamanan data Anda. Kata sandi dienkripsi menggunakan algoritma hashing aman. Akses terhadap informasi dibatasi hanya kepada personil berwenang sesuai dengan tingkat hak akses (Role-Based Access Control). Evaluasi keamanan dan pencadangan data dilakukan secara berkala.
                    </p>
                </section>

                <hr class="border-gray-100 dark:border-gray-800" />

                <!-- Section 5 -->
                <section class="space-y-3">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center text-blue-600 dark:text-blue-400">
                            <i class="ri-share-forward-line text-lg"></i>
                        </div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">5. Pembagian Informasi Kepada Pihak Ketiga</h2>
                    </div>
                    <p class="text-sm leading-relaxed text-gray-600 dark:text-gray-300 pl-11">
                        Pemerintah PPN Sibolga tidak menjual, menyewakan, atau memperdagangkan data pribadi pengguna kepada pihak ketiga. Informasi dapat dibagikan kepada instansi pemerintah berwenang (seperti Kementerian Kelautan dan Perikanan atau instansi penegak hukum) bilamana diwajibkan oleh ketentuan perundang-undangan Republik Indonesia.
                    </p>
                </section>

                <hr class="border-gray-100 dark:border-gray-800" />

                <!-- Section 6 -->
                <section class="space-y-3">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center text-blue-600 dark:text-blue-400">
                            <i class="ri-customer-service-2-line text-lg"></i>
                        </div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">6. Kontak & Bantuan</h2>
                    </div>
                    <div class="pl-11 text-sm leading-relaxed text-gray-600 dark:text-gray-300 space-y-2">
                        <p>Jika Anda memiliki pertanyaan, saran, atau kendala terkait Kebijakan Privasi ini, silakan hubungi tim pengelola melalui:</p>
                        <div class="bg-gray-50 dark:bg-gray-800/60 rounded-xl p-4 border border-gray-200 dark:border-gray-700/50 space-y-2 mt-3">
                            <p class="font-semibold text-gray-900 dark:text-white">Pelabuhan Perikanan Nusantara (PPN) Sibolga</p>
                            <p class="text-xs flex items-center text-gray-600 dark:text-gray-400">
                                <i class="ri-map-pin-line mr-2 text-blue-500"></i> Jl. Ph. Tobing, Kota Sibolga, Sumatera Utara
                            </p>
                            <p class="text-xs flex items-center text-gray-600 dark:text-gray-400">
                                <i class="ri-global-line mr-2 text-blue-500"></i> Website: <a href="https://kendariweb.com" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline ml-1">kendariweb.com</a>
                            </p>
                        </div>
                    </div>
                </section>

            </div>

            <!-- Footer links -->
            <div class="mt-8 text-center space-y-2">
                <div class="flex items-center justify-center space-x-4 text-xs font-medium text-gray-500 dark:text-gray-400">
                    <span class="text-blue-600 dark:text-blue-400">Kebijakan Privasi</span>
                    <span>•</span>
                    <Link :href="route('terms-conditions')" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors hover:underline">
                        Syarat & Ketentuan
                    </Link>
                    <span>•</span>
                    <Link :href="route('login')" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors hover:underline">
                        Halaman Login
                    </Link>
                </div>
                <p class="text-xs text-gray-400 dark:text-gray-600">
                    © 2022-2026 SAMOSIR v3.0 · Pelabuhan Perikanan Nusantara Sibolga
                </p>
            </div>
        </main>
    </div>
</template>
