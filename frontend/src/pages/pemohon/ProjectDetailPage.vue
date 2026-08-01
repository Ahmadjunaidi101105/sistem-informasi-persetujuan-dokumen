<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { projectsApi } from '@/api/projects'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import StatusBadge from '@/components/common/StatusBadge.vue'
import ProjectTimeline from '@/components/project/ProjectTimeline.vue'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'
import ConfirmDialog from '@/components/common/ConfirmDialog.vue'
import { useUiStore } from '@/stores/ui'
import { formatDate } from '@/utils/formatters'
import { 
  ArrowLeftIcon, 
  DocumentArrowDownIcon,
  PencilSquareIcon,
  PaperAirplaneIcon,
  DocumentIcon
} from '@heroicons/vue/20/solid'
import { STATUS_MAP, PRIORITY_MAP } from '@/utils/constants'

const router = useRouter()
const route = useRoute()
const uiStore = useUiStore()

const loading = ref(true)
const submitting = ref(false)
const project = ref(null)
const activeTab = ref('info') // info, docs, history

const submitDialog = ref(false)

const loadProject = async () => {
  try {
    const res = await projectsApi.get(route.params.id)
    project.value = res.data
  } catch (e) {
    uiStore.showToast('Gagal memuat data project', 'error')
    router.push('/pemohon/projects')
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadProject()
})

const isEditable = computed(() => {
  return project.value && ['draft', 'revised'].includes(project.value.status)
})

const handleSubmit = async () => {
  if (project.value.documents.length === 0) {
    uiStore.showToast('Minimal harus melampirkan 1 dokumen', 'error')
    submitDialog.value = false
    return
  }

  submitting.value = true
  try {
    await projectsApi.submit(project.value.id)
    uiStore.showToast('Permohonan berhasil disubmit', 'success')
    submitDialog.value = false
    loadProject()
  } catch (e) {
    uiStore.showToast('Gagal melakukan submit', 'error')
  } finally {
    submitting.value = false
  }
}

const handleExportPdf = async () => {
  try {
    const res = await projectsApi.exportPdf(project.value.id, { responseType: 'blob' })
    const url = window.URL.createObjectURL(new Blob([res.data]))
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', `project-${project.value.project_code}.pdf`)
    document.body.appendChild(link)
    link.click()
  } catch (e) {
    uiStore.showToast('Gagal mengekspor PDF', 'error')
  }
}

const tabs = [
  { id: 'info', name: 'Informasi' },
  { id: 'docs', name: 'Dokumen' },
  { id: 'history', name: 'Riwayat Penilaian' },
]
</script>

