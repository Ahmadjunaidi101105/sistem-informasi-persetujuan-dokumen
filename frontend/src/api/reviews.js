import apiClient from './client'

/**
 * Review actions live here rather than on projectsApi: every component that
 * drives the assessment workflow (SubmissionListPage, ReviewPage,
 * ReviewHistoryPage) reaches for reviewsApi.
 */
export const reviewsApi = {
  /** Semua penilaian; mendukung filter reviewer_id, status_to, search, tanggal. */
  list: (params) => apiClient.get('/reviews', { params }),

  /** Riwayat penilaian untuk satu permohonan. */
  projectReviews: (projectId) => apiClient.get(`/projects/${projectId}/reviews`),

  /** Mengambil permohonan untuk dinilai (mengunci berkas ke penilai ini). */
  takeReview: (projectId) => apiClient.post(`/projects/${projectId}/take-review`),

  approve: (projectId, data) => apiClient.post(`/projects/${projectId}/approve`, data),
  revise: (projectId, data) => apiClient.post(`/projects/${projectId}/revise`, data),
  reject: (projectId, data) => apiClient.post(`/projects/${projectId}/reject`, data),
}
