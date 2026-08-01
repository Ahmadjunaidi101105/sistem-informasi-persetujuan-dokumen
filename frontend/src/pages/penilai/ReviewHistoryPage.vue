<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { reviewsApi } from '@/api/reviews'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import DataTable from '@/components/common/DataTable.vue'
import StatusBadge from '@/components/common/StatusBadge.vue'
import SearchInput from '@/components/common/SearchInput.vue'
import Pagination from '@/components/common/Pagination.vue'
import { useUiStore } from '@/stores/ui'
import { formatDate } from '@/utils/formatters'

const router = useRouter()
const uiStore = useUiStore()

const loading = ref(true)
const reviews = ref([])
const pagination = ref({ current_page: 1, last_page: 1, per_page: 10, total: 0 })

const filters = ref({
  search: '',
  status_to: '',
  sort_by: 'created_at',
  sort_order: 'desc'
})

const columns = [
  { key: 'project_code', label: 'Kode Project', sortable: false },
  { key: 'title', label: 'Judul', sortable: false },
  { key: 'pemohon', label: 'Pemohon', sortable: false },
  { key: 'keputusan', label: 'Keputusan', sortable: true },
  { key: 'created_at', label: 'Tanggal', sortable: true },
  { key: 'notes', label: 'Catatan', sortable: false }
]

const loadData = async (page = 1) => {
  loading.value = true
  try {
    const res = await reviewsApi.list({ ...filters.value, page })
    reviews.value = res.data || []
    pagination.value = res.meta || { current_page: 1, last_page: 1, per_page: 10, total: 0 }
  } catch (e) {
    uiStore.showToast('Gagal memuat riwayat', 'error')
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadData()
})

watch(() => filters.value.search, () => loadData(1))
watch(() => filters.value.status_to, () => loadData(1))

const handleSort = (column) => {
  const mapCol = column === 'keputusan' ? 'status_to' : column
  if (filters.value.sort_by === mapCol) {
    filters.value.sort_order = filters.value.sort_order === 'asc' ? 'desc' : 'asc'
  } else {
    filters.value.sort_by = mapCol
    filters.value.sort_order = 'asc'
  }
  loadData(1)
}

const navigateToProject = (projectId) => {
  router.push(`/penilai/submissions/${projectId}`)
}
</script>

<template>
  <DashboardLayout>
    <div class="sm:flex sm:items-center sm:justify-between mb-8">
      <div>
        <h1 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:tracking-tight">Riwayat Penilaian</h1>
        <p class="mt-2 text-sm text-gray-500">Daftar semua keputusan dan penilaian yang pernah Anda berikan.</p>
      </div>
    </div>

    <div class="bg-white shadow sm:rounded-lg mb-6 p-4">
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <SearchInput v-model="filters.search" placeholder="Cari kode atau judul..." />
        
        <select v-model="filters.status_to" class="block w-full rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-brand-700 sm:text-sm sm:leading-6">
          <option value="">Semua Keputusan</option>
          <option value="approved">Approved</option>
          <option value="revised">Revised</option>
          <option value="rejected">Rejected</option>
        </select>
      </div>
    </div>

    <DataTable 
      :columns="columns" 
      :data="reviews" 
      :loading="loading" 
      :sort-by="filters.sort_by === 'status_to' ? 'keputusan' : filters.sort_by" 
      :sort-order="filters.sort_order" 
      @sort="handleSort"
    >
      <template #col-project_code="{ row }">
        <button @click="navigateToProject(row.project_id)" class="font-medium text-brand-700 hover:text-brand-900">
          {{ row.project?.project_code }}
        </button>
      </template>
      <template #col-title="{ row }">
        <div class="max-w-[150px] truncate" :title="row.project?.title">{{ row.project?.title }}</div>
      </template>
      <template #col-pemohon="{ row }">
        <div class="text-sm text-gray-900">{{ row.project?.user?.name }}</div>
      </template>
      <template #col-keputusan="{ row }">
        <StatusBadge :status="row.status_to" />
      </template>
      <template #col-created_at="{ value }">
        {{ formatDate(value) }}
      </template>
      <template #col-notes="{ value }">
        <div class="max-w-xs truncate text-xs text-gray-500" :title="value">{{ value || '-' }}</div>
      </template>
    </DataTable>

    <div class="mt-4">
      <Pagination :meta="pagination" @page-change="loadData" />
    </div>

  </DashboardLayout>
</template>
