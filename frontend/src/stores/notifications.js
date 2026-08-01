import { defineStore } from 'pinia'
import { ref } from 'vue'
import { notificationsApi } from '@/api/notifications'

export const useNotificationStore = defineStore('notifications', () => {
  const notifications = ref([])
  const unreadCount = ref(0)
  const loading = ref(false)

  const fetchNotifications = async (params) => {
    loading.value = true
    try {
      const response = await notificationsApi.list(params)
      notifications.value = response.data || []
      return response
    } finally {
      loading.value = false
    }
  }

  const fetchUnreadCount = async () => {
    try {
      const response = await notificationsApi.list({ unread_only: 1, per_page: 1 })
      unreadCount.value = response.meta?.total || 0
    } catch (e) {
      console.error(e)
    }
  }

  const markAsRead = async (id) => {
    try {
      await notificationsApi.markAsRead(id)
      const notif = notifications.value.find(n => n.id === id)
      if (notif && !notif.read_at) {
        notif.read_at = new Date().toISOString()
        unreadCount.value = Math.max(0, unreadCount.value - 1)
      }
    } catch (e) {
      console.error(e)
    }
  }

  const markAllAsRead = async () => {
    try {
      await notificationsApi.markAllAsRead()
      notifications.value.forEach(n => {
        if (!n.read_at) n.read_at = new Date().toISOString()
      })
      unreadCount.value = 0
    } catch (e) {
      console.error(e)
    }
  }

  return {
    notifications,
    unreadCount,
    loading,
    fetchNotifications,
    fetchUnreadCount,
    markAsRead,
    markAllAsRead
  }
})