<template>
  <DashboardLayout>
    <div v-if="loading" class="flex justify-center h-64 items-center">
      <LoadingSpinner size="lg" />
    </div>
    
    <div v-else-if="project">
      <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <div>
          <button @click="router.back()" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 mb-2">
            <ArrowLeftIcon class="mr-1 h-5 w-5 flex-shrink-0" aria-hidden="true" />
            Kembali
          </button>
          <div class="flex items-center space-x-3">
            <h1 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:tracking-tight">
              {{ project.project_code }}
            </h1>
            <StatusBadge :status="project.status" />
          </div>
        </div>
        <div class="mt-4 flex sm:ml-4 sm:mt-0 space-x-3">
          <button @click="handleExportPdf" type="button" class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
            <DocumentArrowDownIcon class="-ml-0.5 mr-1.5 h-5 w-5 text-gray-400" aria-hidden="true" />
            Export PDF
          </button>
          
          <router-link v-if="isEditable" :to="`/pemohon/projects/${project.id}/edit`" class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
            <PencilSquareIcon class="-ml-0.5 mr-1.5 h-5 w-5 text-gray-400" aria-hidden="true" />
            Edit
          </router-link>

          <button v-if="isEditable" @click="submitDialog = true" type="button" class="inline-flex items-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
            <PaperAirplaneIcon class="-ml-0.5 mr-1.5 h-5 w-5" aria-hidden="true" />
            Submit
          </button>
        </div>
      </div>

      <!-- Tabs -->
      <div class="mb-6">
        <div class="sm:hidden">
          <label for="tabs" class="sr-only">Select a tab</label>
          <select id="tabs" name="tabs" v-model="activeTab" class="block w-full rounded-md border-gray-300 py-2 pl-3 pr-10 text-base focus:border-blue-500 focus:outline-none focus:ring-blue-500 sm:text-sm">
            <option v-for="tab in tabs" :key="tab.id" :value="tab.id">{{ tab.name }}</option>
          </select>
        </div>
        <div class="hidden sm:block">
          <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
              <button 
                v-for="tab in tabs" 
                :key="tab.id" 
                @click="activeTab = tab.id"
                :class="[activeTab === tab.id ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700', 'whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium']"
              >
                {{ tab.name }}
                <span v-if="tab.id === 'docs'" :class="[activeTab === tab.id ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-900', 'ml-2 rounded-full py-0.5 px-2.5 text-xs font-medium inline-block']">
                  {{ project.documents?.length || 0 }}
                </span>
              </button>
            </nav>
          </div>
        </div>
      </div>

      <!-- Tab Content: Info -->
      <div v-show="activeTab === 'info'" class="bg-white shadow sm:rounded-lg">
        <div class="px-4 py-6 sm:px-6">
          <h3 class="text-base font-semibold leading-7 text-gray-900">Informasi Permohonan</h3>
          <p class="mt-1 max-w-2xl text-sm leading-6 text-gray-500">Detail dari pengajuan dokumen.</p>
        </div>
        <div class="border-t border-gray-100 px-4 py-6 sm:px-6">
          <dl class="grid grid-cols-1 gap-x-4 gap-y-8 sm:grid-cols-2">
            <div class="sm:col-span-1">
              <dt class="text-sm font-medium text-gray-500">Judul Permohonan</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ project.title }}</dd>
            </div>
            <div class="sm:col-span-1">
              <dt class="text-sm font-medium text-gray-500">Kategori</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ project.document_category?.name }}</dd>
            </div>
            <div class="sm:col-span-1">
              <dt class="text-sm font-medium text-gray-500">Prioritas</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ PRIORITY_MAP[project.priority] || project.priority }}</dd>
            </div>
            <div class="sm:col-span-1">
              <dt class="text-sm font-medium text-gray-500">Pemohon</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ project.user?.name }} ({{ project.user?.company_name || '-' }})</dd>
            </div>
            <div class="sm:col-span-1">
              <dt class="text-sm font-medium text-gray-500">Tanggal Pengajuan</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ project.submitted_at ? formatDate(project.submitted_at) : '-' }}</dd>
            </div>
            <div class="sm:col-span-1">
              <dt class="text-sm font-medium text-gray-500">Current Reviewer</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ project.current_reviewer?.name || '-' }}</dd>
            </div>
            <div class="sm:col-span-2">
              <dt class="text-sm font-medium text-gray-500">Deskripsi</dt>
              <dd class="mt-1 text-sm text-gray-900 whitespace-pre-wrap">{{ project.description || '-' }}</dd>
            </div>
            <div class="sm:col-span-2">
              <dt class="text-sm font-medium text-gray-500">Catatan</dt>
              <dd class="mt-1 text-sm text-gray-900 whitespace-pre-wrap">{{ project.notes || '-' }}</dd>
            </div>
          </dl>
        </div>
      </div>

      <!-- Tab Content: Dokumen -->
      <div v-show="activeTab === 'docs'" class="bg-white shadow sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6 flex justify-between items-center border-b border-gray-200">
          <div>
            <h3 class="text-base font-semibold leading-7 text-gray-900">Dokumen Lampiran</h3>
            <p class="mt-1 max-w-2xl text-sm leading-6 text-gray-500">Daftar dokumen yang dilampirkan pada permohonan ini.</p>
          </div>
          <router-link v-if="isEditable" :to="`/pemohon/projects/${project.id}/edit`" class="text-sm font-medium text-blue-600 hover:text-blue-500">
            Upload Baru
          </router-link>
        </div>
        <ul role="list" class="divide-y divide-gray-100">
          <li v-for="doc in project.documents" :key="doc.id" class="flex items-center justify-between py-4 pl-4 pr-5 text-sm leading-6">
            <div class="flex w-0 flex-1 items-center">
              <DocumentIcon class="h-6 w-6 flex-shrink-0 text-gray-400" aria-hidden="true" />
              <div class="ml-4 flex min-w-0 flex-1 gap-2 flex-col sm:flex-row sm:items-center">
                <span class="truncate font-medium text-gray-900">{{ doc.original_name }}</span>
                <span class="flex-shrink-0 text-gray-400 text-xs">v{{ doc.version }} • {{ doc.file_size_formatted }}</span>
              </div>
            </div>
            <div class="ml-4 flex-shrink-0">
              <a :href="doc.download_url" target="_blank" class="font-medium text-blue-600 hover:text-blue-500 bg-blue-50 px-3 py-1.5 rounded-md">
                Download
              </a>
            </div>
          </li>
          <li v-if="project.documents?.length === 0" class="py-10 text-center text-gray-500 text-sm">
            Tidak ada dokumen.
          </li>
        </ul>
      </div>

      <!-- Tab Content: History -->
      <div v-show="activeTab === 'history'" class="bg-white shadow sm:rounded-lg p-6">
        <ProjectTimeline :reviews="project.reviews || []" />
      </div>

    </div>

    <ConfirmDialog 
      :show="submitDialog"
      title="Submit Permohonan"
      message="Apakah Anda yakin ingin melakukan submit? Setelah disubmit, Anda tidak dapat mengubah data permohonan sampai proses review selesai."
      confirm-text="Submit Sekarang"
      type="info"
      @confirm="handleSubmit"
      @cancel="submitDialog = false"
    />
  </DashboardLayout>
</template>
