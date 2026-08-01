<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { dashboardApi } from '@/api/dashboard'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import StatCard from '@/components/dashboard/StatCard.vue'
import TrendChart from '@/components/dashboard/TrendChart.vue'
import CategoryChart from '@/components/dashboard/CategoryChart.vue'
import StatusBadge from '@/components/common/StatusBadge.vue'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'
import { formatDate } from '@/utils/formatters'
import { 
  FolderIcon, 
  ClockIcon, 
  CheckCircleIcon, 
  XCircleIcon,
  PlayIcon
} from '@heroicons/vue/24/outline'
import VueApexCharts from 'vue3-apexcharts'

const router = useRouter()
const loading = ref(true)
const dashboardData = ref(null)

onMounted(async () => {
  try {
    const res = await dashboardApi.getPenilaiDashboard()
    dashboardData.value = res.data
  } catch (error) {
    console.error('Failed to load dashboard:', error)
  } finally {
    loading.value = false
  }
})

const donutOptions = {
  chart: { type: 'donut' },
  labels: ['Approved', 'Rejected', 'Revised'],
  colors: ['#10b981', '#ef4444', '#f59e0b'],
  dataLabels: { enabled: true, formatter: (val) => val.toFixed(1) + '%' },
  plotOptions: { pie: { donut: { size: '65%' } } },
  legend: { position: 'bottom' }
}
</script>

<template>
  <DashboardLayout>
    <div v-if="loading" class="flex items-center justify-center h-64">
      <LoadingSpinner size="lg" text="Memuat dashboard..." />
    </div>
    
    <div v-else-if="dashboardData" class="space-y-6">
      
      <!-- Stats -->
      <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-5">
        <StatCard title="Total Pengajuan" :value="dashboardData.stats.total_submissions" :icon="FolderIcon" color="blue" />
        <StatCard title="Menunggu Review" :value="dashboardData.stats.pending_review" :icon="ClockIcon" color="amber" />
        <StatCard title="Sedang Direview" :value="dashboardData.stats.in_review" :icon="PlayIcon" color="blue" />
        <StatCard title="Disetujui" :value="dashboardData.stats.approved" :icon="CheckCircleIcon" color="green" />
        <StatCard title="Ditolak" :value="dashboardData.stats.rejected" :icon="XCircleIcon" color="red" />
      </div>

      <!-- Charts -->
      <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
        
        <div class="bg-white rounded-lg shadow p-4 flex flex-col items-center justify-center">
          <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4 self-start w-full">Approval Rate</h3>
          <VueApexCharts 
            type="donut" 
            width="100%"
            height="280" 
            :options="donutOptions" 
            :series="[
              dashboardData.status_distribution?.approved || 0,
              dashboardData.status_distribution?.rejected || 0,
              dashboardData.status_distribution?.revised || 0
            ]" 
          />
        </div>

        <div class="lg:col-span-1">
           <TrendChart :data="dashboardData.monthly_trends" />
        </div>

        <div class="lg:col-span-1">
           <CategoryChart :data="dashboardData.category_distribution" />
        </div>

      </div>

      <!-- Recent Reviews -->
      <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:px-6 flex justify-between items-center border-b border-gray-200">
          <h3 class="text-lg leading-6 font-medium text-gray-900">Sedang Direview</h3>
          <router-link to="/penilai/submissions" class="text-sm font-medium text-blue-600 hover:text-blue-500">
            Lihat Semua Pengajuan &rarr;
          </router-link>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Project</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pemohon</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="review in dashboardData.recent_reviews" :key="review.id" class="hover:bg-gray-50 cursor-pointer" @click="router.push(`/penilai/submissions/${review.id}/review`)">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900">{{ review.project_code }}</div>
                  <div class="text-xs text-gray-500 truncate max-w-[200px]">{{ review.title }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ review.user?.name }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  <StatusBadge :status="review.status" />
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ formatDate(review.created_at) }}</td>
              </tr>
              <tr v-if="dashboardData.recent_reviews.length === 0">
                <td colspan="4" class="px-6 py-10 text-center text-gray-500">Tidak ada project yang sedang Anda review</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      
    </div>
  </DashboardLayout>
</template>
