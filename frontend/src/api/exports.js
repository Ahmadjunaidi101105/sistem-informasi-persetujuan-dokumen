import apiClient from './client'

export const exportsApi = {
  exportExcel: (params) => apiClient.get('/export/projects', { params, responseType: 'blob' }),
  exportPdf: (projectId) => apiClient.get(`/export/projects/${projectId}/pdf`, { responseType: 'blob' })
}
