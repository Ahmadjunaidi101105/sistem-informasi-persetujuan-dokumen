<script setup>
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useNotificationStore } from '@/stores/notifications'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import Pagination from '@/components/common/Pagination.vue'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'
import { timeAgo, formatDateTime } from '@/utils/formatters'
import { CheckIcon } from '@heroicons/vue/20/solid'

const router = useRouter()
const notificationStore = useNotificationStore()

const loadData = async (page = 1) => {
  await notificationStore.fetchNotifications({ page, per_page: 10 })
}

onMounted(() => {
  loadData()
})

const handleMarkAsRead = async (id) => {
  await notificationStore.markAsRead(id)
}

const handleMarkAllAsRead = async () => {
  await notificationStore.markAllAsRead()
}

const navigateToLink = (link, notifId, isRead) => {
  if (!isRead) {
    handleMarkAsRead(notifId)
  }
  if (link) {
    router.push(link)
  }
}
</script>

<template>
  <DashboardLayout>
    <div class="sm:flex sm:items-center sm:justify-between mb-8">
      <div>
        <h1 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:tracking-tight">Notifikasi</h1>
        <p class="mt-2 text-sm text-gray-500">Pembaruan dan aktivitas terbaru terkait akun Anda.</p>
      </div>
      <div class="mt-4 sm:ml-4 sm:mt-0 flex space-x-3">
        <button 
          v-if="notificationStore.unreadCount > 0" 
          @click="handleMarkAllAsRead" 
          class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
        >
          <CheckIcon class="-ml-0.5 mr-1.5 h-5 w-5 text-gray-400" aria-hidden="true" />
          Tandai Semua Dibaca
        </button>
      </div>
    </div>

    <div v-if="notificationStore.loading && notificationStore.notifications.length === 0" class="flex justify-center h-64 items-center">
      <LoadingSpinner size="lg" />
    </div>

    <div v-else class="bg-white shadow sm:rounded-lg overflow-hidden">
      <ul role="list" class="divide-y divide-gray-200">
        <li 
          v-for="notif in notificationStore.notifications" 
          :key="notif.id" 
          :class="[!notif.read_at ? 'bg-blue-50/50 hover:bg-blue-50' : 'bg-white hover:bg-gray-50', 'transition duration-150 cursor-pointer']"
          @click="navigateToLink(notif.data.link, notif.id, !!notif.read_at)"
        >
          <div class="px-4 py-6 sm:px-6">
            <div class="flex items-center justify-between">
              <p :class="[!notif.read_at ? 'font-bold text-gray-900' : 'font-medium text-gray-700', 'text-sm truncate']">
                {{ notif.data.title || 'Sistem Notifikasi' }}
              </p>
              <div class="ml-2 flex flex-shrink-0">
                <p class="text-xs text-gray-500" :title="formatDateTime(notif.created_at)">
                  {{ timeAgo(notif.created_at) }}
                </p>
              </div>
            </div>
            <div class="mt-2 sm:flex sm:justify-between">
              <div class="sm:flex">
                <p :class="[!notif.read_at ? 'text-gray-700' : 'text-gray-500', 'text-sm']">
                  {{ notif.data.message }}
                </p>
              </div>
              <div class="mt-2 flex items-center text-sm text-gray-500 sm:mt-0" v-if="!notif.read_at">
                <button @click.stop="handleMarkAsRead(notif.id)" class="flex items-center text-blue-600 hover:text-blue-800 focus:outline-none">
                  <span class="h-2 w-2 bg-blue-600 rounded-full inline-block mr-2"></span>
                  Tandai dibaca
                </button>
              </div>
            </div>
          </div>
        </li>
        <li v-if="notificationStore.notifications.length === 0" class="px-4 py-12 text-center">
          <p class="text-sm text-gray-500">Belum ada notifikasi saat ini.</p>
        </li>
      </ul>
    </div>

    <div class="mt-4">
      <Pagination :meta="notificationStore.pagination" @page-change="loadData" />
    </div>

  </DashboardLayout>
</template>
