import axios from 'axios'
import apiClient from './client'

export const authApi = {
  getCsrfCookie: () => axios.get('/sanctum/csrf-cookie', { withCredentials: true }),
  login: (credentials) => apiClient.post('/auth/login', credentials),
  register: (data) => apiClient.post('/auth/register', data),
  logout: () => apiClient.post('/auth/logout'),
  getUser: () => apiClient.get('/auth/user')
}
