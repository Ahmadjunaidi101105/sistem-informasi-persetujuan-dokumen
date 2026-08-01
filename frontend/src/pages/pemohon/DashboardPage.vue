<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { dashboardApi } from '@/api/dashboard'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import StatCard from '@/components/dashboard/StatCard.vue'
import StatusChart from '@/components/dashboard/StatusChart.vue'
import TrendChart from '@/components/dashboard/TrendChart.vue'
import StatusBadge from '@/components/common/StatusBadge.vue'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'
import { formatDate } from '@/utils/formatters'
import { FolderIcon, ClockIcon, CheckCircleIcon, ExclamationTriangleIcon } from '@heroicons/vue/24/outline'

const router = useRouter()
const loading = ref(true)
const dashboardData = ref(null)

onMounted(async () => {
  try {
    const res = await dashboardApi.getPemohonDashboard()
    dashboardData.value = res.data
  } catch (error) {
    console.error('Failed to load dashboard:', error)
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <DashboardLayout>
    <div v-if="loading" class="flex items-center justify-center h-64">
      <LoadingSpinner size="lg" text="Memuat dashboard..." />
    </div>
    
    <div v-else-if="dashboardData" class="space-y-6">
      
      <!-- Stats -->
      <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <StatCard 
          title="Total Project" 
          :value="dashboardData.stats.total_projects" 
          :icon="FolderIcon" 
          color="brand" 
        />
        <StatCard 
          title="Menunggu Review" 
          :value="dashboardData.stats.pending_review" 
          :icon="ClockIcon" 
          color="sky" 
        />
        <StatCard 
          title="Disetujui" 
          :value="dashboardData.stats.approved" 
          :icon="CheckCircleIcon" 
          color="brand" 
        />
        <StatCard 
          title="Perlu Revisi" 
          :value="dashboardData.stats.needs_revision" 
          :icon="ExclamationTriangleIcon" 
          color="accent" 
        />
      </div>

      <!-- Charts -->
      <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
        <StatusChart :data="dashboardData.status_distribution" />
        <TrendChart :data="dashboardData.monthly_trends" />
      </div>

      <!-- Recent Projects -->
      <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:px-6 flex justify-between items-center border-b border-gray-200">
          <h3 class="text-lg leading-6 font-medium text-gray-900">Project Terakhir</h3>
          <router-link to="/pemohon/projects" class="text-sm font-medium text-brand-700 hover:text-brand-500">
            Lihat Semua &rarr;
          </router-link>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode Project</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Judul</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="project in dashboardData.recent_projects" :key="project.id" class="hover:bg-gray-50 cursor-pointer" @click="router.push(`/pemohon/projects/${project.id}`)">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ project.project_code }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 truncate max-w-xs">{{ project.title }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  <StatusBadge :status="project.status" />
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ formatDate(project.created_at) }}</td>
              </tr>
              <tr v-if="dashboardData.recent_projects.length === 0">
                <td colspan="4" class="px-6 py-10 text-center text-gray-500">Belum ada project</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      
    </div>
  </DashboardLayout>
</template>
