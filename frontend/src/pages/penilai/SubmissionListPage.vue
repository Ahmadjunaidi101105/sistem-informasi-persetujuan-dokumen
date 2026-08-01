<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { projectsApi } from '@/api/projects'
import { categoriesApi } from '@/api/categories'
import { reviewsApi } from '@/api/reviews'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import DataTable from '@/components/common/DataTable.vue'
import StatusBadge from '@/components/common/StatusBadge.vue'
import SearchInput from '@/components/common/SearchInput.vue'
import Pagination from '@/components/common/Pagination.vue'
import ConfirmDialog from '@/components/common/ConfirmDialog.vue'
import { useUiStore } from '@/stores/ui'
import { useAuthStore } from '@/stores/auth'
import { formatDate } from '@/utils/formatters'
import { Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/vue'
import { EllipsisVerticalIcon, DocumentArrowDownIcon } from '@heroicons/vue/20/solid'
import { STATUS_MAP } from '@/utils/constants'

const router = useRouter()
const uiStore = useUiStore()
const authStore = useAuthStore()

const loading = ref(true)
const projects = ref([])
const categories = ref([])
const pagination = ref({ current_page: 1, last_page: 1, per_page: 10, total: 0 })

const filters = ref({
  search: '',
  status: '',
  category_id: '',
  sort_by: 'submitted_at',
  sort_order: 'desc'
})

const reviewDialog = ref({ show: false, project: null })

const columns = [
  { key: 'project_code', label: 'Kode Project', sortable: true },
  { key: 'title', label: 'Judul', sortable: true },
  { key: 'pemohon', label: 'Pemohon', sortable: false },
  { key: 'status', label: 'Status', sortable: true },
  { key: 'submitted_at', label: 'Tgl Ajuan', sortable: true },
  { key: 'actions', label: 'Aksi', sortable: false }
]

const loadData = async (page = 1) => {
  loading.value = true
  try {
    const res = await projectsApi.list({ ...filters.value, page })
    projects.value = res.data || []
    pagination.value = res.meta || { current_page: 1, last_page: 1, per_page: 10, total: 0 }
  } catch (e) {
    uiStore.showToast('Gagal memuat data', 'error')
  } finally {
    loading.value = false
  }
}

const loadCategories = async () => {
  try {
    const res = await categoriesApi.list()
    categories.value = res.data || []
  } catch (e) {}
}

onMounted(() => {
  loadCategories()
  loadData()
})

watch(() => filters.value.search, () => loadData(1))
watch(() => filters.value.status, () => loadData(1))
watch(() => filters.value.category_id, () => loadData(1))

const handleSort = (column) => {
  if (filters.value.sort_by === column) {
    filters.value.sort_order = filters.value.sort_order === 'asc' ? 'desc' : 'asc'
  } else {
    filters.value.sort_by = column
    filters.value.sort_order = 'asc'
  }
  loadData(1)
}

const confirmTakeReview = (project) => {
  reviewDialog.value = { show: true, project }
}

const handleTakeReview = async () => {
  try {
    await reviewsApi.takeReview(reviewDialog.value.project.id)
    uiStore.showToast('Berhasil mengambil review project', 'success')
    reviewDialog.value.show = false
    loadData(pagination.value.current_page)
  } catch (e) {
    uiStore.showToast('Gagal mengambil review', 'error')
  }
}

const handleExportExcel = async () => {
  try {
    const res = await projectsApi.exportExcel(filters.value, { responseType: 'blob' })
    const url = window.URL.createObjectURL(new Blob([res.data]))
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', 'daftar_pengajuan.xlsx')
    document.body.appendChild(link)
    link.click()
  } catch (e) {
    uiStore.showToast('Gagal mengekspor Excel', 'error')
  }
}
</script>

<template>
  <DashboardLayout>
    <div class="sm:flex sm:items-center sm:justify-between mb-8">
      <div>
        <h1 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:tracking-tight">Daftar Pengajuan Permohonan</h1>
        <p class="mt-2 text-sm text-gray-500">Daftar semua permohonan persetujuan dokumen dari klien.</p>
      </div>
      <div class="mt-4 sm:ml-4 sm:mt-0 flex space-x-3">
        <button @click="handleExportExcel" class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
          <DocumentArrowDownIcon class="-ml-0.5 mr-1.5 h-5 w-5 text-gray-400" aria-hidden="true" />
          Export Excel
        </button>
      </div>
    </div>

    <div class="bg-white shadow sm:rounded-lg mb-6 p-4">
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <SearchInput v-model="filters.search" placeholder="Cari kode, judul, atau perusahaan..." />
        
        <select v-model="filters.status" class="block w-full rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-blue-600 sm:text-sm sm:leading-6">
          <option value="">Semua Status</option>
          <option v-for="(config, key) in STATUS_MAP" :key="key" :value="key" v-show="key !== 'draft'">{{ config.label }}</option>
        </select>

        <select v-model="filters.category_id" class="block w-full rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-blue-600 sm:text-sm sm:leading-6">
          <option value="">Semua Kategori</option>
          <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
        </select>
      </div>
    </div>

    <DataTable 
      :columns="columns" 
      :data="projects" 
      :loading="loading" 
      :sort-by="filters.sort_by" 
      :sort-order="filters.sort_order" 
      @sort="handleSort"
    >
      <template #col-project_code="{ value }">
        <span class="font-medium text-gray-900">{{ value }}</span>
      </template>
      <template #col-title="{ row }">
        <div class="max-w-[200px] truncate" :title="row.title">{{ row.title }}</div>
      </template>
      <template #col-pemohon="{ row }">
        <div class="text-sm text-gray-900">{{ row.user?.name }}</div>
        <div class="text-xs text-gray-500">{{ row.user?.company_name || '-' }}</div>
      </template>
      <template #col-status="{ value }">
        <StatusBadge :status="value" />
      </template>
      <template #col-submitted_at="{ value }">
        {{ value ? formatDate(value) : '-' }}
      </template>
      <template #col-actions="{ row }">
        <Menu as="div" class="relative inline-block text-left">
          <div>
            <MenuButton class="flex items-center rounded-full bg-gray-100 text-gray-400 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-gray-100">
              <span class="sr-only">Open options</span>
              <EllipsisVerticalIcon class="h-5 w-5" aria-hidden="true" />
            </MenuButton>
          </div>

          <transition enter-active-class="transition ease-out duration-100" enter-from-class="transform opacity-0 scale-95" enter-to-class="transform opacity-100 scale-100" leave-active-class="transition ease-in duration-75" leave-from-class="transform opacity-100 scale-100" leave-to-class="transform opacity-0 scale-95">
            <MenuItems class="absolute right-0 z-10 mt-2 w-56 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none">
              <div class="py-1">
                <MenuItem v-slot="{ active }">
                  <router-link :to="`/penilai/submissions/${row.id}`" :class="[active ? 'bg-gray-100 text-gray-900' : 'text-gray-700', 'block px-4 py-2 text-sm']">Lihat Detail</router-link>
                </MenuItem>
                
                <MenuItem v-slot="{ active }" v-if="row.status === 'submitted' && !row.current_reviewer_id">
                  <button @click.prevent="confirmTakeReview(row)" :class="[active ? 'bg-blue-50 text-blue-900' : 'text-blue-700', 'block w-full px-4 py-2 text-left text-sm']">Ambil Review</button>
                </MenuItem>

                <MenuItem v-slot="{ active }" v-if="row.status === 'in_review' && row.current_reviewer_id === authStore.user?.id">
                  <router-link :to="`/penilai/submissions/${row.id}/review`" :class="[active ? 'bg-amber-50 text-amber-900' : 'text-amber-700', 'block px-4 py-2 text-sm']">Lanjutkan Review</router-link>
                </MenuItem>
              </div>
            </MenuItems>
          </transition>
        </Menu>
      </template>
    </DataTable>

    <div class="mt-4">
      <Pagination :meta="pagination" @page-change="loadData" />
    </div>

    <ConfirmDialog 
      :show="reviewDialog.show"
      title="Ambil Review Project"
      message="Apakah Anda yakin ingin mengambil project ini untuk dinilai? Anda akan menjadi penilai utama untuk project ini."
      confirm-text="Ya, Ambil Review"
      type="info"
      @confirm="handleTakeReview"
      @cancel="reviewDialog.show = false"
    />

  </DashboardLayout>
</template>
