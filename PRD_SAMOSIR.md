# Product Requirements Document (PRD)
# SAMOSIR — Sistem Informasi Pelabuhan Perikanan Nusantara Sibolga

**Versi:** 3.0  
**Tanggal:** 9 Juni 2026  
**Status:** Production  
**URL Production:** https://demo-samosir.kendariweb.web.id

---

## 1. Ringkasan Eksekutif

SAMOSIR adalah platform digital terpadu untuk mengelola seluruh aktivitas operasional di Pelabuhan Perikanan Nusantara (PPN) Sibolga. Sistem ini mencakup manajemen kapal, pencatatan lalu lintas pelabuhan (kedatangan & keberangkatan), penimbangan hasil tangkapan ikan, pelayanan jasa pelabuhan, pelaporan, serta komunikasi real-time antar pengguna.

Aplikasi dibangun sebagai **Single Page Application (SPA)** menggunakan arsitektur modern berbasis **Laravel 12 + Inertia.js + Vue 3**, di-deploy menggunakan **Docker** pada platform **EasyPanel**.

---

## 2. Tujuan Produk

1. **Digitalisasi Operasional Pelabuhan** — Menggantikan proses manual pencatatan kedatangan, keberangkatan, dan bongkar muat ikan dengan sistem digital yang terintegrasi.
2. **Transparansi & Akuntabilitas** — Menyediakan alur persetujuan (approval workflow) berlapis untuk setiap aktivitas kritis.
3. **Efisiensi Pelayanan Jasa** — Mengotomatisasi perhitungan biaya layanan peralatan, ice cruiser, dan air bersih.
4. **Pelaporan Real-time** — Dashboard analitik dan laporan yang dapat diekspor (Excel/PDF) untuk pengambilan keputusan.
5. **Komunikasi Terpadu** — Sistem pesan real-time (chat) berbasis WebSocket untuk koordinasi antar stakeholder.
6. **Akses Mobile** — RESTful API (v1) dengan autentikasi Sanctum untuk aplikasi mobile pendamping.

---

## 3. Arsitektur & Teknologi

### 3.1 Technology Stack

| Layer | Teknologi |
|---|---|
| **Backend Framework** | Laravel 12 (PHP 8.3) |
| **Frontend Framework** | Vue 3 + Inertia.js v2 |
| **CSS Framework** | Tailwind CSS 4 |
| **Charting** | ECharts 6 + Highcharts 12 |
| **Peta / Maps** | Leaflet.js 1.9 |
| **Icons** | Remix Icon 4.9 |
| **Real-time** | Laravel Reverb (WebSocket) + Laravel Echo |
| **Database** | MySQL 8.0 |
| **API Auth** | Laravel Sanctum (Token-based) |
| **PDF Export** | Barryvdh/DomPDF |
| **Build Tool** | Vite 7 |
| **Routing (JS)** | Ziggy 2.6 |
| **Deployment** | Docker (multi-stage) + EasyPanel |
| **Process Manager** | Supervisord (Nginx + PHP-FPM + Queue Worker + Reverb) |

### 3.2 Arsitektur Deployment (Docker)

```
┌─────────────────────────────────────────┐
│              EasyPanel Host             │
│  ┌────────────────┐ ┌────────────────┐  │
│  │  samosir-app   │ │  samosir-db    │  │
│  │  ┌───────────┐ │ │  MySQL 8.0     │  │
│  │  │  Nginx    │ │ │                │  │
│  │  │  :80      │ │ │  :3306         │  │
│  │  ├───────────┤ │ └────────────────┘  │
│  │  │  PHP-FPM  │ │                     │
│  │  │  :9000    │ │                     │
│  │  ├───────────┤ │                     │
│  │  │  Reverb   │ │                     │
│  │  │  :8080    │ │                     │
│  │  ├───────────┤ │                     │
│  │  │  Queue    │ │                     │
│  │  │  Worker   │ │                     │
│  │  └───────────┘ │                     │
│  └────────────────┘                     │
└─────────────────────────────────────────┘
```

