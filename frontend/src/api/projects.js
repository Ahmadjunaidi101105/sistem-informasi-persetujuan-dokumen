import apiClient from './client'

export const projectsApi = {
  list: (params) => apiClient.get('/projects', { params }),
  create: (data) => apiClient.post('/projects', data),
  get: (id) => apiClient.get(`/projects/${id}`),
  update: (id, data) => apiClient.put(`/projects/${id}`, data),
  delete: (id) => apiClient.delete(`/projects/${id}`),
  submit: (id) => apiClient.post(`/projects/${id}/submit`),
  // Review actions (take-review, approve, revise, reject) live on reviewsApi,
  // which is what the assessment pages actually import.
}
