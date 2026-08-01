import apiClient from './client'

export const categoriesApi = {
  list: () => apiClient.get('/document-categories')
}
