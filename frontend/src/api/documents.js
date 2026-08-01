import apiClient from './client'

export const documentsApi = {
  upload: (projectId, data) => apiClient.post(`/projects/${projectId}/documents`, data, {
    headers: {
      'Content-Type': 'multipart/form-data'
    }
  }),
  list: (projectId) => apiClient.get(`/projects/${projectId}/documents`),
  download: (documentId) => apiClient.get(`/documents/${documentId}/download`, { responseType: 'blob' }),
  delete: (documentId) => apiClient.delete(`/documents/${documentId}`)
}
