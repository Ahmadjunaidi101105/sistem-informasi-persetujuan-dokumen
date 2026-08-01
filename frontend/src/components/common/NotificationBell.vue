<script setup>
import { onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useNotificationStore } from '@/stores/notifications'
import { Popover, PopoverButton, PopoverPanel } from '@headlessui/vue'
import { BellIcon } from '@heroicons/vue/24/outline'
import { timeAgo } from '@/utils/formatters'

const router = useRouter()
const notificationStore = useNotificationStore()
let intervalId = null

const loadData = async () => {
  await notificationStore.fetchUnreadCount()
  await notificationStore.fetchNotifications({ per_page: 5 })
}

onMounted(() => {
  loadData()
  intervalId = setInterval(() => {
    loadData()
  }, 30000)
})

onUnmounted(() => {
  if (intervalId) clearInterval(intervalId)
})

const handleMarkAsRead = async (id) => {
  await notificationStore.markAsRead(id)
}

const handleMarkAllAsRead = async () => {
  await notificationStore.markAllAsRead()
}

const viewAll = (close) => {
  close()
  router.push('/notifications')
}
</script>

<template>
  <Popover class="relative">
    <PopoverButton class="relative rounded-full bg-white p-1 text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
      <span class="sr-only">View notifications</span>
      <BellIcon class="h-6 w-6" aria-hidden="true" />
      <span v-if="notificationStore.unreadCount > 0" class="absolute top-0 right-0 block h-4 w-4 rounded-full bg-red-500 text-[10px] font-bold text-white text-center leading-4 ring-2 ring-white">
        {{ notificationStore.unreadCount > 9 ? '9+' : notificationStore.unreadCount }}
      </span>
    </PopoverButton>

    <transition enter-active-class="transition ease-out duration-200" enter-from-class="opacity-0 translate-y-1" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition ease-in duration-150" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 translate-y-1">
      <PopoverPanel v-slot="{ close }" class="absolute right-0 z-10 mt-2 w-80 sm:w-96 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none">
        <div class="p-4 border-b border-gray-100 flex justify-between items-center">
          <h3 class="text-sm font-semibold text-gray-900">Notifikasi</h3>
          <button v-if="notificationStore.unreadCount > 0" @click="handleMarkAllAsRead" class="text-xs text-blue-600 hover:text-blue-800">
            Tandai Semua Dibaca
          </button>
        </div>
        
        <div class="max-h-96 overflow-y-auto">
          <div v-if="notificationStore.loading && notificationStore.notifications.length === 0" class="p-4 text-center text-sm text-gray-500">
            Loading...
          </div>
          <div v-else-if="notificationStore.notifications.length === 0" class="p-4 text-center text-sm text-gray-500">
            Tidak ada notifikasi
          </div>
          <div v-else class="divide-y divide-gray-100">
            <div v-for="notif in notificationStore.notifications" :key="notif.id" :class="[!notif.read_at ? 'bg-blue-50' : '', 'p-4 hover:bg-gray-50 transition duration-150']">
              <div class="flex justify-between items-start">
                <div class="flex-1 min-w-0 pr-4">
                  <p class="text-sm font-medium text-gray-900 truncate">
                    {{ notif.data.title || 'Notifikasi' }}
                  </p>
                  <p class="text-xs text-gray-500 mt-1 line-clamp-2">
                    {{ notif.data.message }}
                  </p>
                  <p class="text-[10px] text-gray-400 mt-1">
                    {{ timeAgo(notif.created_at) }}
                  </p>
                </div>
                <div v-if="!notif.read_at">
                  <button @click.stop="handleMarkAsRead(notif.id)" class="text-xs text-blue-600 hover:text-blue-800" title="Tandai dibaca">
                    <span class="h-2 w-2 bg-blue-600 rounded-full inline-block"></span>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="p-2 border-t border-gray-100 text-center">
          <button @click="viewAll(close)" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
            Lihat Semua
          </button>
        </div>
      </PopoverPanel>
    </transition>
  </Popover>
</template>
