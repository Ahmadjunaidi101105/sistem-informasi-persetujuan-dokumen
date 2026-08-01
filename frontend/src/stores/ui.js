import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useUiStore = defineStore('ui', () => {
  const sidebarOpen = ref(false)
  const toast = ref({
    message: '',
    type: 'success', // success, error, warning, info
    visible: false
  })
  
  let toastTimeout = null

  const toggleSidebar = (state = null) => {
    if (state !== null) {
      sidebarOpen.value = state
    } else {
      sidebarOpen.value = !sidebarOpen.value
    }
  }

  const showToast = (message, type = 'success', duration = 3000) => {
    if (toastTimeout) {
      clearTimeout(toastTimeout)
    }
    
    toast.value = {
      message,
      type,
      visible: true
    }
    
    toastTimeout = setTimeout(() => {
      hideToast()
    }, duration)
  }

  const hideToast = () => {
    toast.value.visible = false
  }

  return {
    sidebarOpen,
    toast,
    toggleSidebar,
    showToast,
    hideToast
  }
})
