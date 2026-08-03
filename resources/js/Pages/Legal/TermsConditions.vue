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
    <Head title="Syarat & Ketentuan - SAMOSIR" />

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
                    <i class="ri-file-text-line text-cyan-300"></i>
                    <span>Aturan & Perjanjian Penggunaan</span>
                </div>
                <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight">Syarat & Ketentuan</h1>
                <p class="mt-3 text-blue-100/80 text-sm sm:text-base max-w-2xl mx-auto">
                    Ketentuan penggunaan layanan aplikasi SAMOSIR di Pelabuhan Perikanan Nusantara Sibolga.
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
                            <i class="ri-checkbox-circle-line text-lg"></i>
                        </div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">1. Penerimaan Ketentuan</h2>
                    </div>
                    <p class="text-sm leading-relaxed text-gray-600 dark:text-gray-300 pl-11">
                        Dengan mengakses atau menggunakan aplikasi SAMOSIR (Sistem Informasi dan Monitoring Aktivitas Kapal Perikanan), Anda menyatakan telah membaca, memahami, dan menyetujui untuk terikat oleh seluruh Syarat dan Ketentuan ini. Jika Anda tidak menyetujui salah satu poin dalam syarat ini, Anda tidak diperkenankan menggunakan aplikasi.
                    </p>
                </section>

                <hr class="border-gray-100 dark:border-gray-800" />

                <!-- Section 2 -->
                <section class="space-y-3">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center text-blue-600 dark:text-blue-400">
                            <i class="ri-user-settings-line text-lg"></i>
                        </div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">2. Akun & Keamanan Kredensial</h2>
                    </div>
                    <div class="pl-11 text-sm leading-relaxed text-gray-600 dark:text-gray-300 space-y-2">
                        <ul class="list-disc pl-5 space-y-2">
                            <li><strong class="text-gray-900 dark:text-white">Akses Terbatas:</strong> Akun aplikasi diberikan secara khusus kepada pengguna sesuai dengan peran (Admin, Syahbandar, Petugas Lapangan, dan Pengelola Kapal).</li>
                            <li><strong class="text-gray-900 dark:text-white">Kerahasiaan Password:</strong> Pengguna bertanggung jawab penuh atas kerahasiaan kata sandi dan seluruh aktivitas yang terjadi di bawah akun mereka.</li>
                            <li><strong class="text-gray-900 dark:text-white">Pelaporan Akun:</strong> Apabila indikasi penyalahgunaan atau akses tanpa izin ditemukan, pengguna wajib segera melaporkannya kepada Administrator PPN Sibolga.</li>
                        </ul>
                    </div>
                </section>

                <hr class="border-gray-100 dark:border-gray-800" />

                <!-- Section 3 -->
                <section class="space-y-3">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center text-blue-600 dark:text-blue-400">
                            <i class="ri-shield-cross-line text-lg"></i>
                        </div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">3. Kewajiban & Larangan Pengguna</h2>
                    </div>
                    <div class="pl-11 text-sm leading-relaxed text-gray-600 dark:text-gray-300 space-y-3">
                        <p>Pengguna diwajibkan memberikan informasi yang benar, akurat, dan dapat dipertanggungjawabkan. Pengguna dilarang keras untuk:</p>
                        <ul class="list-disc pl-5 space-y-2">
                            <li>Memasukkan data palsu atau memanipulasi dokumen kedatangan/keberangkatan kapal dan data bongkar muat ikan.</li>
                            <li>Menggunakan aplikasi untuk tindakan ilegal atau melanggar peraturan perundang-undangan perikanan yang berlaku.</li>
                            <li>Mengintervensi atau mencoba merusak integritas sistem, server, atau jaringan SAMOSIR.</li>
                            <li>Mengalihkan atau memindahtangankan akun pengguna kepada pihak lain tanpa persetujuan tertulis dari pengelola.</li>
                        </ul>
                    </div>
                </section>

                <hr class="border-gray-100 dark:border-gray-800" />

                <!-- Section 4 -->
                <section class="space-y-3">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center text-blue-600 dark:text-blue-400">
                            <i class="ri-service-line text-lg"></i>
                        </div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">4. Ketentuan Jasa Pelabuhan</h2>
                    </div>
                    <div class="pl-11 text-sm leading-relaxed text-gray-600 dark:text-gray-300 space-y-2">
                        <p>Pengajuan pemesanan jasa (Peralatan, Ice Cruiser, dan Air Tawar) melalui aplikasi tunduk pada ketentuan berikut:</p>
                        <ul class="list-disc pl-5 space-y-2">
                            <li>Setiap permohonan jasa harus melalui tahapan pesanan, kalkulasi hitung oleh petugas, dan pelunasan pembayaran.</li>
                            <li>Tarif jasa dihitung berdasarkan ketentuan yang berlaku di Pelabuhan Perikanan Nusantara Sibolga.</li>
                            <li>Pengelola berhak menolak permohonan jasa apabila kuota/fasilitas tidak memadai atau pengguna memiliki tunggakan kewajiban.</li>
                        </ul>
                    </div>
                </section>

                <hr class="border-gray-100 dark:border-gray-800" />

                <!-- Section 5 -->
                <section class="space-y-3">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center text-blue-600 dark:text-blue-400">
                            <i class="ri-copper-coin-line text-lg"></i>
                        </div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">5. Hak Kekayaan Intelektual</h2>
                    </div>
                    <p class="text-sm leading-relaxed text-gray-600 dark:text-gray-300 pl-11">
                        Seluruh desain, kode program, merek dagang, logo, serta konten dalam aplikasi SAMOSIR adalah milik Pelabuhan Perikanan Nusantara Sibolga dan pengembangnya. Dilarang menggandakan, mendistribusikan, atau memodifikasi bagian mana pun dari sistem ini tanpa izin tertulis.
                    </p>
                </section>

                <hr class="border-gray-100 dark:border-gray-800" />

                <!-- Section 6 -->
                <section class="space-y-3">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center text-blue-600 dark:text-blue-400">
                            <i class="ri-refresh-line text-lg"></i>
                        </div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">6. Perubahan Syarat & Ketentuan</h2>
                    </div>
                    <p class="text-sm leading-relaxed text-gray-600 dark:text-gray-300 pl-11">
                        PPN Sibolga berhak memperbarui atau mengubah Syarat & Ketentuan ini sewaktu-waktu sesuai dengan perkembangan regulasi atau kebutuhan teknis aplikasi. Perubahan akan dipublikasikan pada halaman ini dengan tanggal pembaruan terbaru.
                    </p>
                </section>

                <hr class="border-gray-100 dark:border-gray-800" />

                <!-- Section 7 -->
                <section class="space-y-3">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center text-blue-600 dark:text-blue-400">
                            <i class="ri-government-line text-lg"></i>
                        </div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">7. Hukum yang Berlaku</h2>
                    </div>
                    <p class="text-sm leading-relaxed text-gray-600 dark:text-gray-300 pl-11">
                        Syarat dan Ketentuan ini diatur dan ditafsirkan sesuai dengan hukum Republik Indonesia. Setiap sengketa yang timbul dari atau sehubungan dengan penggunaan aplikasi ini akan diselesaikan secara musyawarah atau melalui badan peradilan yang berwenang di Republik Indonesia.
                    </p>
                </section>

            </div>

            <!-- Footer links -->
            <div class="mt-8 text-center space-y-2">
                <div class="flex items-center justify-center space-x-4 text-xs font-medium text-gray-500 dark:text-gray-400">
                    <Link :href="route('privacy-policy')" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors hover:underline">
                        Kebijakan Privasi
                    </Link>
                    <span>•</span>
                    <span class="text-blue-600 dark:text-blue-400">Syarat & Ketentuan</span>
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
