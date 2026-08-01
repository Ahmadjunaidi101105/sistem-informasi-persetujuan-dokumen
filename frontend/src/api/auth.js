import apiClient from './client'

export const authApi = {
  getCsrfCookie: () => apiClient.get('/sanctum/csrf-cookie', { baseURL: import.meta.env.VITE_API_URL ? import.meta.env.VITE_API_URL.replace('/api/v1', '') : '' }),
  login: (credentials) => apiClient.post('/auth/login', credentials),
  register: (data) => apiClient.post('/auth/register', data),
  logout: () => apiClient.post('/auth/logout'),
  getUser: () => apiClient.get('/auth/user')
}
