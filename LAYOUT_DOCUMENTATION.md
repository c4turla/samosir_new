# Documentation - Layout AppLayout

## Ringkasan
Layout AppLayout adalah layout modern dengan sidebar dan header yang responsif, mendukung dark mode, dan menggunakan Tailwind CSS.

## Fitur Utama

### 1. **Sidebar Navigation**
- **Responsive:** Sidebar bisa di-toggle (buka/tutup)
- **Menu dengan Submenu:** Mendukung struktur menu bertingkat
- **Active State:** Highlight menu yang sedang aktif
- **Mobile Friendly:** Overlay pada mobile saat sidebar terbuka

### 2. **Modern Header**
- **Hamburger Menu:** Toggle sidebar
- **Search Bar:** Search input dengan icon
- **Dark Mode Toggle:** Switch antara light dan dark mode
- **Notifications:** Tombol notifikasi dengan badge
- **User Menu:** Avatar dan dropdown user

### 3. **Dark Mode**
- **Persistent:** Status dark mode disimpan di localStorage
- **Smooth Transition:** Perubahan warna smooth dengan animasi
- **Full Support:** Semua elemen mendukung dark mode

### 4. **Menu Navigasi**

Menu sidebar yang tersedia:

1. **Dashboard** (`/`)
   - Halaman utama

2. **Manajemen Kapal**
   - Daftar Kapal (`/vessels`)
   - Approval Kapal (`/vessels/pending`)

3. **Kedatangan**
   - Daftar Kedatangan (`/arrivals`)
   - Tambah Kedatangan (`/arrivals/create`)

4. **Keberangkatan**
   - Daftar Keberangkatan (`/departures`)
   - Tambah Keberangkatan (`/departures/create`)

5. **Unloading** (`/unloadings`)
   - Manajemen unloading

6. **Dokumen** (`/documents`)
   - Manajemen dokumen

7. **Laporan**
   - Laporan Kedatangan (`/reports/arrivals`)
   - Laporan Keberangkatan (`/reports/departures`)
   - Laporan Tangkapan (`/reports/catches`)

8. **Settings** (`/settings`)
   - Pengaturan aplikasi

## Cara Menggunakan Layout

### 1. Import Layout di Halaman Vue

```vue
<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';

defineOptions({
  layout: AppLayout
});
</script>

<template>
  <Head title="Judul Halaman" />
  
  <div>
    <!-- Konten halaman di sini -->
  </div>
</template>
```

### 2. Menggunakan Dark Mode di Custom Component

```vue
<script setup>
import { ref } from 'vue'

const isDark = ref(localStorage.getItem('darkMode') === 'true')

// Toggle dark mode
const toggleDarkMode = () => {
  isDark.value = !isDark.value
  localStorage.setItem('darkMode', isDark.value)
  if (isDark.value) {
    document.documentElement.classList.add('dark')
  } else {
    document.documentElement.classList.remove('dark')
  }
}
</script>

<template>
  <div :class="{ 'dark': isDark }">
    <!-- Konten -->
  </div>
</template>
```

## Styling dengan Tailwind

### Light Mode (Default)
```html
<div class="bg-white text-gray-900">Content</div>
```

### Dark Mode
```html
<div class="bg-white dark:bg-gray-800 text-gray-900 dark:text-white">Content</div>
```

### Responsive Classes
```html
<!-- Mobile: full width, Tablet: 1/2, Desktop: 1/4 -->
<div class="w-full md:w-1/2 lg:w-1/4">Content</div>
```

## Icons (Remix Icons)

Layout menggunakan Remix Icons. Contoh penggunaan:

```html
<!-- Dashboard Icon -->
<i class="ri-dashboard-line text-xl"></i>

<!-- Ship Icon -->
<i class="ri-ship-line text-xl"></i>

<!-- Settings Icon -->
<i class="ri-settings-4-line text-xl"></i>
```

Daftar icon lengkap: https://remixicon.com/