---

## 4. Peran Pengguna (User Roles)

| Peran | Kode | Deskripsi |
|---|---|---|
| **Administrator** | `admin` | Akses penuh ke seluruh modul, manajemen user, dan pengaturan sistem |
| **Syahbandar** | `syahbandar` | Approval kedatangan/keberangkatan/penimbangan, approval SPR, verifikasi dokumen |
| **Petugas Pelabuhan** | `petugas` | Input data operasional harian (kedatangan, keberangkatan, jasa, penimbangan) |
| **Kepala Pelabuhan** | `kepala_pelabuhan` | Akses read-only dashboard, laporan, dan monitoring. Tidak dapat menginput data |

### 4.1 Matriks Akses

| Modul | Admin | Syahbandar | Petugas | Kepala Pelabuhan |
|---|:---:|:---:|:---:|:---:|
| Dashboard | ✅ | ✅ | ✅ | ✅ |
| Posisi Kapal (Peta) | ✅ | ✅ | ✅ | ✅ |
| Data Master (Ikan, Dermaga) | ✅ | ✅ | ✅ | ✅ |
| Daftar Kapal | ✅ | ✅ | ✅ | ✅ |
| Approval Kapal | ✅ | ✅ | ✅ | ❌ |
| Input Kedatangan | ✅ | ✅ | ✅ | ❌ |
| Input Keberangkatan | ✅ | ✅ | ✅ | ❌ |
| Permohonan SPR | ✅ | ✅ | ✅ | ❌ |
| Penimbangan Ikan | ✅ | ✅ | ✅ | ✅ |
| Approval Penimbangan | ❌ | ✅ | ❌ | ❌ |
| Jasa (Peralatan/Ice/Air) | ✅ | ✅ | ✅ | ✅ |
| Laporan | ✅ | ✅ | ✅ | ✅ |
| Chat / Pesan | ✅ | ✅ | ✅ | ✅ |
| Manajemen User | ✅ | ❌ | ❌ | ❌ |
| Settings | ✅ | ❌ | ❌ | ❌ |
| FAQ | ✅ | ✅ | ✅ | ✅ |

---

## 5. Modul & Fitur Fungsional

### 5.1 Modul Dashboard

**Halaman:** `Dashboard/Index`

Dashboard menampilkan ringkasan operasional pelabuhan secara real-time:

- **Kartu Statistik:** Total kapal, kapal aktif, kapal pending, total user aktif, kedatangan hari ini, keberangkatan hari ini, kapal sandar (TAMBAT), kapal bongkar (BONGKAR).
- **Grafik Tren Aktivitas:** Kedatangan vs keberangkatan per periode (mingguan/bulanan/tahunan).
- **Distribusi Status Kapal:** Pie chart (approved/pending/rejected).
- **Distribusi Status Kedatangan:** Pie chart (tambat/bongkar/selesai).
- **Top 5 Dermaga:** Peringkat dermaga berdasarkan frekuensi kedatangan.
- **Top 5 Jenis Ikan:** Peringkat spesies ikan berdasarkan total berat tangkapan (kg).
- **Pendapatan Jasa:** Grafik pendapatan 6 bulan terakhir per kategori (Peralatan, Ice Cruiser, Air).
- **Tabel Terbaru:** 10 kedatangan dan keberangkatan terakhir.

### 5.2 Modul Posisi Kapal (Vessel Positions)

**Halaman:** `VesselPositions/Index`

- Peta interaktif (Leaflet.js) menampilkan posisi kapal yang sedang sandar di pelabuhan.
- Pengelompokan kapal berdasarkan lokasi dermaga (landing site) yang memiliki koordinat GPS.
- Logika penentuan status: jika tanggal kedatangan terakhir > keberangkatan terakhir → kapal di pelabuhan.
- Popup informasi: nama kapal, pemilik, GT, status, waktu kedatangan relatif.

### 5.3 Modul Data Master

