import apiClient from './client'

export const projectsApi = {
  list: (params) => apiClient.get('/projects', { params }),
  create: (data) => apiClient.post('/projects', data),
  get: (id) => apiClient.get(`/projects/${id}`),
  update: (id, data) => apiClient.put(`/projects/${id}`, data),
  delete: (id) => apiClient.delete(`/projects/${id}`),
  submit: (id) => apiClient.post(`/projects/${id}/submit`),
  takeReview: (id) => apiClient.post(`/projects/${id}/take-review`),
  approve: (id, data) => apiClient.post(`/projects/${id}/approve`, data),
  revise: (id, data) => apiClient.post(`/projects/${id}/revise`, data),
  reject: (id, data) => apiClient.post(`/projects/${id}/reject`, data)
}
