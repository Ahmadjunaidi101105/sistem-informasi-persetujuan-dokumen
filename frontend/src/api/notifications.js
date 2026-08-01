import apiClient from './client'

export const notificationsApi = {
  list: (params) => apiClient.get('/notifications', { params }),
  markAsRead: (id) => apiClient.post(`/notifications/${id}/read`),
  markAllAsRead: () => apiClient.post('/notifications/read-all')
}
