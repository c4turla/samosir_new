<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { Head } from '@inertiajs/vue3'
import Sidebar from '../Components/Sidebar.vue'
import Header from '../Components/Header.vue'
import UserProfile from '../Components/UserProfile.vue'
import LogoutConfirmModal from '../Components/LogoutConfirmModal.vue'
import ToastNotification from '../Components/ToastNotification.vue'
import ChatWidget from '../Components/ChatWidget.vue'

const page = usePage()

const user = computed(() => page.props.auth?.user)
const sidebarOpen = ref(true)
const sidebarCollapsed = ref(window.innerWidth < 1024)
const darkMode = ref(localStorage.getItem('darkMode') === 'true')
const showLogoutModal = ref(false)

// Handle resize
const handleResize = () => {
  if (window.innerWidth < 1024) {
    sidebarCollapsed.value = true
    sidebarOpen.value = false
  } else {
    sidebarCollapsed.value = false
  }
}

onMounted(() => {
  handleResize()
  window.addEventListener('resize', handleResize)
})

onUnmounted(() => {
  window.removeEventListener('resize', handleResize)
})

// Watch dark mode changes
watch(darkMode, (newValue) => {
  localStorage.setItem('darkMode', newValue)
  if (newValue) {
    document.documentElement.classList.add('dark')
  } else {
    document.documentElement.classList.remove('dark')
  }
  // Emit custom event for child components
  window.dispatchEvent(new CustomEvent('darkModeChanged', { detail: { darkMode: newValue } }))
}, { immediate: true })

// Initialize dark mode
if (darkMode.value) {
  document.documentElement.classList.add('dark')
}

const menuItems = computed(() => {
  const userRole = user.value?.role;
  const items = [
    {
      title: 'Dashboard',
      icon: 'ri-dashboard-line',
      to: '/',
      open: false,
    },
    {
      title: 'Posisi Kapal',
      icon: 'ri-map-pin-2-line',
      to: '/vessel-positions',
      open: false,
    },
    {
      title: 'Data',
      icon: 'ri-database-2-line',
      items: [
        { title: 'Jenis Ikan', icon: 'ri-deepseek-line', to: '/fish-species' },
        { title: 'Dermaga', icon: 'ri-anchor-line', to: '/landing-sites' },
      ],
      open: false,
    },
    {
      title: 'Manajemen Kapal',
      icon: 'ri-ship-line',
      items: [
        { title: 'Daftar Kapal', icon: 'ri-ship-2-line', to: '/vessels' },
        ...(userRole === 'admin' || userRole === 'syahbandar' ? [{ title: 'Approval Kapal', icon: 'ri-check-double-line', to: '/vessels/approval' }] : []),
      ],
      open: false,
    },
    {
      title: 'Kedatangan',
      icon: 'ri-anchor-line',
      items: [
        { title: 'Daftar Kedatangan', icon: 'ri-anchor-line', to: '/arrivals' },
        ...(userRole !== 'kepala_pelabuhan' ? [{ title: 'Tambah Kedatangan', icon: 'ri-add-circle-line', to: '/arrivals/create' }] : []),
      ],
      open: false,
    },
    {
      title: 'Keberangkatan',
      icon: 'ri-sailboat-line',
      items: [
        { title: 'Daftar Keberangkatan', icon: 'ri-sailboat-line', to: '/departures' },
        ...(userRole !== 'kepala_pelabuhan' ? [
            { title: 'Tambah Keberangkatan', icon: 'ri-add-circle-line', to: '/departures/create' },
            { title: 'Permohonan SPR', icon: 'ri-file-info-line', to: '/spr-departures' }
        ] : []),
      ],
      open: false,
    },
    {
      title: 'Penimbangan Ikan',
      icon: 'ri-download-cloud-2-line',
      items: [
        { title: 'Daftar Penimbangan', icon: 'ri-list-check', to: '/unloadings' },
        ...(userRole === 'syahbandar' ? [{ title: 'Approval Penimbangan', icon: 'ri-check-double-line', to: '/approval' }] : []),
      ],
      open: false,
    },
    {
      title: 'Dokumen',
      icon: 'ri-file-list-3-line',
      to: '/documents',
      open: false,
    },
    {
      title: 'Laporan',
      icon: 'ri-bar-chart-line',
      items: [
        { title: 'Laporan Kedatangan', icon: 'ri-bar-chart-grouped-line', to: '/reports/arrivals' },
        { title: 'Laporan Keberangkatan', icon: 'ri-bar-chart-grouped-line', to: '/reports/departures' },
        { title: 'Laporan Data Kapal', icon: 'ri-ship-2-line', to: '/reports/vessels' },
        { title: 'Laporan Tangkapan', icon: 'ri-bar-chart-grouped-line', to: '/reports/catches' },
      ],
      open: false,
    },
  ]

  // Add admin-only menu items
  if (userRole === 'admin') {
    items.push({
      title: 'Manajemen User',
      icon: 'ri-user-settings-line',
      to: '/users',
      open: false,
    })
  }

  // Add settings menu for all users
  items.push({
    title: 'Settings',
    icon: 'ri-settings-4-line',
    to: '/settings',
    open: false,
  })

  return items
})

const currentPath = computed(() => page.props.ziggy?.location || window.location.pathname)

const handleLogout = () => {
  showLogoutModal.value = true
}

const closeLogoutModal = () => {
  showLogoutModal.value = false
}
</script>

<template>
  <div class="min-h-screen bg-gray-100 dark:bg-gray-900 transition-colors duration-300">
    <!-- Mobile Sidebar Overlay -->
    <div
      v-if="sidebarOpen"
      class="fixed inset-0 bg-black/50 z-20 lg:hidden"
      @click="sidebarOpen = false"
    ></div>

    <!-- Sidebar Component -->
    <Sidebar
      :menu-items="menuItems"
      :is-open="sidebarOpen"
      :is-collapsed="sidebarCollapsed"
      :current-path="currentPath"
      :user="user"
      @close="sidebarOpen = false"
      @toggle-collapse="sidebarCollapsed = !sidebarCollapsed"
    >
      <template #user-profile>
        <UserProfile :user="user" :is-collapsed="sidebarCollapsed" @logout="handleLogout" />
      </template>
    </Sidebar>

    <!-- Main Content -->
    <div
      :class="[
        'transition-all duration-300',
        sidebarOpen ? (sidebarCollapsed ? 'lg:ml-16' : 'lg:ml-64') : 'lg:ml-0'
      ]"
    >
      <!-- Header Component -->
      <Header
        :user="user"
        :dark-mode="darkMode"
        :is-sidebar-open="sidebarOpen"
        :is-sidebar-collapsed="sidebarCollapsed"
        @toggle-dark-mode="darkMode = !darkMode"
        @toggle-sidebar-collapse="sidebarCollapsed = !sidebarCollapsed"
        @logout="handleLogout"
      />

      <!-- Page Content -->
      <main class="p-4 sm:p-6 lg:p-8">
        <slot />
      </main>
    </div>

    <!-- Logout Confirm Modal -->
    <LogoutConfirmModal
      :show="showLogoutModal"
      @close="closeLogoutModal"
      @confirm="closeLogoutModal"
    />

    <!-- Global Toast Notifications -->
    <ToastNotification />

    <!-- Chat Widget -->
    <ChatWidget />
  </div>
</template>
