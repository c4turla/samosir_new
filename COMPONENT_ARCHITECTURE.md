# Component Architecture - SAMOSIR

## Overview
Aplikasi SAMOSIR menggunakan arsitektur komponen Vue.js yang modular dan maintainable. Layout utama (AppLayout) telah dipecah menjadi komponen-komponen kecil yang masing-masing memiliki tanggung jawab spesifik.

## Struktur Komponen

```
resources/js/
├── Layouts/
│   └── AppLayout.vue           # Layout utama yang menggabungkan semua komponen
├── Components/
│   ├── Sidebar.vue             # Komponen sidebar navigasi
│   ├── Header.vue              # Komponen header/top bar
│   └── UserProfile.vue         # Komponen profile user dengan logout
└── Pages/
    ├── Auth/
    │   └── Login.vue           # Halaman login
    └── Dashboard/
        └── Index.vue           # Halaman dashboard
```

## Komponen Utama

### 1. AppLayout.vue (Layout Container)
**Lokasi**: `resources/js/Layouts/AppLayout.vue`

**Tanggung Jawab**:
- Mengelola state global (dark mode, sidebar state)
- Menggabungkan semua sub-komponen
- Mengelola data menu navigation
- Menyediakan slot untuk konten page

**State**:
```javascript
{
  user: Computed,           // Data user dari Inertia props
  sidebarOpen: Boolean,      // Status buka/tutup sidebar (mobile)
  sidebarCollapsed: Boolean, // Status collapse sidebar (desktop)
  darkMode: Boolean,        // Status dark mode
  menuItems: Array,         // Daftar menu navigasi
  currentPath: Computed     // Path URL saat ini
}
```

**Emits**: Tidak ada emit langsung, hanya menghandle emits dari child components

---

### 2. Sidebar.vue (Navigasi Kiri)
**Lokasi**: `resources/js/Components/Sidebar.vue`

**Tanggung Jawab**:
- Menampilkan logo dan nama aplikasi
- Menampilkan menu navigasi
- Menangani expand/collapse menu dengan subitems
- Menampilkan tombol close untuk mobile
- Menyediakan slot untuk user profile

**Props**:
```javascript
{
  menuItems: Array,      // Daftar menu navigasi (required)
  isOpen: Boolean,       // Status sidebar terbuka
  isCollapsed: Boolean,  // Status sidebar di-collapse
  currentPath: String,   // Path URL saat ini
  user: Object          // Data user (opsional)
}
```

**Emits**:
- `@close` - Menutup sidebar (mobile)
- `@toggle-collapse` - Toggle collapse sidebar (desktop)

**Slots**:
- `#user-profile` - Slot untuk komponen UserProfile

---

### 3. Header.vue (Top Bar)
**Lokasi**: `resources/js/Components/Header.vue`

**Tanggung Jawab**:
- Menampilkan search bar
- Menampilkan tombol toggle dark mode
- Menampilkan tombol notifications
- Menampilkan informasi user (nama, role, avatar)
- Menampilkan tombol toggle sidebar collapse

**Props**:
```javascript
{
  user: Object,              // Data user
  darkMode: Boolean,         // Status dark mode
  isSidebarOpen: Boolean,     // Status sidebar terbuka
  isSidebarCollapsed: Boolean // Status sidebar di-collapse
}
```

**Emits**:
- `@toggle-dark-mode` - Toggle dark mode
- `@toggle-sidebar-collapse` - Toggle sidebar collapse

---

### 4. UserProfile.vue (Profile di Sidebar)
**Lokasi**: `resources/js/Components/UserProfile.vue`

**Tanggung Jawab**:
- Menampilkan avatar user dengan inisial
- Menampilkan nama dan role user
- Menangani fungsi logout

**Props**:
```javascript
{
  user: Object,       // Data user
  isCollapsed: Boolean // Status sidebar di-collapse
}
```

**Emits**: Tidak ada (menghandle logout secara internal)

---

## Data Flow

### 1. User Data Flow
```
HandleInertiaRequests (PHP)
    ↓ share(auth.user)
Inertia props (auth.user)
    ↓
AppLayout (computed user)
    ↓ :user
Sidebar & Header & UserProfile
    ↓
Display user info
```

### 2. Dark Mode Flow
```
AppLayout (darkMode ref + localStorage)
    ↓ :dark-mode + @toggle-dark-mode
Header component
    ↓
User clicks toggle
    ↓ @toggle-dark-mode
AppLayout updates state + localStorage
    ↓
Updates document class (dark mode CSS)
```

### 3. Sidebar Collapse Flow
```
AppLayout (sidebarCollapsed ref)
    ↓ :is-collapsed + @toggle-collapse
Sidebar & Header components
    ↓
User clicks collapse button
    ↓ @toggle-collapse
AppLayout updates state
    ↓
Layout margin adjusts (lg:ml-16 vs lg:ml-64)
```

