<script setup>
import { ref, onMounted } from 'vue'
import { useForm, Head } from '@inertiajs/vue3'
import AppLayout from '../../Layouts/AppLayout.vue'

const props = defineProps({
  user: {
    type: Object,
    required: true
  }
})

const user = props.user

const profileForm = useForm({
  name: props.user.name,
  email: props.user.email,
  phone: props.user.phone || '',
  address: props.user.address || ''
})

const passwordForm = useForm({
  current_password: '',
  password: '',
  password_confirmation: ''
})

const signatureForm = useForm({
  signature: null
})

const updateProfile = () => {
  profileForm.post(route('profile.update'), {
    onSuccess: () => {
      profileForm.reset()
      profileForm.name = props.user.name
      profileForm.email = props.user.email
      profileForm.phone = props.user.phone || ''
      profileForm.address = props.user.address || ''
    }
  })
}

const updatePassword = () => {
  passwordForm.post(route('profile.password'), {
    onSuccess: () => {
      passwordForm.reset()
    }
  })
}

// Signature Pad Logic
const canvasRef = ref(null)
const ctx = ref(null)
const isDrawing = ref(false)
const lastX = ref(0)
const lastY = ref(0)

const initCanvas = () => {
  if (!canvasRef.value) return
  
  const canvas = canvasRef.value
  ctx.value = canvas.getContext('2d')
  
  // Set canvas size (visual size is 100%, actual resolution should be fixed)
  canvas.width = 600
  canvas.height = 200
  
  ctx.value.strokeStyle = '#000000'
  ctx.value.lineWidth = 2
  ctx.value.lineJoin = 'round'
  ctx.value.lineCap = 'round'
}

onMounted(() => {
  if (user.role === 'syahbandar') {
    initCanvas()
  }
})

const getMousePos = (e) => {
  const rect = canvasRef.value.getBoundingClientRect()
  const scaleX = canvasRef.value.width / rect.width
  const scaleY = canvasRef.value.height / rect.height
  
  return {
    x: ((e.clientX || (e.touches && e.touches[0].clientX)) - rect.left) * scaleX,
    y: ((e.clientY || (e.touches && e.touches[0].clientY)) - rect.top) * scaleY
  }
}

const startDrawing = (e) => {
  isDrawing.value = true
  const pos = getMousePos(e)
  lastX.value = pos.x
  lastY.value = pos.y
  
  // Prevent scrolling on touch
  if (e.touches) e.preventDefault()
}

const draw = (e) => {
  if (!isDrawing.value) return
  
  const pos = getMousePos(e)
  
  ctx.value.beginPath()
  ctx.value.moveTo(lastX.value, lastY.value)
  ctx.value.lineTo(pos.x, pos.y)
  ctx.value.stroke()
  
  lastX.value = pos.x
  lastY.value = pos.y
  
  if (e.touches) e.preventDefault()
}

const stopDrawing = () => {
  isDrawing.value = false
}

const clearSignature = () => {
  ctx.value.clearRect(0, 0, canvasRef.value.width, canvasRef.value.height)
}

const handleSaveSignature = () => {
  console.log('handleSaveSignature called');
  alert('Memulai proses penyimpanan tanda tangan...');
  
  if (!canvasRef.value) {
    alert('Error: Canvas tidak ditemukan di DOM.');
    return;
  }
  
  try {
    const dataUrl = canvasRef.value.toDataURL('image/png');
    console.log('Data URL generated, length:', dataUrl.length);
    
    signatureForm.signature = dataUrl;
    
    // Using direct URL to avoid any Ziggy route issues
    signatureForm.post('/profile/signature', {
      preserveScroll: true,
      onSuccess: () => {
        alert('Tanda tangan BERHASIL disimpan ke server!');
      },
      onError: (errors) => {
        console.error('Server errors:', errors);
        alert('Gagal menyimpan ke server: ' + JSON.stringify(errors));
      },
      onFinish: () => {
        console.log('Request finished');
      }
    });
  } catch (err) {
    console.error('Technical error:', err);
    alert('Terjadi kesalahan teknis: ' + err.message);
  }
}        
</script>

