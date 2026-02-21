# Dashboard SAMOSIR

Dashboard lengkap untuk Sistem Informasi Pelabuhan Perikanan Nusantara Sibolga.

## 🎯 Fitur Dashboard

### Statistik Utama
- **Total Kapal** - Jumlah seluruh kapal terdaftar
- **Kapal Aktif** - Kapal yang sudah disetujui
- **Pending Approval** - Kapal yang menunggu persetujuan
- **Total Pengguna** - Jumlah pengguna aktif
- **Kedatangan Hari Ini** - Kapal yang tiba hari ini
- **Keberangkatan Hari Ini** - Kapal yang berangkat hari ini
- **Kapal Tambat** - Kapal yang sedang berlabuh
- **Kapal Bongkar** - Kapal yang sedang membongkar muatan

### Grafik dan Visualisasi
1. **Statistik Bulanan** - Grafik batang yang menunjukkan perbandingan kedatangan dan keberangkatan setiap bulan
2. **Distribusi Status Kapal** - Diagram lingkaran menampilkan status kapal (Disetujui, Pending, Ditolak)

### Tabel Data
1. **Kedatangan Terbaru** - 10 kedatangan kapal terakhir dengan status (TAMBAT, BONGKAR, SELESAI)
2. **Keberangkatan Terbaru** - 10 keberangkatan kapal terakhir dengan status (Sesuai Jadwal, Terlambat, Dibatalkan)
3. **Pending Approval Kapal** - 5 kapal yang menunggu persetujuan

### Lokasi Pendaratan
- **Top Lokasi Pendaratan** - 5 lokasi pendaratan terbanyak berdasarkan jumlah kedatangan

## 🛠️ Tech Stack

- **Backend**: Laravel 12 + MySQL
- **Frontend**: Inertia.js + Vue 3 + Tailwind CSS
- **Authentication**: Laravel Sanctum (API) + Session (Web)

## 📦 Struktur File

```
app/
├── Http/
│   ├── Controllers/
│   │   └── DashboardController.php    # Controller dashboard
│   └── Middleware/
│       └── HandleInertiaRequests.php  # Middleware Inertia.js
├── Models/
│   ├── Arrival.php                    # Model kedatangan
│   ├── Departure.php                  # Model keberangkatan
│   ├── FishSpecies.php                # Model spesies ikan
│   ├── LandingSite.php                # Model lokasi pendaratan
│   ├── User.php                       # Model pengguna
│   └── Vessel.php                     # Model kapal
resources/
├── js/
│   ├── Pages/
│   │   └── Dashboard/
│   │       └── Index.vue              # Komponen Vue dashboard
│   └── app.js                        # Entry point Vue + Inertia
├── css/
│   └── app.css                       # Styles Tailwind CSS
└── views/
    └── app.blade.php                  # Template Blade untuk Inertia
```

## 🚀 Instalasi dan Penggunaan

### 1. Install Dependencies
```bash
npm install
```

### 2. Konfigurasi Environment
Pastikan file `.env` sudah dikonfigurasi dengan benar:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=samosir
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 3. Run Migrations
```bash
php artisan migrate
```

### 4. Run Seeders (Optional)
```bash
php artisan db:seed
```

### 5. Build Assets
Untuk production:
```bash
npm run build
```

Untuk development:
```bash
npm run dev
```

### 6. Start Development Server
```bash
php artisan serve
```

Buka browser dan akses: `http://localhost:8000`

## 📊 Data yang Ditampilkan

### Statistik
Data diambil secara real-time dari database:
- Total kapal dari tabel `vessels`
- Pengguna aktif dari tabel `users`
- Kedatangan hari ini dari tabel `arrivals`
- Keberangkatan hari ini dari tabel `departures`

### Grafik Bulanan
- Data kedatangan dan keberangkatan di-group berdasarkan bulan
- Menampilkan data untuk tahun berjalan

### Tabel
- Menampilkan 10-20 record terbaru
- Dengan informasi relasi (nama kapal, lokasi pendaratan, dll)

## 🎨 Customisasi

### Mengubah Warna dan Style
Edit file `resources/css/app.css` dan `resources/js/Pages/Dashboard/Index.vue`

### Menambahkan Statistik Baru
1. Edit `app/Http/Controllers/DashboardController.php`
2. Tambahkan query ke array `$stats`
3. Update komponen Vue untuk menampilkan data baru

### Menambahkan Grafik Baru
Dashboard menggunakan Tailwind CSS untuk visualisasi. Anda dapat:
- Mengintegrasikan library grafik seperti Chart.js atau ApexCharts
- Menggunakan komponen grafik yang sudah ada sebagai referensi

## 🔐 Authentication

Dashboard menggunakan:
- **Laravel Sanctum** untuk API authentication
- **Session-based auth** untuk web

Untuk mengakses dashboard:
1. Login sebagai pengguna yang terdaftar
2. Dashboard akan menampilkan data sesuai dengan role pengguna

## 📝 Routes

```php
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
```

Dashboard dapat diakses di route utama (`/`).

## 🐛 Troubleshooting

### Issue: Dependencies tidak terinstall
```bash
rm -rf node_modules package-lock.json
npm install
```

### Issue: Build error
```bash
npm run build --force
```

### Issue: Database connection
Pastikan file `.env` sudah dikonfigurasi dengan benar dan database MySQL sudah berjalan.

### Issue: Data tidak muncul
1. Pastikan database sudah di-migrate
2. Cek apakah ada data di database
3. Jalankan seeder untuk data dummy:
   ```bash
   php artisan db:seed
   ```

## 📄 Models yang Digunakan

### Vessel
- Data kapal (nama, pemilik, GT, alat tangkap, dll)
- Status approval (pending, approved, rejected)
- Relasi dengan managers, arrivals, departures

### Arrival
- Data kedatangan kapal
- Status (TAMBAT, BONGKAR, SELESAI)
- Informasi kualitas ikan, suhu, dll

### Departure
- Data keberangkatan kapal
- Status (Sesuai Jadwal, Terlambat, Dibatalkan)
- Informasi suplai (es, air, solar, dll)

### LandingSite
- Lokasi pendaratan kapal
- Koordinat GPS
- Status aktif/non-aktif

### User
- Data pengguna sistem
- Role (admin, manager, dll)
- Status aktif/non-aktif

## 🔄 Update Dashboard

Untuk memperbarui dashboard:
1. Edit file yang relevan (Controller, Vue component, dll)
2. Rebuild assets jika perlu:
   ```bash
   npm run build
   ```
3. Refresh browser

## 📞 Support

Untuk pertanyaan atau issue, silakan hubungi tim development.

## 📄 License

© 2026 Pelabuhan Perikanan Nusantara Sibolga