#### 5.3.1 Jenis Ikan (Fish Species)
- **CRUD:** Create, Read, Update, Delete
- **Atribut:** Nama spesies, nama lokal, nama ilmiah, kategori, status aktif
- **Filter & Search:** Pencarian berdasarkan nama

#### 5.3.2 Dermaga (Landing Sites)
- **CRUD:** Create, Read, Update, Delete
- **Atribut:** Nama dermaga, alamat, jarak, latitude, longitude, tipe, status aktif
- **Integrasi:** Digunakan sebagai referensi di modul kedatangan, keberangkatan, dan peta

### 5.4 Modul Manajemen Kapal (Vessels)

**Halaman:** `Vessels/Index`, `Vessels/Create`, `Vessels/Edit`, `Vessels/Approval`

#### 5.4.1 Data Kapal
- **Atribut Utama:** Nama kapal, nama pemilik, nomor izin, GT (Gross Tonnage), alat tangkap, tanda selar, tipe kapal, daya mesin, LOA, panjang kapal
- **Dokumen Perizinan:** Tanggal SIPI, tanggal berakhir SIPI, nomor SIUP
- **Media:** Foto kapal (upload, max 10MB), QR Code
- **Status Perizinan:** Otomatis dihitung (Aktif / Expired) berdasarkan `sipi_end_date`
- **Soft Delete:** Data yang dihapus tetap tersimpan di database

#### 5.4.2 Approval Kapal
- Alur: `Pending` → `Approved` / `Rejected`
- Dicatat: siapa yang approve/reject dan kapan
- Filter berdasarkan status approval

#### 5.4.3 Manajemen Pengelola Kapal (Vessel Managers)
- Penugasan pengelola ke kapal (many-to-many relationship)
- Atribut pivot: is_primary, alamat, KTP (id_card), surat kuasa (authorization_letter)
- Alur approval pengelola: `Pending` → `Approved` / `Rejected`
- Satu kapal dapat memiliki satu pengelola utama (primary)

### 5.5 Modul Kedatangan (Arrivals)

**Halaman:** `Arrivals/Index`, `Arrivals/Create`, `Arrivals/Edit`

- **Input Data:** Kapal, asal, tanggal & waktu kedatangan, dermaga, mutu ikan, kualitas ikan, harga rata-rata, volume limbah, suhu ikan, suhu palka, catatan
- **Status Operasional:** `TAMBAT` → `BONGKAR` → `SELESAI`
- **Approval:** Status 0 (pending) → 1 (approved), dicatat oleh siapa dan kapan
- **Relasi:** Terhubung ke data tangkapan (arrival_catches) dan penimbangan (unloadings)
- **Filter:** Pencarian, status, rentang tanggal
- **Pagination:** 10 item per halaman

### 5.6 Modul Keberangkatan (Departures)

**Halaman:** `Departures/Index`, `Departures/Create`, `Departures/Edit`

- **Input Data:** Nomor, kapal, nama nakhoda, tujuan, jumlah ABK, tanggal & waktu keberangkatan, dermaga, syahbandar
- **Logistik Perbekalan:** Es, air, solar, oli, bensin, perbekalan lainnya (dalam satuan liter/kg)
- **Perhitungan Etmal:** Kalkulasi otomatis durasi sandar di pelabuhan (etmal_days sebagai float, etmal_hours sebagai varchar)
- **Status:** Floating status, unloading status, admin completion
- **Approval:** Alur yang sama dengan kedatangan
- **Tanda Tangan Digital:** Field signature untuk pengesahan dokumen

### 5.7 Modul SPR Keberangkatan (Surat Persetujuan Berlayar)

**Halaman:** `SprDepartures/Index`, `SprDepartures/Show`