<template>
  <AppLayout>
    <Head title="Profile" />

    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Profile</h1>
      <p class="text-gray-600 dark:text-gray-400">Kelola informasi profil dan keamanan akun Anda</p>
    </div>

    <div class="space-y-12">
      <!-- Profile Information Form -->
      <form @submit.prevent="updateProfile">
        <div class="bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-900/5 dark:ring-gray-700 sm:rounded-xl">
          <div class="px-4 py-6 sm:px-8 sm:py-8 border-b border-gray-900/10 dark:border-gray-700">
            <h2 class="text-base font-semibold leading-7 text-gray-900 dark:text-white">Informasi Profil</h2>
            <p class="mt-1 text-sm leading-6 text-gray-600 dark:text-gray-400">Update informasi pribadi Anda</p>
          </div>

          <div class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 px-4 py-6 sm:px-8">
            <!-- Name -->
            <div class="sm:col-span-3">
              <label for="name" class="block text-sm/6 font-medium text-gray-900 dark:text-white">Nama Lengkap</label>
              <div class="mt-2">
                <input
                  id="name"
                  v-model="profileForm.name"
                  type="text"
                  placeholder="Masukkan nama lengkap"
                  class="block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 dark:text-white dark:bg-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm/6"
                />
              </div>
              <p v-if="profileForm.errors.name" class="mt-1 text-sm text-red-600 dark:text-red-400">
                {{ profileForm.errors.name }}
              </p>
            </div>

            <!-- Email -->
            <div class="sm:col-span-3">
              <label for="email" class="block text-sm/6 font-medium text-gray-900 dark:text-white">Email</label>
              <div class="mt-2">
                <input
                  id="email"
                  v-model="profileForm.email"
                  type="email"
                  placeholder="user@example.com"
                  class="block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 dark:text-white dark:bg-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm/6"
                />
              </div>
              <p v-if="profileForm.errors.email" class="mt-1 text-sm text-red-600 dark:text-red-400">
                {{ profileForm.errors.email }}
              </p>
            </div>

            <!-- Phone -->
            <div class="sm:col-span-3">
              <label for="phone" class="block text-sm/6 font-medium text-gray-900 dark:text-white">No. Telepon</label>
              <div class="mt-2">
                <input
                  id="phone"
                  v-model="profileForm.phone"
                  type="text"
                  placeholder="08123456789"
                  class="block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 dark:text-white dark:bg-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm/6"
                />
              </div>
              <p v-if="profileForm.errors.phone" class="mt-1 text-sm text-red-600 dark:text-red-400">
                {{ profileForm.errors.phone }}
              </p>
            </div>

            <!-- Address -->
            <div class="col-span-full">
              <label for="address" class="block text-sm/6 font-medium text-gray-900 dark:text-white">Alamat</label>
              <div class="mt-2">
                <textarea
                  id="address"
                  v-model="profileForm.address"
                  rows="3"
                  placeholder="Masukkan alamat lengkap"
                  class="block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 dark:text-white dark:bg-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm/6"
                ></textarea>
              </div>
              <p v-if="profileForm.errors.address" class="mt-1 text-sm text-red-600 dark:text-red-400">
                {{ profileForm.errors.address }}
              </p>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex items-center justify-end gap-x-6 border-t border-gray-900/10 dark:border-gray-700 px-4 py-4 sm:px-8">
            <button
              type="submit"
              :disabled="profileForm.processing"
              class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span v-if="profileForm.processing">Menyimpan...</span>
              <span v-else>Simpan Perubahan</span>
            </button>
          </div>
        </div>
      </form>

      <!-- Signature Section (Syahbandar Only) -->
      <div v-if="user.role === 'syahbandar'" class="bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-900/5 dark:ring-gray-700 sm:rounded-xl overflow-hidden">
        <div class="px-4 py-6 sm:px-8 sm:py-8 border-b border-gray-900/10 dark:border-gray-700">
          <h2 class="text-base font-semibold leading-7 text-gray-900 dark:text-white">Tanda Tangan Digital</h2>
          <p class="mt-1 text-sm leading-6 text-gray-600 dark:text-gray-400">Gunakan area di bawah ini untuk membuat tanda tangan Anda</p>
        </div>
        
        <div class="px-4 py-6 sm:px-8 space-y-6">
          <div v-if="user.signature_url" class="space-y-2">
            <label class="block text-sm font-medium text-gray-900 dark:text-white">Tanda Tangan Saat Ini:</label>
            <div class="bg-gray-50 dark:bg-gray-900 p-4 border border-gray-200 dark:border-gray-700 rounded-lg inline-block">
              <img :src="user.signature_url" alt="Signature" class="max-h-24 h-auto" />
            </div>
          </div>

          <div class="space-y-2">
            <label class="block text-sm font-medium text-gray-900 dark:text-white">Ubah/Buat Tanda Tangan Baru:</label>
            <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-900 cursor-crosshair overflow-hidden">
              <canvas
                ref="canvasRef"
                @mousedown="startDrawing"
                @mousemove="draw"
                @mouseup="stopDrawing"
                @mouseleave="stopDrawing"
                @touchstart="startDrawing"
                @touchmove="draw"
                @touchend="stopDrawing"
                class="w-full h-48 md:h-56"
              ></canvas>
            </div>
            <div class="flex justify-between items-center text-xs text-gray-500">
              <p>Tanda tangani di dalam area kotak dashed.</p>
              <button @click="clearSignature" type="button" class="text-red-600 hover:text-red-500 font-medium bg-red-50 dark:bg-red-900/20 px-2 py-1 rounded">
                Hapus & Ulangi
              </button>
            </div>
          </div>
        </div>

        <div class="flex items-center justify-end gap-x-6 border-t border-gray-900/10 dark:border-gray-700 px-4 py-4 sm:px-8 bg-gray-50/50 dark:bg-gray-900/50">
          <button
            @click="handleSaveSignature"
            type="button"
            :disabled="signatureForm.processing"
            class="rounded-md bg-green-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-600 disabled:opacity-50"
          >
            <span v-if="signatureForm.processing">Menyimpan...</span>
            <span v-else>Simpan Tanda Tangan Digital</span>
          </button>
        </div>
      </div>

      <!-- Change Password Form -->
      <form @submit.prevent="updatePassword">
        <div class="bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-900/5 dark:ring-gray-700 sm:rounded-xl">
          <div class="px-4 py-6 sm:px-8 sm:py-8 border-b border-gray-900/10 dark:border-gray-700">
            <h2 class="text-base font-semibold leading-7 text-gray-900 dark:text-white">Ganti Password</h2>
            <p class="mt-1 text-sm leading-6 text-gray-600 dark:text-gray-400">Pastikan password Anda kuat dan aman</p>
          </div>

          <div class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 px-4 py-6 sm:px-8">
            <!-- Current Password -->
            <div class="sm:col-span-3">
              <label for="current_password" class="block text-sm/6 font-medium text-gray-900 dark:text-white">Password Saat Ini</label>
              <div class="mt-2">
                <input
                  id="current_password"
                  v-model="passwordForm.current_password"
                  type="password"
                  placeholder="Masukkan password saat ini"
                  class="block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 dark:text-white dark:bg-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm/6"
                />
              </div>
              <p v-if="passwordForm.errors.current_password" class="mt-1 text-sm text-red-600 dark:text-red-400">
                {{ passwordForm.errors.current_password }}
              </p>
            </div>

            <!-- New Password -->
            <div class="sm:col-span-3">
              <label for="password" class="block text-sm/6 font-medium text-gray-900 dark:text-white">Password Baru</label>
              <div class="mt-2">
                <input
                  id="password"
                  v-model="passwordForm.password"
                  type="password"
                  placeholder="Minimal 8 karakter"
                  class="block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 dark:text-white dark:bg-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm/6"
                />
              </div>
              <p v-if="passwordForm.errors.password" class="mt-1 text-sm text-red-600 dark:text-red-400">
                {{ passwordForm.errors.password }}
              </p>
            </div>

            <!-- Password Confirmation -->
            <div class="col-span-full">
              <label for="password_confirmation" class="block text-sm/6 font-medium text-gray-900 dark:text-white">Konfirmasi Password Baru</label>
              <div class="mt-2">
                <input
                  id="password_confirmation"
                  v-model="passwordForm.password_confirmation"
                  type="password"
                  placeholder="Ulangi password baru"
                  class="block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 dark:text-white dark:bg-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm/6"
                />
              </div>
              <p v-if="passwordForm.errors.password_confirmation" class="mt-1 text-sm text-red-600 dark:text-red-400">
                {{ passwordForm.errors.password_confirmation }}
              </p>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex items-center justify-end gap-x-6 border-t border-gray-900/10 dark:border-gray-700 px-4 py-4 sm:px-8">
            <button
              type="submit"
              :disabled="passwordForm.processing"
              class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span v-if="passwordForm.processing">Mengubah...</span>
              <span v-else>Ganti Password</span>
            </button>
          </div>
        </div>
      </form>
    </div>
  </AppLayout>
</template>