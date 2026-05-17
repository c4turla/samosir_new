<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class FaqController extends Controller
{
    /**
     * Display the FAQ page.
     */
    public function index()
    {
        // Define FAQ categories and items statically for now.
        // In a real app, you might want to move this to a database if it needs to be managed dynamically.
        $faqs = [
            [
                'category' => 'Umum',
                'icon' => 'ri-information-line',
                'questions' => [
                    [
                        'id' => 'q1',
                        'question' => 'Apa itu aplikasi SAMOSIR?',
                        'answer' => 'SAMOSIR (Sistem Informasi Pelabuhan Perikanan Nusantara Sibolga) adalah platform digital terpadu yang dirancang untuk mengelola dan memonitor berbagai aktivitas di pelabuhan perikanan. Aplikasi ini memfasilitasi administrasi kapal, pendataan kedatangan dan keberangkatan, manajemen bongkar muat ikan, hingga pelayanan jasa di area pelabuhan secara efisien dan transparan.'
                    ],
                    [
                        'id' => 'q2',
                        'question' => 'Bagaimana cara mendapatkan akses ke aplikasi ini?',
                        'answer' => 'Akses ke aplikasi SAMOSIR diberikan sesuai dengan peran Anda di pelabuhan (misalnya: Syahbandar, Petugas Lapangan, atau Kepala Pelabuhan). Silakan hubungi Administrator sistem atau pihak pengelola PPN Sibolga untuk pembuatan akun dan pengaturan hak akses.'
                    ],
                    [
                        'id' => 'q3',
                        'question' => 'Apa fungsi fitur Mode Gelap (Dark Mode)?',
                        'answer' => 'Mode Gelap dirancang untuk mengurangi ketegangan mata saat menggunakan aplikasi di lingkungan yang minim cahaya atau di malam hari. Anda dapat mengaktifkannya melalui tombol khusus di bagian atas (header) aplikasi.'
                    ]
                ]
            ],
            [
                'category' => 'Manajemen Kapal & Aktivitas',
                'icon' => 'ri-ship-line',
                'questions' => [
                    [
                        'id' => 'q4',
                        'question' => 'Bagaimana alur persetujuan (approval) sebuah kapal baru?',
                        'answer' => 'Saat data kapal baru ditambahkan melalui menu Daftar Kapal, statusnya akan menjadi "Pending". Syahbandar atau pihak berwenang kemudian akan memverifikasi data tersebut melalui menu Approval Kapal. Jika disetujui, kapal tersebut baru dapat melakukan proses kedatangan atau keberangkatan.'
                    ],
                    [
                        'id' => 'q5',
                        'question' => 'Apa perbedaan antara Kedatangan dan Keberangkatan?',
                        'answer' => 'Menu Kedatangan digunakan untuk mencatat kapal yang baru bersandar di pelabuhan (status awal biasanya TAMBAT). Sedangkan menu Keberangkatan mencatat kapal yang telah mendapat Surat Persetujuan Berlayar (SPB) dan siap meninggalkan pelabuhan. Kedua proses ini memerlukan pendataan dokumen kelengkapan kapal.'
                    ],
                    [
                        'id' => 'q6',
                        'question' => 'Bagaimana cara mencatat hasil tangkapan ikan?',
                        'answer' => 'Pencatatan hasil tangkapan dilakukan melalui menu Penimbangan Ikan (Bongkar). Petugas dapat memasukkan jenis ikan dan berat (dalam kg). Total berat tangkapan ini akan secara otomatis terekap dalam laporan kedatangan kapal tersebut.'
                    ]
                ]
            ],
            [
                'category' => 'Pelayanan Jasa & Perhitungan',
                'icon' => 'ri-hand-heart-line',
                'questions' => [
                    [
                        'id' => 'q7',
                        'question' => 'Apa saja jenis jasa yang dilayani dalam aplikasi ini?',
                        'answer' => 'Saat ini, aplikasi SAMOSIR mendukung pengelolaan tiga jenis jasa utama: Jasa Peralatan (penyewaan alat pelabuhan), Jasa Ice Cruiser (kebutuhan es untuk kapal), dan Jasa Air Tawar (pengisian air bersih). Ketiganya memiliki alur pesanan, perhitungan biaya, dan pencetakan dokumen yang seragam.'
                    ],
                    [
                        'id' => 'q8',
                        'question' => 'Bagaimana alur status pada menu Jasa (Air, Ice Cruiser, Peralatan)?',
                        'answer' => 'Alurnya terbagi dalam 3 tahap: 1) Pesanan: Input data awal permohonan jasa. 2) Diproses: Setelah petugas lapangan mengisi detail penggunaan (volume/durasi/jumlah) dan sistem menghitung total biaya. 3) Selesai: Setelah pembayaran diverifikasi dan diselesaikan. Dokumen PDF (Cetak Order & Perhitungan) dapat diunduh pada masing-masing tahapan.'
                    ]
                ]
            ],
            [
                'category' => 'Laporan & Dashboard',
                'icon' => 'ri-bar-chart-line',
                'questions' => [
                    [
                        'id' => 'q9',
                        'question' => 'Apakah saya bisa mencetak laporan rekapitulasi data?',
                        'answer' => 'Ya, aplikasi menyediakan menu Laporan khusus untuk Kedatangan, Keberangkatan, Data Kapal, dan Tangkapan Ikan. Anda dapat memfilter data berdasarkan periode waktu tertentu (harian, mingguan, bulanan) dan mengekspor hasilnya untuk keperluan audit atau pelaporan.'
                    ],
                    [
                        'id' => 'q10',
                        'question' => 'Data apa saja yang ditampilkan pada Dashboard?',
                        'answer' => 'Dashboard menampilkan ringkasan data operasional secara real-time, meliputi statistik kapal aktif, jumlah kedatangan/keberangkatan harian, grafik tren aktivitas, sebaran jenis ikan hasil tangkapan, dan analisis pendapatan dari modul jasa. Data ini dapat difilter berdasarkan minggu, bulan, atau tahun ini.'
                    ]
                ]
            ]
        ];

        return Inertia::render('Faq/Index', [
            'faqs' => $faqs
        ]);
    }
}
