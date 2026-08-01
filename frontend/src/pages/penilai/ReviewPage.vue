<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { projectsApi } from '@/api/projects'
import { reviewsApi } from '@/api/reviews'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import StatusBadge from '@/components/common/StatusBadge.vue'
import ProjectTimeline from '@/components/project/ProjectTimeline.vue'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'
import ConfirmDialog from '@/components/common/ConfirmDialog.vue'
import { useUiStore } from '@/stores/ui'
import { useAuthStore } from '@/stores/auth'
import { formatDate } from '@/utils/formatters'
import { 
  ArrowLeftIcon, 
  DocumentIcon,
  CheckCircleIcon,
  XCircleIcon,
  ArrowPathIcon
} from '@heroicons/vue/20/solid'
import { PRIORITY_MAP } from '@/utils/constants'

const router = useRouter()
const route = useRoute()
const uiStore = useUiStore()
const authStore = useAuthStore()

const loading = ref(true)
const submitting = ref(false)
const project = ref(null)

const form = ref({ notes: '' })
const confirmDialog = ref({ show: false, action: null, title: '', message: '', type: 'info', confirmText: '' })

onMounted(async () => {
  try {
    const res = await projectsApi.get(route.params.id)
    project.value = res.data.data
    
    // Validasi apakah user bisa review project ini
    if (project.value.status !== 'in_review' || project.value.current_reviewer_id !== authStore.user?.id) {
      uiStore.showToast('Anda tidak memiliki akses review ke project ini', 'error')
      router.push('/penilai/submissions')
    }
  } catch (e) {
    uiStore.showToast('Gagal memuat data project', 'error')
    router.push('/penilai/submissions')
  } finally {
    loading.value = false
  }
})

const triggerAction = (action) => {
  if (action !== 'approve' && form.value.notes.length < 10) {
    uiStore.showToast('Catatan penilaian wajib diisi minimal 10 karakter untuk aksi Revisi/Tolak', 'error')
    return
  }
  
  if (action === 'approve') {
    confirmDialog.value = {
      show: true, action,
      title: 'Setujui Dokumen',
      message: 'Apakah Anda yakin ingin menyetujui dokumen ini? Proses ini akan mengubah status menjadi Approved dan mengirimkan notifikasi ke pemohon.',
      type: 'info',
      confirmText: 'Setujui Sekarang'
    }
  } else if (action === 'revise') {
    confirmDialog.value = {
      show: true, action,
      title: 'Minta Revisi Dokumen',
      message: 'Apakah Anda yakin ingin meminta revisi untuk dokumen ini? Status akan berubah menjadi Revised.',
      type: 'warning',
      confirmText: 'Minta Revisi'
    }
  } else if (action === 'reject') {
    confirmDialog.value = {
      show: true, action,
      title: 'Tolak Dokumen',
      message: 'Apakah Anda yakin ingin menolak dokumen ini? Status akan berubah menjadi Rejected dan permohonan akan ditutup.',
      type: 'danger',
      confirmText: 'Tolak Dokumen'
    }
  }
}