### 4. Logout Flow
```
UserProfile component
    ↓
User clicks logout button
    ↓
form.post(route('logout'))
    ↓
AuthController::logout()
    ↓
Auth::logout()
    ↓
Redirect to /login
```

## Menu Items Structure

```javascript
const menuItems = [
  // Single menu item
  {
    title: 'Dashboard',        // Label menu
    icon: 'ri-dashboard-line', // Icon class (RemixIcon)
    to: '/',                  // Route path
    open: false               // Status expand/collapse (untuk submenus)
  },
  
  // Menu dengan subitems
  {
    title: 'Manajemen Kapal',
    icon: 'ri-ship-line',
    items: [                 // Array subitems
      {
        title: 'Daftar Kapal',
        icon: 'ri-ship-2-line',
        to: '/vessels'
      }
    ],
    open: false
  }
]
```

## Best Practices

### 1. Single Responsibility Principle
Setiap komponen hanya memiliki satu tanggung jawab:
- **Sidebar**: Menangani navigasi
- **Header**: Menangani header UI dan user info
- **UserProfile**: Menangani display user dan logout
- **AppLayout**: Menggabungkan semua dan mengelola state global

### 2. Props Down, Events Up
Data mengalir dari parent ke child via props:
```
AppLayout → Sidebar/Header/UserProfile (props)
```

Events mengalir dari child ke parent via emits:
```
Sidebar/Header → AppLayout (emits)
```

### 3. Computed vs Ref
- **Ref**: Digunakan untuk state yang berubah (darkMode, sidebar state)
- **Computed**: Digunakan untuk derived state (user, currentPath)

### 4. Slot Pattern
Menggunakan slot untuk komponen yang fleksibel:
```vue
<Sidebar>
  <template #user-profile>
    <UserProfile />
  </template>
</Sidebar>
```

### 5. Scoped Styles
Setiap komponen menggunakan `<style scoped>` untuk mengisolasi CSS:
```vue
<style scoped>
/* Styles hanya berlaku untuk komponen ini */
</style>
```

## Keuntungan Arsitektur Ini

### 1. Maintainability
- Setiap komponen memiliki file sendiri
- Mudah mencari dan memperbaiki bug
- Perubahan pada satu bagian tidak mempengaruhi yang lain

### 2. Reusability
- Komponen dapat digunakan di layout lain jika diperlukan
- UserProfile dapat digunakan di tempat lain jika perlu

### 3. Testability
- Setiap komponen dapat di-test secara terpisah
- Mocking props lebih mudah

### 4. Performance
- Komponen hanya re-render jika props/state berubah
- Vue's reactivity system optimal

### 5. Collaboration
- Tim developer dapat bekerja pada komponen berbeda secara paralel
- Merge conflicts berkurang

## Cara Menambahkan Menu Baru

Di `AppLayout.vue`, tambahkan item baru ke `menuItems`:

```javascript
const menuItems = ref([
  // ... menu existing
  {
    title: 'Menu Baru',
    icon: 'ri-new-icon-line',
    to: '/new-route',
    open: false,
  },
  // Atau dengan subitems:
  {
    title: 'Menu Baru',
    icon: 'ri-new-icon-line',
    items: [
      { title: 'Submenu 1', icon: 'ri-icon-1', to: '/route-1' },
      { title: 'Submenu 2', icon: 'ri-icon-2', to: '/route-2' },
    ],
    open: false,
  },
])
```

## Cara Menambahkan Fitur Baru di Header

1. Tambahkan prop ke `Header.vue` jika perlu data dari parent
2. Tambahkan emit jika perlu mengirim event ke parent
3. Implementasikan logika di `Header.vue`
4. Handle emit di `AppLayout.vue`

## Troubleshooting

### Menu tidak responsive saat collapsed
Pastikan prop `isCollapsed` diterima dengan benar di semua komponen.

### Dark mode tidak tersimpan
Cek `localStorage` di browser dan watch di `AppLayout.vue`.

### Logout tidak berfungsi
Pastikan route `logout` ada di `routes/web.php` dan ziggy.js sudah di-generate.

### Sidebar tidak muncul di mobile
Cek prop `isOpen` dan overlay overlay di `AppLayout.vue`.

## Future Improvements

1. **Loading States**: Tambahkan loading state untuk async operations
2. **Error Boundaries**: Handle error secara graceful
3. **Accessibility**: Tambahkan ARIA labels dan keyboard navigation
4. **Internationalization**: Support multiple languages
5. **Theme Customization**: Allow custom colors and layouts
6. **Notifications System**: Implement real notifications (bukan placeholder)
7. **Search Functionality**: Implement actual search functionality