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
  name: '',
  email: '',
  phone: '',
  company_name: '',
  company_address: '',
  password: '',
  password_confirmation: ''
})

const errors = ref({})
const loading = ref(false)

const handleRegister = async () => {
  errors.value = {}
  loading.value = true
  
  try {
    await authStore.register(form.value)
    uiStore.showToast('Pendaftaran berhasil, silakan masuk', 'success')
    router.push('/login')
  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors || {}
      uiStore.showToast('Periksa kembali data Anda', 'error')
    } else {
      uiStore.showToast(error.response?.data?.message || 'Pendaftaran gagal', 'error')
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <AuthLayout>
    <div class="px-4 py-8 sm:px-10">
      <form class="space-y-6" @submit.prevent="handleRegister">
        
        <div>
          <label for="name" class="block text-sm font-medium leading-6 text-gray-900">Nama Lengkap</label>
          <div class="mt-2">
            <input id="name" name="name" type="text" required v-model="form.name" :class="['block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset focus:ring-2 focus:ring-inset sm:text-sm sm:leading-6', errors.name ? 'ring-red-300 text-red-900 focus:ring-red-500' : 'text-gray-900 ring-gray-300 focus:ring-blue-600']" />
            <p v-if="errors.name" class="mt-2 text-sm text-red-600">{{ errors.name[0] }}</p>
          </div>
        </div>

        <div>
          <label for="email" class="block text-sm font-medium leading-6 text-gray-900">Email address</label>
          <div class="mt-2">
            <input id="email" name="email" type="email" autocomplete="email" required v-model="form.email" :class="['block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset focus:ring-2 focus:ring-inset sm:text-sm sm:leading-6', errors.email ? 'ring-red-300 text-red-900 focus:ring-red-500' : 'text-gray-900 ring-gray-300 focus:ring-blue-600']" />
            <p v-if="errors.email" class="mt-2 text-sm text-red-600">{{ errors.email[0] }}</p>
          </div>
        </div>

        <div>
          <label for="phone" class="block text-sm font-medium leading-6 text-gray-900">No. Telepon</label>
          <div class="mt-2">
            <input id="phone" name="phone" type="text" required v-model="form.phone" :class="['block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset focus:ring-2 focus:ring-inset sm:text-sm sm:leading-6', errors.phone ? 'ring-red-300 text-red-900 focus:ring-red-500' : 'text-gray-900 ring-gray-300 focus:ring-blue-600']" />
            <p v-if="errors.phone" class="mt-2 text-sm text-red-600">{{ errors.phone[0] }}</p>
          </div>
        </div>

        <div>
          <label for="company_name" class="block text-sm font-medium leading-6 text-gray-900">Nama Perusahaan (Opsional)</label>
          <div class="mt-2">
            <input id="company_name" name="company_name" type="text" v-model="form.company_name" :class="['block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset focus:ring-2 focus:ring-inset sm:text-sm sm:leading-6', errors.company_name ? 'ring-red-300 text-red-900 focus:ring-red-500' : 'text-gray-900 ring-gray-300 focus:ring-blue-600']" />
            <p v-if="errors.company_name" class="mt-2 text-sm text-red-600">{{ errors.company_name[0] }}</p>
          </div>
        </div>
        
        <div>
          <label for="company_address" class="block text-sm font-medium leading-6 text-gray-900">Alamat Perusahaan (Opsional)</label>
          <div class="mt-2">
            <textarea id="company_address" name="company_address" rows="3" v-model="form.company_address" :class="['block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset focus:ring-2 focus:ring-inset sm:text-sm sm:leading-6', errors.company_address ? 'ring-red-300 text-red-900 focus:ring-red-500' : 'text-gray-900 ring-gray-300 focus:ring-blue-600']"></textarea>
            <p v-if="errors.company_address" class="mt-2 text-sm text-red-600">{{ errors.company_address[0] }}</p>
          </div>
        </div>

        <div>
          <label for="password" class="block text-sm font-medium leading-6 text-gray-900">Password</label>
          <div class="mt-2">
            <input id="password" name="password" type="password" required v-model="form.password" :class="['block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset focus:ring-2 focus:ring-inset sm:text-sm sm:leading-6', errors.password ? 'ring-red-300 text-red-900 focus:ring-red-500' : 'text-gray-900 ring-gray-300 focus:ring-blue-600']" />
            <p v-if="errors.password" class="mt-2 text-sm text-red-600">{{ errors.password[0] }}</p>
          </div>
        </div>
        
        <div>
          <label for="password_confirmation" class="block text-sm font-medium leading-6 text-gray-900">Konfirmasi Password</label>
          <div class="mt-2">
            <input id="password_confirmation" name="password_confirmation" type="password" required v-model="form.password_confirmation" class="block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6" />
          </div>
        </div>

        <div>
          <button 
            type="submit" 
            :disabled="loading"
            class="flex w-full justify-center rounded-md bg-blue-600 px-3 py-1.5 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 disabled:opacity-70 disabled:cursor-not-allowed"
          >
            <LoadingSpinner v-if="loading" size="sm" class="mr-2 text-white flex-shrink-0" />
            <span :class="{'ml-2': loading}">Daftar</span>
          </button>
        </div>
      </form>

      <div class="mt-6 text-center">
        <p class="text-sm text-gray-500">
          Sudah punya akun?
          {{ ' ' }}
          <router-link to="/login" class="font-semibold leading-6 text-blue-600 hover:text-blue-500">Masuk</router-link>
        </p>
      </div>
    </div>
  </AuthLayout>
</template>
