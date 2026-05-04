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

import { menuConfig, ROLES } from '../Config/menuPermissions'

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