- **Permohonan:** Diajukan oleh pengelola kapal (via mobile) atau petugas (via web)
- **Atribut:** Kapal, nakhoda, muatan, tanggal kedatangan/keberangkatan CP & fisik, rencana keberangkatan
- **Alur Status:** `pending` → `processed` (diverifikasi petugas) → `approved` / `rejected` (oleh syahbandar)
- **Notifikasi Otomatis:** Dikirim ke syahbandar saat SPR diteruskan, dan ke pengelola saat disetujui/ditolak

### 5.8 Modul Penimbangan Ikan (Unloadings)

**Halaman:** `Unloadings/Index`, `Unloadings/Create`, `Unloadings/Edit`

- **Input Data:** Kedatangan (arrival_id), nomor referensi, syahbandar, nama nakhoda, tanda pengenal, tanggal & waktu penimbangan, nomor urut, tanggal registrasi, kode BL, dermaga
- **Approval Syahbandar:** Hanya role `syahbandar` yang dapat melakukan approval penimbangan
- **Cetak:** Dokumen penimbangan dapat dicetak dalam format PDF
- **Filter:** Pencarian (nomor referensi, kode BL, nama kapal), status, rentang tanggal

### 5.9 Modul Jasa Pelabuhan (Services)

Tiga sub-modul jasa dengan alur yang seragam:

**Alur Status:** `order` (Pesanan) → `processed` (Diproses) → `completed` (Selesai) / `cancelled` (Dibatalkan)

#### 5.9.1 Jasa Peralatan (Equipment Services)
- **Atribut:** Nomor order, nama penyewa, kapal, tanggal layanan, waktu mulai/selesai, durasi, petugas, bendahara, total biaya, catatan
- **Item Peralatan:** Relasi one-to-many ke `equipment_items` (nama alat, jumlah, harga satuan, subtotal)
- **Perhitungan:** Otomatis menghitung total berdasarkan item
- **Cetak:** Order dan perhitungan biaya (PDF)

#### 5.9.2 Jasa Ice Cruiser
- Strukturnya sama dengan Equipment Services, dibedakan berdasarkan jenis item peralatan (`equipment_name = 'ice_cruiser'`)
- Memiliki halaman terpisah untuk kemudahan operasional

#### 5.9.3 Jasa Air Bersih (Water Services)
- **Atribut:** Nomor order, kapal, tanggal permintaan, tanggal layanan, volume (liter), harga satuan, total pembayaran, pemohon, petugas lapangan, bendahara
- **Perhitungan:** volume × harga = total pembayaran
- **Cetak:** Order dan perhitungan biaya (PDF)

### 5.10 Modul Laporan (Reports)

Semua laporan mendukung filter periode dan ekspor ke Excel & PDF.

| Laporan | Deskripsi |
|---|---|
| **Laporan Kedatangan** | Rekapitulasi kedatangan kapal per periode |
| **Laporan Keberangkatan** | Rekapitulasi keberangkatan kapal per periode |
| **Laporan Data Kapal** | Daftar kapal terdaftar beserta detail perizinan |
| **Laporan Tangkapan** | Rekapitulasi hasil tangkapan per jenis ikan dan berat |
| **Laporan Jasa** | Pendapatan dari ketiga layanan jasa (Peralatan, Ice Cruiser, Air), dengan tab "Keseluruhan" sebagai default |

**Catatan:** Tab "Keseluruhan" pada Laporan Jasa hanya menampilkan ringkasan agregat; ekspor Excel/PDF diblokir untuk tab ini guna mencegah inkonsistensi data.

### 5.11 Modul Chat / Pesan (Real-time Messaging)

**Halaman:** `Chat/Index`

- **Arsitektur:** Private conversation (1-on-1) menggunakan Laravel Reverb WebSocket
- **Fitur:**
  - Daftar percakapan dengan pesan terakhir dan timestamp
  - Kirim pesan teks
  - Upload file/gambar (max 10MB), otomatis deteksi tipe (image/file)
  - Edit pesan (hanya pengirim, ditandai `is_edited`)
  - Hapus pesan secara soft delete (hanya pengirim, ditandai `is_deleted`)
  - Pencarian dan pemilihan kontak untuk memulai percakapan baru
  - Mark as read (read_at pada pivot table)
