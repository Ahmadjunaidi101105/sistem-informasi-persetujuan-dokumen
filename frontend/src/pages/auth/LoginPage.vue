<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useUiStore } from '@/stores/ui'
import AuthLayout from '@/layouts/AuthLayout.vue'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'

const router = useRouter()
const authStore = useAuthStore()
const uiStore = useUiStore()

const form = ref({
  email: '',
  password: ''
})

const errors = ref({})
const loading = ref(false)

const handleLogin = async () => {
  errors.value = {}
  loading.value = true
  
  try {
    await authStore.login(form.value)
    uiStore.showToast('Login berhasil', 'success')
    
    if (authStore.isPemohon) {
      router.push('/pemohon/dashboard')
    } else if (authStore.isPenilai) {
      router.push('/penilai/dashboard')
    } else {
      router.push('/')
    }
  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors || {}
    } else {
      uiStore.showToast(error.response?.data?.message || 'Login gagal, periksa email dan password Anda.', 'error')
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <AuthLayout>
    <div class="px-4 py-8 sm:px-10">
      <form class="space-y-6" @submit.prevent="handleLogin">
        <div>
          <label for="email" class="block text-sm font-medium leading-6 text-gray-900">Email address</label>
          <div class="mt-2">
            <input 
              id="email" 
              name="email" 
              type="email" 
              autocomplete="email" 
              required 
              v-model="form.email"
              :class="['block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset focus:ring-2 focus:ring-inset sm:text-sm sm:leading-6', errors.email ? 'ring-red-300 text-red-900 focus:ring-red-500' : 'text-gray-900 ring-gray-300 focus:ring-brand-700']" 
            />
            <p v-if="errors.email" class="mt-2 text-sm text-red-600">{{ errors.email[0] }}</p>
          </div>
        </div>

        <div>
          <label for="password" class="block text-sm font-medium leading-6 text-gray-900">Password</label>
          <div class="mt-2">
            <input 
              id="password" 
              name="password" 
              type="password" 
              autocomplete="current-password" 
              required 
              v-model="form.password"
              :class="['block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset focus:ring-2 focus:ring-inset sm:text-sm sm:leading-6', errors.password ? 'ring-red-300 text-red-900 focus:ring-red-500' : 'text-gray-900 ring-gray-300 focus:ring-brand-700']" 
            />
            <p v-if="errors.password" class="mt-2 text-sm text-red-600">{{ errors.password[0] }}</p>
          </div>
        </div>

        <div>
          <button 
            type="submit" 
            :disabled="loading"
            class="flex w-full justify-center rounded-md bg-brand-700 px-3 py-1.5 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-brand-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-700 disabled:opacity-70 disabled:cursor-not-allowed"
          >
            <LoadingSpinner v-if="loading" size="sm" class="mr-2 text-white flex-shrink-0" />
            <span :class="{'ml-2': loading}">Masuk</span>
          </button>
        </div>
      </form>

      <div class="mt-6 text-center">
        <p class="text-sm text-gray-500">
          Belum punya akun?
          {{ ' ' }}
          <router-link to="/register" class="font-semibold leading-6 text-brand-700 hover:text-brand-500">Daftar di sini</router-link>
        </p>
      </div>
    </div>
  </AuthLayout>
</template>