## Struktur Komponen

### AppLayout.vue
```
AppLayout
├── Sidebar
│   ├── Logo Section
│   ├── Navigation Menu
│   └── User Section
├── Header
│   ├── Left Section (Toggle + Search)
│   └── Right Section (Dark Mode + Notifications + User)
└── Main Content
    └── Page Content (Slot)
```

## Responsive Breakpoints

- **Mobile:** `< 1024px` (Sidebar hidden by default)
- **Tablet:** `768px - 1023px`
- **Desktop:** `≥ 1024px` (Sidebar always visible)

## Dark Mode Colors

### Background Colors
- Light: `bg-gray-100` → Dark: `bg-gray-900`
- Surface: `bg-white` → Dark: `bg-gray-800`
- Cards: `bg-white` → Dark: `bg-gray-800`

### Text Colors
- Primary: `text-gray-900` → Dark: `text-white`
- Secondary: `text-gray-600` → Dark: `text-gray-300`
- Muted: `text-gray-500` → Dark: `text-gray-400`

### Border Colors
- Default: `border-gray-200` → Dark: `border-gray-700`
- Hover: `border-gray-300` → Dark: `border-gray-600`

## Tips dan Best Practices

### 1. Gunakan Responsive Classes
Selalu gunakan responsive classes untuk mobile-friendly:
```vue
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
  <!-- Cards -->
</div>
```

### 2. Support Dark Mode
Pastikan setiap komponen mendukung dark mode:
```vue
<div class="bg-white dark:bg-gray-800 rounded-lg shadow">
  <!-- Content -->
</div>
```

### 3. Gunakan Transition
Tambahkan transisi untuk smooth experience:
```vue
<div class="transition-all duration-300">
  <!-- Content -->
</div>
```

### 4. Semantic HTML
Gunakan elemen semantic:
- `<header>` untuk header
- `<aside>` untuk sidebar
- `<main>` untuk konten utama
- `<nav>` untuk navigasi

## Troubleshooting

### Dark Mode Tidak Berfungsi
Pastikan:
1. `@variant dark` sudah ditambahkan di `resources/css/app.css`
2. Class `dark` ditambahkan ke `<html>` element
3. Menggunakan `dark:` prefix di Tailwind classes

### Sidebar Tidak Responsive
Pastikan:
1. Menggunakan `lg:` prefix untuk desktop-specific styles
2. Breakpoint sudah dikonfigurasi dengan benar
3. JavaScript untuk toggle sidebar berfungsi

### Icons Tidak Muncul
Pastikan:
1. Remix Icons sudah diinstall: `npm install remixicon`
2. Import CSS di `resources/js/app.js`: `import 'remixicon/fonts/remixicon.css'`

## Dependencies

- **Tailwind CSS 4.0** - Framework CSS
- **Remix Icons** - Icon library
- **Vue 3** - JavaScript framework
- **Inertia.js** - SPA tanpa API terpisah

## Contoh Halaman Lengkap

```vue
<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';

defineOptions({
  layout: AppLayout
});

const items = ref([
  { name: 'Item 1', status: 'Active' },
  { name: 'Item 2', status: 'Inactive' },
]);
</script>

<template>
  <Head title="Contoh Halaman" />
  
  <div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
        Judul Halaman
      </h1>
      <p class="text-gray-600 dark:text-gray-400 mt-2">
        Deskripsi halaman
      </p>
    </div>

    <!-- Content Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div v-for="item in items" :key="item.name" 
           class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 transition-all hover:shadow-lg">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
          {{ item.name }}
        </h3>
        <span :class="[
          'px-3 py-1 rounded-full text-sm',
          item.status === 'Active' 
            ? 'bg-green-100 text-green-800' 
            : 'bg-red-100 text-red-800'
        ]">
          {{ item.status }}
        </span>
      </div>
    </div>
  </div>
</template>
```

## Support

Untuk pertanyaan atau masalah, hubungi tim development.