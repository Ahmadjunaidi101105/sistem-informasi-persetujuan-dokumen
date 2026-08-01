import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { authApi } from '@/api/auth'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const token = ref(localStorage.getItem('token') || null)
  const loading = ref(false)

  const isAuthenticated = computed(() => !!token.value)
  const userRole = computed(() => {
    if (!user.value || !user.value.roles) return null;
    return user.value.roles[0] || null;
  })
  const isPemohon = computed(() => userRole.value === 'pemohon')
  const isPenilai = computed(() => userRole.value === 'penilai')
  const userName = computed(() => user.value?.name || '')

  const hasRole = (role) => {
    if (!user.value || !user.value.roles) return false;
    return user.value.roles.includes(role);
  }

  const setAuth = (authData) => {
    user.value = authData.user
    token.value = authData.token
    localStorage.setItem('token', authData.token)
  }

  const clearAuth = () => {
    user.value = null
    token.value = null
    localStorage.removeItem('token')
  }

  const login = async (credentials) => {
    loading.value = true
    try {
      const response = await authApi.login(credentials)
      setAuth(response.data)
      return response
    } finally {
      loading.value = false
    }
  }

  const register = async (data) => {
    loading.value = true
    try {
      return await authApi.register(data)
    } finally {
      loading.value = false
    }
  }

  const logout = async () => {
    loading.value = true
    try {
      if (token.value) {
        await authApi.logout()
      }
    } catch (e) {
      console.error('Logout error', e)
    } finally {
      clearAuth()
      loading.value = false
    }
  }

  const fetchUser = async () => {
    if (!token.value) return null
    loading.value = true
    try {
      const response = await authApi.getUser()
      user.value = response.data.user
      return user.value
    } catch (e) {
      clearAuth()
      throw e
    } finally {
      loading.value = false
    }
  }

  const checkAuth = async () => {
    if (token.value && !user.value) {
      try {
        await fetchUser()
      } catch (e) {
        // failed to fetch user, token might be invalid
      }
    }
  }

  return {
    user,
    token,
    loading,
    isAuthenticated,
    isPemohon,
    isPenilai,
    userName,
    userRole,
    hasRole,
    login,
    register,
    logout,
    fetchUser,
    checkAuth
  }
})
