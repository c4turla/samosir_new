# Product Requirements Document (PRD) - Aplikasi Mobile SAMOSIR

## 1. Pendahuluan
**Nama Produk:** SAMOSIR Mobile App (Sistem Manajemen Kapal dan Pelabuhan)
**Tujuan Dokumen:** Menjabarkan spesifikasi, fungsionalitas, dan kebutuhan teknis untuk pengembangan aplikasi mobile pendamping dari sistem web SAMOSIR yang sudah berjalan.
**Latar Belakang:** Saat ini SAMOSIR telah beroperasi melalui platform web (Stack Vue 3 + Laravel 11). Untuk memaksimalkan mobilitas pengguna—terutama petugas di lapangan dan nahkoda/manajer kapal—diperlukan akses cepat dan real-time melalui perangkat smartphone untuk mempercepat pelaporan, persetujuan (approval), pengecekan on-site, dan komunikasi.

## 2. Sasaran Pengguna (Target Audience)
Aplikasi mobile ini akan difokuskan untuk 2 (dua) peran utama, mengingat keterbatasan mobilitas mereka di lapangan:
1. **Manajer/Pemilik Kapal (Vessel Manager):** Pengguna yang bertugas mengelola armadanya, memantau masa aktif dokumen izin (SIPI/SIUP), melaporkan kedatangan, keberangkatan, dan melaporkan hasil tangkapan.
2. **Petugas Pelabuhan / Syahbandar (Port Official / Authority):** Pengguna yang bertugas memantau lalu lintas pelabuhan, melakukan inspeksi fisik (scan QR kapal di lapangan), dan menyetujui (approve/reject) dokumen serta aktivitas kapal.

## 3. Platform & Teknologi
- **Framework Mobile:** React Native atau Flutter (cross-platform Android & iOS).
- **Backend API:** Menggunakan RESTful API dengan **Laravel Sanctum Token** ke backend Laravel 11.
- **Real-time Feature:** Integrasi client dengan **Laravel Reverb (WebSockets)** untuk fitur Chat Real-time lintas platform (Web & Mobile) dan Push Notification.
- **Hardware Integration:** Akses Modul Kamera untuk **Pemindai/Scanner QR Code**.

## 4. Fitur Utama & Kebutuhan Fungsional (Functional Requirements)

### 4.1. Modul Autentikasi & Profil (User Management)
- **Login:** Pengguna masuk menggunakan kredensial yang sama dengan versi web. Autentikasi akan men-generate API Token.
- **Profil Pengguna:** Melihat profil dasar, ganti password, dan input/edit tanda tangan digital (signature) secara mobile.

### 4.2. Dashboard Mobile (Ringkasan Cepat)
- **Manajer Kapal:** Widget ringkasan status armada miliknya. Notifikasi dokumen (SIPI/SIUP) yang *expired* atau hampir habis masa berlakunya. Shortcut *Lapor Kedatangan* & *Keberangkatan*.
- **Petugas Pelabuhan:** Statistik harian (kedatangan/keberangkatan hari ini, kapal sandar) dan *To-Do List* persetujuan (*Approval* kedatangan/keberangkatan).

### 4.3. Manajemen Kapal & Identifikasi QR (Vessel Data & QR)
- **Daftar Kapal:** Menampilkan list kapal dengan detail (Nama, GT, LOA, dokumen SIPI/SIUP, status masa aktif).
- **Tampilkan QR Code (Manajer/Nahkoda):** Menampilkan QR Code unik masing-masing kapal di layar HP untuk discan oleh petugas.
- **QR Code Scanner (Petugas):** Fitur kamera untuk memindai QR code kapal. Setelah discan, layar HP petugas langsung menampilkan detail lengkap kapal dan status izinnya.

### 4.4. Modul Pelaporan & Aktivitas (Port Activities)
- **Lapor Kedatangan (Arrivals):** Form mobile untuk mengisi data kedatangan, estimasi tiba, pelabuhan asal. Petugas dapat men-approve/reject via Mobile.
- **Lapor Keberangkatan (Departures):** Permohonan izin berangkat kapal.
- **Bongkar Muat & Tangkapan (Unloadings & Catches):** Form input hasil tangkapan berdasarkan master data *fish species*.

### 4.5. Komunikasi & Real-Time (Chat & Notifikasi)
- **Push Notifications:** Pemberitahuan otomatis di HP saat ada pengajuan baru (bagi petugas) atau perubahan status dokumen (bagi manajer kapal).
- **Live Chat (Konsultasi Petugas vs Publik/Manajer):** UI Chat (seperti WhatsApp) untuk berkonsultasi langsung antara Manajer Kapal dengan Petugas/Master Pelabuhan.

## 5. Contoh Alur Pengguna (Key User Flows)

**Flow 1: Inspeksi Kapal On-Site (Oleh Petugas)**
1. Petugas Pelabuhan berjalan ke dermaga.
2. Buka aplikasi SAMOSIR -> Tap menu **"Scan QR Kapal"**.
3. Arahkan kamera ke QR Code yang ditunjukkan nahkoda.
4. Layar otomatis menampilkan rincian Kapal dan Alert validitas dokumen (SIPI).

**Flow 2: Pengajuan Kedatangan (Oleh Manajer Kapal)**
1. Manajer Kapal tap **"Lapor Kedatangan"** -> Pilih Kapalnya.
2. Isi tanggal & estimasi tiba -> Submit.
3. Petugas menerima **Push Notification** di HP -> Tap Notifikasi.
4. Petugas review form -> Tekan **"Approve"**.
5. Manajer kapal mendapat balasan notifikasi disetujui.

## 6. Kebutuhan Non-Fungsional 
- **Security:** API menggunakan HTTPS/SSL. Token Sanctum disimpan di *Secure Storage* secara aman.
- **Performance:** App size ringan (<50MB). Menggunakan *local caching* re-usable data.
- **Real-time Synchronization:** Data dan chat tersinkron otomatis menggunakan Laravel Reverb tanpa perlu *manual refresh*.

## 7. Fase Roadmap Rilis (Milestones)
- **Fase 1 (MVP):** API readiness, Auth Laravel Sanctum, Mobile Dashboard, Daftar Kapal, Fitur QR Code (Generate & Scan).
- **Fase 2:** Pelaporan Kedatangan, Keberangkatan, Catatan Tangkapan, Approval Petugas.
- **Fase 3:** WebSockets Chat System, Integrasi Push Notifications, Rilis Store.
