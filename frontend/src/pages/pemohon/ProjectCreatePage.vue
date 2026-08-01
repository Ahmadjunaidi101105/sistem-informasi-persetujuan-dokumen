<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { projectsApi } from '@/api/projects'
import { categoriesApi } from '@/api/categories'
import { documentsApi } from '@/api/documents'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import FileUpload from '@/components/common/FileUpload.vue'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'
import { useUiStore } from '@/stores/ui'
import { PRIORITY_OPTIONS } from '@/utils/constants'
import { ArrowLeftIcon } from '@heroicons/vue/20/solid'

const router = useRouter()
const uiStore = useUiStore()

const categories = ref([])
const loading = ref(false)
const errors = ref({})

const form = ref({
  title: '',
  document_category_id: '',
  priority: 'normal',
  description: '',
  notes: ''
})

const files = ref([])

onMounted(async () => {
  try {
    const res = await categoriesApi.list()
    categories.value = res.data || []
  } catch (e) {
    uiStore.showToast('Gagal memuat kategori', 'error')
  }
})

const handleFilesChanged = (newFiles) => {
  files.value = newFiles
}

const saveProject = async (submitImmediately = false) => {
  if (submitImmediately && files.value.length === 0) {
    uiStore.showToast('Minimal harus ada 1 dokumen untuk melakukan submit', 'error')
    return
  }

  loading.value = true
  errors.value = {}
  
  try {
    const res = await projectsApi.create(form.value)
    const project = res.data

    for (const file of files.value) {
      const formData = new FormData()
      formData.append('document', file)
      await documentsApi.upload(project.id, formData)
    }

    if (submitImmediately) {
      await projectsApi.submit(project.id)
      uiStore.showToast('Permohonan berhasil disubmit', 'success')
      router.push('/pemohon/projects')
    } else {
      uiStore.showToast('Draft berhasil disimpan', 'success')
      router.push(`/pemohon/projects/${project.id}/edit`)
    }
    
  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors || {}
      uiStore.showToast('Periksa kembali data form Anda', 'error')
    } else {
      uiStore.showToast(error.response?.data?.message || 'Terjadi kesalahan', 'error')
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <DashboardLayout>
    <div class="mb-8">
      <button @click="router.back()" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-700">
        <ArrowLeftIcon class="mr-1 h-5 w-5 flex-shrink-0" aria-hidden="true" />
        Kembali
      </button>
      <h1 class="mt-2 text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:tracking-tight">Buat Permohonan Baru</h1>
    </div>

    <form @submit.prevent="saveProject(false)" class="space-y-8 divide-y divide-gray-200">
      <div class="space-y-8 divide-y divide-gray-200">
        
        <div class="bg-white shadow sm:rounded-lg p-6">
          <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
            
            <div class="sm:col-span-4">
              <label for="title" class="block text-sm font-medium leading-6 text-gray-900">Judul Permohonan <span class="text-red-500">*</span></label>
              <div class="mt-2">
                <input type="text" id="title" v-model="form.title" required :class="['block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset sm:text-sm sm:leading-6', errors.title ? 'ring-red-300 focus:ring-red-500' : 'ring-gray-300 focus:ring-brand-700']" />
                <p v-if="errors.title" class="mt-2 text-sm text-red-600">{{ errors.title[0] }}</p>
              </div>
            </div>

            <div class="sm:col-span-3">
              <label for="category" class="block text-sm font-medium leading-6 text-gray-900">Kategori Dokumen <span class="text-red-500">*</span></label>
              <div class="mt-2">
                <select id="category" v-model="form.document_category_id" required :class="['block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset sm:text-sm sm:leading-6', errors.document_category_id ? 'ring-red-300 focus:ring-red-500' : 'ring-gray-300 focus:ring-brand-700']">
                  <option value="" disabled>Pilih Kategori</option>
                  <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                </select>
                <p v-if="errors.document_category_id" class="mt-2 text-sm text-red-600">{{ errors.document_category_id[0] }}</p>
              </div>
            </div>

            <div class="sm:col-span-3">
              <label for="priority" class="block text-sm font-medium leading-6 text-gray-900">Prioritas</label>
              <div class="mt-2">
                <select id="priority" v-model="form.priority" :class="['block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset sm:text-sm sm:leading-6', errors.priority ? 'ring-red-300 focus:ring-red-500' : 'ring-gray-300 focus:ring-brand-700']">
                  <option v-for="opt in PRIORITY_OPTIONS" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                </select>
                <p v-if="errors.priority" class="mt-2 text-sm text-red-600">{{ errors.priority[0] }}</p>
              </div>
            </div>

            <div class="sm:col-span-6">
              <label for="description" class="block text-sm font-medium leading-6 text-gray-900">Deskripsi</label>
              <div class="mt-2">
                <textarea id="description" v-model="form.description" rows="3" class="block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-brand-700 sm:text-sm sm:leading-6"></textarea>
                <p v-if="errors.description" class="mt-2 text-sm text-red-600">{{ errors.description[0] }}</p>
              </div>
            </div>

            <div class="sm:col-span-6">
              <label for="notes" class="block text-sm font-medium leading-6 text-gray-900">Catatan Tambahan</label>
              <div class="mt-2">
                <textarea id="notes" v-model="form.notes" rows="2" class="block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-brand-700 sm:text-sm sm:leading-6"></textarea>
                <p v-if="errors.notes" class="mt-2 text-sm text-red-600">{{ errors.notes[0] }}</p>
              </div>
            </div>

          </div>
        </div>

        <div class="bg-white shadow sm:rounded-lg p-6 pt-8">
          <div>
            <h3 class="text-base font-semibold leading-6 text-gray-900">Dokumen Pendukung</h3>
            <p class="mt-1 text-sm text-gray-500">Upload dokumen terkait permohonan. Untuk submit permohonan, Anda wajib melampirkan minimal 1 dokumen.</p>
          </div>
          <div class="mt-6">
            <FileUpload 
              :multiple="true" 
              accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png"
              @files-changed="handleFilesChanged"
            />
          </div>
        </div>

      </div>

      <div class="pt-5 pb-10">
        <div class="flex justify-end gap-x-3">
          <button type="button" @click="router.back()" class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
            Batal
          </button>
          <button type="button" @click="saveProject(false)" :disabled="loading" class="inline-flex justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-brand-700 shadow-sm ring-1 ring-inset ring-brand-300 hover:bg-brand-50 disabled:opacity-50">
            Simpan Draft
          </button>
          <button type="button" @click="saveProject(true)" :disabled="loading" class="inline-flex justify-center rounded-md bg-brand-700 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-700 disabled:opacity-50">
            <LoadingSpinner v-if="loading" size="sm" class="mr-2 text-white" />
            Submit Permohonan
          </button>
        </div>
      </div>
    </form>
  </DashboardLayout>
</template>
