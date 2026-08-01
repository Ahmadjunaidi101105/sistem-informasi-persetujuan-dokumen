import apiClient from './client'

export const reviewsApi = {
  projectReviews: (projectId) => apiClient.get(`/projects/${projectId}/reviews`),
  allReviews: (params) => apiClient.get('/reviews', { params })
}
