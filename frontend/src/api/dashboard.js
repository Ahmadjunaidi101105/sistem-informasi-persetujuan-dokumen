import apiClient from './client'

export const dashboardApi = {
  getPemohonDashboard: () => apiClient.get('/dashboard/pemohon'),
  getPenilaiDashboard: () => apiClient.get('/dashboard/penilai')
}
