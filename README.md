# SAMOSIR - Sistem Manajemen Kapal dan Pelabuhan

Sistem manajemen kapal dan pelabuhan yang komprehensif untuk memantau dan mengelola aktivitas perikanan di pelabuhan.

## Deskripsi Project

SAMOSIR adalah sistem berbasis web yang dirancang untuk mempermudah pengelolaan kapal ikan, pemantauan aktivitas kedatangan dan keberangkatan kapal, serta manajemen fasilitas pelabuhan. Sistem ini menyediakan antarmuka yang modern dan responsif untuk petugas pelabuhan, pengelola kapal, dan pengguna lainnya.

## Fitur Utama

### Manajemen Kapal
- ✅ Data kapal lengkap (nama, pemilik, jenis, GT, LOA, dll)
- ✅ Manajemen dokumen SIPI dan SIUP
- ✅ Monitoring status SIPI (Aktif/Expired)
- ✅ Sistem approval kapal
- ✅ Pengelola kapal (manajer kapal)
- ✅ QR Code untuk identifikasi kapal

### Manajemen Pengguna
- ✅ Multi-level user management
- ✅ Role-based access control
- ✅ User authentication dan authorization
- ✅ Profile management

### Manajemen Data Master
- ✅ Jenis ikan (fish species)
- ✅ Lokasi pendaratan (dermaga/landing sites)
- ✅ Data lengkap dengan validasi

### Aktivitas Pelabuhan
- ✅ Monitoring kedatangan kapal (arrivals)
- ✅ Monitoring keberangkatan kapal (departures)
- ✅ Pencatatan hasil tangkapan
- ✅ Activity logging untuk audit trail
- ✅ Fitur Chat Real-time (lintas perangkat Web & Mobile)
- ✅ Konsultasi masyarakat ke Petugas/Master Pelabuhan


### Layanan Pelabuhan
- ✅ Layanan air (water services)
- ✅ Layanan peralatan (equipment services)
- ✅ Inventory peralatan
- ✅ Management dokumen

## Teknologi

### Backend
- **Laravel 11** - PHP Framework
- **MySQL** - Database
- **Laravel Sanctum** - API Authentication
- **Laravel Reverb** - WebSocket Server (Real-time Chat)


### Frontend
- **Vue 3** - JavaScript Framework
- **Inertia.js** - SPA tanpa memisahkan frontend/backend
- **Tailwind CSS** - Utility-first CSS framework
- **Vite** - Build tool dan asset bundler

### Authentication
- Session-based authentication untuk web
- Token-based authentication untuk mobile/API

## Struktur Database

### Tabel Utama
- `users` - Data pengguna
- `vessels` - Data kapal
- `vessel_managers` - Pengelola kapal
- `fish_species` - Jenis ikan
- `landing_sites` - Lokasi pendaratan
- `arrivals` - Data kedatangan
- `arrivals_catches` - Hasil tangkapan
- `departures` - Data keberangkatan
- `unloadings` - Data bongkar muat
- `movements` - Pergerakan kapal
- `water_services` - Layanan air
- `equipment_services` - Layanan peralatan
- `equipment_items` - Inventory peralatan
- `documents` - Manajemen dokumen
- `activity_logs` - Log aktivitas
- `conversations` - Daftar percakapan chat
- `messages` - Isi pesan chat
- `conversation_user` - Partisipan chat


## Instalasi

### Prerequisites
- PHP >= 8.2
- Composer
- Node.js >= 18
- MySQL >= 5.7
- Redis (Optional, untuk antrean pesan skala besar)

### Panduan Instalasi VPS (Ubuntu/Debian)

1. **Clone Repository**
   ```bash
   git clone [url-repo]
   cd samosir_new
   ```

2. **Backend Setup**
   ```bash
   composer install
   cp .env.example .env
   php artisan key:generate
   ```

3. **Database & Chat Configuration**
   ```bash
   # Update DB_DATABASE, DB_USERNAME, DB_PASSWORD di .env
   php artisan migrate
   php artisan reverb:install
   ```

4. **Frontend Setup**
   ```bash
   npm install
   npm run build
   ```

5. **Menjalankan Services (Production)**
   Disarankan menggunakan **Supervisor** untuk menjaga service tetap berjalan:
   ```bash
   # Jalankan Queue Worker
   php artisan queue:work
   
   # Jalankan Reverb (WebSocket)
   php artisan reverb:start
   ```

6. **Web Server configuration**
   Pastikan port WebSocket (default: 8080) terbuka di firewall dan dikonfigurasi di Nginx/Apache.



## Kontak

Untuk pertanyaan dan dukungan, silakan hubungi tim development.

## Dukungan Teknis

- [Laravel Documentation](https://laravel.com/docs)
- [Vue.js Documentation](https://vuejs.org/guide/)
- [Inertia.js Documentation](https://inertiajs.com/)
- [Tailwind CSS Documentation](https://tailwindcss.com/docs)

## Roadmap

Fitur yang akan datang:
- [ ] Mobile application (React Native/Flutter)
- [ ] Real-time notifications
- [ ] Advanced reporting dan analytics
- [ ] Integration dengan sistem perizinan resmi
- [ ] QR Code scanning untuk kapal