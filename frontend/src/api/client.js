import axios from 'axios'

const apiClient = axios.create({
  baseURL: import.meta.env.VITE_API_URL || '/api/v1',
  // No cookies involved: the API is stateless and authenticates via the
  // Authorization header set in the request interceptor below.
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json',
  },
})

// Request interceptor
apiClient.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => Promise.reject(error)
)

// Response interceptor
apiClient.interceptors.response.use(
  (response) => response.data,
  (error) => {
    // Lazily import UI store to avoid Pinia not initialized error
    import('@/stores/ui').then(({ useUiStore }) => {
      const uiStore = useUiStore()

      if (!error.response) {
        uiStore.showToast('Tidak dapat terhubung ke server', 'error')
        return
      }

      const status = error.response.status
      
      if (status === 401) {
        localStorage.removeItem('token')
        window.location.href = '/login'
      } else if (status === 403) {
        uiStore.showToast('Anda tidak memiliki akses', 'error')
      } else if (status === 404) {
        uiStore.showToast('Data tidak ditemukan', 'error')
      } else if (status === 429) {
        uiStore.showToast('Terlalu banyak request, coba lagi nanti', 'error')
      } else if (status >= 500) {
        uiStore.showToast('Terjadi kesalahan server', 'error')
      }
    }).catch(() => {})

    // For 422, we return the error so the component can handle form validation
    return Promise.reject(error)
  }
)

export default apiClient