- **Real-time Events:** `MessageSent`, `MessageUpdated`, `MessageDeleted` — broadcast via private channel `chat.{conversation_id}`
- **Cross-platform:** API Chat v1 tersedia untuk aplikasi mobile

### 5.12 Modul Manajemen User

**Halaman:** `Users/Index`, `Users/Create`, `Users/Edit`  
**Akses:** Hanya Admin

- **Atribut:** Nama, email, NIP, telepon, alamat, foto, tanda tangan digital, role, status aktif, KTP, surat kuasa
- **Operasi:** CRUD, ganti password, aktivasi/deaktivasi user
- **Soft Delete:** User yang dihapus tetap ada di database

### 5.13 Modul Profil

**Halaman:** `Profile/Show`

- Edit informasi profil (nama, email, foto, telepon, alamat)
- Upload/update tanda tangan digital (signature)
- Ganti password

### 5.14 Modul Pengaturan (Settings)

**Halaman:** `Settings/Index`  
**Akses:** Hanya Admin

- Pengaturan sistem berbasis key-value yang dikelompokkan (grouped)
- Tipe setting: text, textarea, number, select, toggle
- Caching otomatis (1 jam) untuk performa optimal
- Flush cache saat ada perubahan

### 5.15 Modul FAQ

**Halaman:** `Faq/Index`

Halaman FAQ statis dengan 4 kategori:
1. **Umum** — Penjelasan aplikasi, akses, dark mode
2. **Manajemen Kapal & Aktivitas** — Alur approval, kedatangan/keberangkatan, pencatatan tangkapan
3. **Pelayanan Jasa & Perhitungan** — Jenis jasa, alur status, cetak dokumen
4. **Laporan & Dashboard** — Fitur ekspor, data dashboard

### 5.16 Pencarian Global (Global Search)

- Pencarian lintas modul dari header aplikasi
- Cakupan: Kapal, Kedatangan, Keberangkatan, Jenis Ikan
- Hasil dikelompokkan per kategori (max 4 per grup)
- Klik hasil langsung navigasi ke halaman edit item

### 5.17 Notifikasi

- Notifikasi in-app menggunakan Laravel Notifications (database driver)
- Mark as read (individual & bulk)
- Notifikasi otomatis untuk: SPR diteruskan, SPR disetujui/ditolak, data baru diinput

---

## 6. API Mobile (RESTful API v1)

Base URL: `/api/v1`  
Autentikasi: **Laravel Sanctum** (Bearer Token)

### 6.1 Endpoint Tersedia

| Grup | Endpoint | Method |
|---|---|---|
| **Auth** | `/register`, `/login`, `/logout`, `/me` | POST, GET |
| **Profile** | `/profile/update`, `/profile/signature`, `/profile/change-password` | POST |
| **Notifications** | `/notifications`, `/notifications/{id}/read`, `/notifications/read-all` | GET, POST |
| **Vessels** | `/vessels/available`, `/vessels/summary`, `/vessels/my-vessels`, `/vessels/{id}` | GET |
| **Vessel Managers** | `/vessels/register-manager`, `/vessels/update-manager`, `/vessels/unregister-manager` | POST, PUT, DELETE |
| **Arrivals** | `/arrivals`, `/arrivals/{id}` | GET, POST |
| **Departures** | `/departures`, `/departures/{id}` | GET, POST |
| **SPR** | `/spr-departures` | GET, POST |
| **Services** | `/services`, `/services/water`, `/services/equipment`, `/services/ice-cruiser` | GET, POST |
| **Chat** | `/chat/conversations`, `/chat/messages`, `/chat/contacts` | GET, POST, PUT, DELETE |
| **Reference** | `/landing-sites`, `/syahbandars`, `/fish`, `/schedules` | GET |
| **Broadcasting** | `/broadcasting/auth` | POST |

### 6.2 Middleware Khusus