const handleConfirm = async () => {
  const { action } = confirmDialog.value
  submitting.value = true
  confirmDialog.value.show = false
  
  try {
    if (action === 'approve') {
      await reviewsApi.approve(project.value.id, form.value)
      uiStore.showToast('Permohonan berhasil disetujui', 'success')
    } else if (action === 'revise') {
      await reviewsApi.revise(project.value.id, form.value)
      uiStore.showToast('Permintaan revisi berhasil dikirim', 'success')
    } else if (action === 'reject') {
      await reviewsApi.reject(project.value.id, form.value)
      uiStore.showToast('Permohonan berhasil ditolak', 'success')
    }
    router.push('/penilai/submissions')
  } catch (e) {
    uiStore.showToast('Gagal memproses penilaian', 'error')
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <DashboardLayout>
    <div v-if="loading" class="flex justify-center h-64 items-center">
      <LoadingSpinner size="lg" />
    </div>
    
    <div v-else-if="project">
      <div class="mb-6">
        <button @click="router.back()" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 mb-2">
          <ArrowLeftIcon class="mr-1 h-5 w-5 flex-shrink-0" aria-hidden="true" />
          Kembali
        </button>
        <div class="flex items-center space-x-3">
          <h1 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:tracking-tight">
            Review: {{ project.project_code }}
          </h1>
          <StatusBadge :status="project.status" />
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Kolom Kiri: Detail Project & Review Form -->
        <div class="lg:col-span-2 space-y-6">
          
          <!-- Summary Info -->
          <div class="bg-white shadow sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
              <h3 class="text-base font-semibold leading-6 text-gray-900">Informasi Pengajuan</h3>
            </div>
            <div class="px-4 py-5 sm:p-6">
              <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                <div class="sm:col-span-1">
                  <dt class="text-sm font-medium text-gray-500">Judul</dt>
                  <dd class="mt-1 text-sm text-gray-900 font-medium">{{ project.title }}</dd>
                </div>
                <div class="sm:col-span-1">
                  <dt class="text-sm font-medium text-gray-500">Pemohon</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ project.user?.name }} - {{ project.user?.company_name || '-' }}</dd>
                </div>
                <div class="sm:col-span-1">
                  <dt class="text-sm font-medium text-gray-500">Kategori</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ project.document_category?.name }}</dd>
                </div>
                <div class="sm:col-span-1">
                  <dt class="text-sm font-medium text-gray-500">Prioritas & Revisi</dt>
                  <dd class="mt-1 text-sm text-gray-900">
                    {{ PRIORITY_MAP[project.priority] || project.priority }} 
                    <span class="text-gray-400 mx-1">•</span> 
                    Revisi: {{ project.revision_count }} kali
                  </dd>
                </div>
                <div class="sm:col-span-2">
                  <dt class="text-sm font-medium text-gray-500">Deskripsi</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ project.description || '-' }}</dd>
                </div>
              </dl>
            </div>
          </div>

          <!-- Documents -->
          <div class="bg-white shadow sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
              <h3 class="text-base font-semibold leading-6 text-gray-900">Dokumen Lampiran ({{ project.documents?.length || 0 }})</h3>
            </div>
            <ul role="list" class="divide-y divide-gray-100 px-4 py-2 sm:px-6">
              <li v-for="doc in project.documents" :key="doc.id" class="flex items-center justify-between py-4 text-sm leading-6">
                <div class="flex w-0 flex-1 items-center">
                  <DocumentIcon class="h-6 w-6 flex-shrink-0 text-gray-400" aria-hidden="true" />
                  <div class="ml-4 flex min-w-0 flex-1 gap-2 flex-col sm:flex-row sm:items-center">
                    <span class="truncate font-medium text-gray-900">{{ doc.original_name }}</span>
                    <span class="flex-shrink-0 text-gray-400 text-xs">v{{ doc.version }} • {{ doc.file_size_formatted }}</span>
                  </div>
                </div>
                <div class="ml-4 flex-shrink-0">
                  <a :href="doc.download_url" target="_blank" class="font-medium text-blue-600 hover:text-blue-500 bg-blue-50 px-3 py-1.5 rounded-md">
                    Unduh
                  </a>
                </div>
              </li>
              <li v-if="project.documents?.length === 0" class="py-6 text-center text-gray-500 text-sm">
                Tidak ada dokumen.
              </li>
            </ul>
          </div>

          <!-- Review Form -->
          <div class="bg-white shadow sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
              <h3 class="text-base font-semibold leading-6 text-gray-900">Form Penilaian</h3>
              <p class="mt-1 text-sm text-gray-500">Isi catatan penilaian dan tentukan keputusan Anda.</p>
            </div>
            <div class="px-4 py-5 sm:p-6">
              <div>
                <label for="notes" class="block text-sm font-medium leading-6 text-gray-900">Catatan Penilaian</label>
                <div class="mt-2">
                  <textarea id="notes" v-model="form.notes" rows="4" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6" placeholder="Masukkan detail revisi atau alasan penolakan..."></textarea>
                </div>
                <p class="mt-2 text-sm text-gray-500">Catatan wajib diisi (minimal 10 karakter) jika memilih Minta Revisi atau Tolak.</p>
              </div>

              <div class="mt-8 flex flex-col sm:flex-row sm:justify-end gap-3 border-t border-gray-200 pt-5">
                <button @click="triggerAction('reject')" :disabled="submitting" type="button" class="inline-flex items-center justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600 disabled:opacity-50">
                  <XCircleIcon class="-ml-0.5 mr-1.5 h-5 w-5" aria-hidden="true" />
                  Tolak
                </button>
                <button @click="triggerAction('revise')" :disabled="submitting" type="button" class="inline-flex items-center justify-center rounded-md bg-amber-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-amber-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amber-600 disabled:opacity-50">
                  <ArrowPathIcon class="-ml-0.5 mr-1.5 h-5 w-5" aria-hidden="true" />
                  Minta Revisi
                </button>
                <button @click="triggerAction('approve')" :disabled="submitting" type="button" class="inline-flex items-center justify-center rounded-md bg-green-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-600 disabled:opacity-50">
                  <CheckCircleIcon class="-ml-0.5 mr-1.5 h-5 w-5" aria-hidden="true" />
                  Setujui Dokumen
                </button>
              </div>
            </div>
          </div>

        </div>

        <!-- Kolom Kanan: History -->
        <div class="lg:col-span-1">
          <div class="bg-white shadow sm:rounded-lg sticky top-24">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
              <h3 class="text-base font-semibold leading-6 text-gray-900">Riwayat Penilaian</h3>
            </div>
            <div class="px-4 py-5 sm:p-6 max-h-[600px] overflow-y-auto">
              <ProjectTimeline :reviews="project.reviews || []" />
            </div>
          </div>
        </div>

      </div>

    </div>

    <ConfirmDialog 
      :show="confirmDialog.show"
      :title="confirmDialog.title"
      :message="confirmDialog.message"
      :confirm-text="confirmDialog.confirmText"
      :type="confirmDialog.type"
      @confirm="handleConfirm"
      @cancel="confirmDialog.show = false"
    />
  </DashboardLayout>
</template>
