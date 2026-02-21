# Authentication System - SAMOSIR

## Overview
Sistem authentication sudah berhasil diimplementasikan untuk melindungi akses ke dashboard dan fitur-fitur aplikasi SAMOSIR.

## Fitur yang Diimplementasikan

### 1. Login System
- **Halaman Login**: `resources/js/Pages/Auth/Login.vue`
- **Controller**: `app/Http/Controllers/AuthController.php`
- **Routes**:
  - GET `/login` - Tampilkan halaman login
  - POST `/login` - Proses login
  - POST `/logout` - Proses logout (memerlukan authentication)

### 2. Authentication Middleware
- **Middleware**: `app/Http/Middleware/EnsureUserIsAuthenticated.php`
- Menggunakan Laravel's built-in `auth` middleware
- Melindungi semua route yang memerlukan authentication

### 3. User Data Sharing
- Data user di-share ke semua Inertia pages melalui `HandleInertiaRequests`
- Data yang tersedia: `auth.user` (name, email, role)

### 4. Layout Updates
- `resources/js/Layouts/AppLayout.vue` sudah diupdate untuk:
  - Menampilkan nama dan role user di sidebar dan header
  - Tombol logout yang berfungsi
  - Inisial user yang dinamis

### 5. Route Protection
```php
// Guest routes (tanpa authentication)
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.store');

// Protected routes (memerlukan authentication)
Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
```

## User Accounts
Database seeder sudah membuat 3 user untuk testing:

| Email | Password | Role |
|-------|----------|------|
| admin@samosir.com | password | admin |
| petugas@samosir.com | password | petugas |
| syahbandar@samosir.com | password | syahbandar |

## Cara Testing

### 1. Akses Aplikasi
```
http://localhost:8002/
```
Akan otomatis di-redirect ke `/login` karena belum authenticated.

### 2. Login
1. Buka halaman login: `http://localhost:8002/login`
2. Masukkan email dan password (lihat tabel User Accounts di atas)
3. Klik tombol "Masuk"

### 3. Dashboard
Setelah login berhasil, user akan:
- Di-redirect ke dashboard
- Melihat nama dan role di sidebar dan header
- Dapat mengakses semua fitur dashboard
- Dapat logout dengan klik ikon logout di sidebar

### 4. Logout
Klik ikon logout di sidebar bagian bawah, akan:
- Logout user
- Hapus session
- Redirect ke halaman login

## Flow Authentication

```
User belum login
    ↓
Akses route protected (misal: /)
    ↓
Middleware 'auth' mendeteksi tidak ada user yang authenticated
    ↓
Redirect ke /login
    ↓
User mengisi form login
    ↓
AuthController::login() melakukan Auth::attempt()
    ↓
Jika berhasil: create session, redirect ke dashboard
Jika gagal: redirect kembali ke login dengan error
```

## Fitur Login Page
- Form login dengan validasi
- Support "Ingat saya" (remember me)
- Error handling untuk invalid credentials
- Loading state saat proses login
- Dark mode support
- Responsive design

## Keamanan
- Password di-hash menggunakan bcrypt
- Session-based authentication
- CSRF protection aktif
- Route protection dengan middleware
- Invalid session akan redirect ke login

## Next Steps
Untuk development selanjutnya, dapat ditambahkan:
1. Password reset functionality
2. Email verification
3. Role-based access control (RBAC)
4. Two-factor authentication (2FA)
5. Rate limiting untuk login attempts
6. Activity logging untuk login/logout

## Troubleshooting

### Login gagal
- Pastikan email dan password benar
- Cek database apakah user sudah ada
- Cek apakah password di-hash dengan benar

### Tidak bisa akses dashboard setelah login
- Cek apakah middleware 'auth' sudah diapply
- Cek apakah session tersimpan dengan benar
- Clear browser cache dan cookies

### Session expired
- Default session timeout Laravel adalah 2 jam
- Bisa diubah di `config/session.php`