- `ensure.docs` — Memastikan user telah mengupload dokumen identitas (KTP & surat kuasa) sebelum dapat melakukan operasi tertentu (input kedatangan, keberangkatan, registrasi kapal, dll.)

---

## 7. Fitur Non-Fungsional

### 7.1 Keamanan
- CSRF protection pada semua form web
- Password hashing otomatis (bcrypt via Laravel casts)
- Soft delete untuk menjaga integritas data
- Security headers: X-Frame-Options, X-Content-Type-Options, X-XSS-Protection, Referrer-Policy
- Trust all proxies untuk environment reverse proxy (Docker/EasyPanel)
- Force HTTPS di production via `URL::forceScheme('https')`

### 7.2 Performa
- Gzip compression pada Nginx
- Static asset caching (30 hari), Vite build assets (1 tahun, immutable)
- Database query optimization dengan eager loading
- Settings caching (1 jam)
- PHP OPcache enabled di production

### 7.3 UI/UX
- Responsive design (mobile-friendly)
- Dark mode native support
- Smooth animations dan micro-interactions
- Pagination di semua daftar (10 item/halaman)
- Flash messages (success/error) setelah setiap operasi

### 7.4 Reliabilitas
- Queue worker untuk background jobs (database driver)
- Supervisord untuk auto-restart services
- Entrypoint script dengan auto-recovery: DB health check, migration retry, permission fixing
- Runtime `.env` generation dari environment variables

---

## 8. Model Data (Entity Relationship)

```
Users ─────────┬──── Vessels (many-to-many via vessel_managers)
               │
               ├──── Conversations (many-to-many via conversation_user)
               │         └──── Messages
               │
               ├──── Arrivals ────── ArrivalCatches ──── FishSpecies
               │         └──── Unloadings
               │
               ├──── Departures
               │
               ├──── SprDepartures
               │
               ├──── EquipmentServices ──── EquipmentItems
               │
               ├──── WaterServices
               │
               ├──── DeviceTokens
               │
               ├──── ActivityLogs
               │
               └──── Notifications

LandingSites ──── Arrivals, Departures, Unloadings

Settings (standalone key-value store)
```

---

## 9. Glosarium

| Istilah | Definisi |
|---|---|
| **SIPI** | Surat Izin Penangkapan Ikan |
| **SIUP** | Surat Izin Usaha Perikanan |
| **SPR/SPB** | Surat Persetujuan Berlayar |
| **GT** | Gross Tonnage (ukuran volume kapal) |
| **LOA** | Length Overall (panjang keseluruhan kapal) |
| **Etmal** | Durasi waktu kapal sandar di pelabuhan |
| **Tambat** | Status kapal yang sedang bersandar/berlabuh |
| **Bongkar** | Status kapal yang sedang melakukan pembongkaran hasil tangkapan |
| **BL Code** | Kode Bill of Lading |
| **PPN** | Pelabuhan Perikanan Nusantara |
| **Nakhoda** | Kapten/pemimpin kapal |
| **Syahbandar** | Pejabat pelabuhan yang berwenang mengeluarkan SPB |

---

## 10. Lampiran

### 10.1 Dokumen Terkait
- `PRD_Mobile_App.md` — PRD khusus aplikasi mobile pendamping
- `api_chat_v1_docs.md` — Dokumentasi lengkap API Chat v1
- `docker_setup_guide.md` — Panduan deployment Docker di EasyPanel

### 10.2 Environment Variables Kritis (Production)

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://[domain]
DB_HOST=[nama-service-mysql-easypanel]
BROADCAST_CONNECTION=reverb
REVERB_HOST=127.0.0.1        # Internal only
REVERB_PORT=8080              # Internal only
REVERB_SCHEME=http            # Internal only
VITE_REVERB_HOST=[domain]     # Public facing
VITE_REVERB_PORT=443          # Via Nginx proxy
VITE_REVERB_SCHEME=https      # Public facing
TELESCOPE_ENABLED=false
```
