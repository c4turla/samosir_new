<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { Head } from '@inertiajs/vue3'
import Sidebar from '../Components/Sidebar.vue'
import Header from '../Components/Header.vue'
import UserProfile from '../Components/UserProfile.vue'
import LogoutConfirmModal from '../Components/LogoutConfirmModal.vue'
import ToastNotification from '../Components/ToastNotification.vue'

import { menuConfig, ROLES } from '../Config/menuPermissions'

const page = usePage()

const user = computed(() => page.props.auth?.user)
const sidebarOpen = ref(window.innerWidth >= 1024)
const sidebarCollapsed = ref(false)
const darkMode = ref(localStorage.getItem('darkMode') === 'true')
const showLogoutModal = ref(false)

// Handle resize
const handleResize = () => {
  if (window.innerWidth < 1024) {
    // On mobile: sidebar is hidden by default, never collapsed (icons-only)
    sidebarOpen.value = false
    sidebarCollapsed.value = false
  } else {
    // On desktop: sidebar is open, not collapsed
    sidebarOpen.value = true
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
  
  return menuConfig.filter(menu => {
    // Check main menu access
    const hasAccess = menu.roles.includes(userRole);
    if (!hasAccess) return false;

    // If there are sub-menus, filter them too
    if (menu.items) {
      const filteredSubItems = menu.items.filter(sub => sub.roles.includes(userRole));
      // Only show main menu if it has accessible sub-menus
      if (filteredSubItems.length === 0) return false;
      
      // Update items with filtered sub-items (clone to avoid modifying original config)
      menu.items = filteredSubItems;
    }

    return true;
  });
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
    <Transition enter-active-class="transition-opacity duration-300" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition-opacity duration-300" leave-from-class="opacity-100" leave-to-class="opacity-0">
      <div
        v-if="sidebarOpen"
        class="fixed inset-0 bg-black/50 z-40 lg:hidden"
        @click="sidebarOpen = false"
      ></div>
    </Transition>

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
        'transition-all duration-300 min-h-screen flex flex-col',
        sidebarOpen && !sidebarCollapsed ? 'lg:ml-64' : sidebarOpen && sidebarCollapsed ? 'lg:ml-16' : 'ml-0'
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
        @toggle-sidebar-mobile="sidebarOpen = !sidebarOpen"
        @logout="handleLogout"
      />

      <!-- Spacer for fixed header -->
      <div class="h-12 flex-shrink-0"></div>

      <!-- Page Content -->
      <main class="p-4 sm:p-5 lg:p-6 flex-grow">
        <slot />
      </main>

      <!-- Footer -->
      <footer class="p-6 text-center border-t border-gray-100 dark:border-gray-800 bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm">
          <p class="text-[11px] text-gray-500 dark:text-gray-400 font-medium">
              © 2022-2026 <span class="font-bold text-blue-600 dark:text-blue-400">SAMOSIR v3.0</span> · Sistem Informasi Pelabuhan Perikanan Nusantara Sibolga
          </p>
          <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-1">
              Dibuat dengan ❤️ oleh <span class="font-semibold text-gray-600 dark:text-gray-300">Kendariweb.com</span>
          </p>
      </footer>
    </div>

    <!-- Logout Confirm Modal -->
    <LogoutConfirmModal
      :show="showLogoutModal"
      @close="closeLogoutModal"
      @confirm="closeLogoutModal"
    />

    <!-- Global Toast Notifications -->
    <ToastNotification />

  </div>
</template>
