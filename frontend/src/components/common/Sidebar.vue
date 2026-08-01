<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useUiStore } from '@/stores/ui'
import { 
  HomeIcon, 
  DocumentTextIcon, 
  DocumentDuplicateIcon, 
  ClipboardDocumentCheckIcon,
  XMarkIcon
} from '@heroicons/vue/24/outline'

const route = useRoute()
const authStore = useAuthStore()
const uiStore = useUiStore()

const navigation = computed(() => {
  if (authStore.isPemohon) {
    return [
      { name: 'Dashboard', href: '/pemohon/dashboard', icon: HomeIcon },
      { name: 'Permohonan Dokumen', href: '/pemohon/projects', icon: DocumentTextIcon },
    ]
  } else if (authStore.isPenilai) {
    return [
      { name: 'Dashboard', href: '/penilai/dashboard', icon: HomeIcon },
      { name: 'Daftar Pengajuan', href: '/penilai/submissions', icon: DocumentDuplicateIcon },
      { name: 'Riwayat Penilaian', href: '/penilai/history', icon: ClipboardDocumentCheckIcon },
    ]
  }
  return []
})

const isCurrentRoute = (path) => {
  return route.path.startsWith(path)
}
</script>

<template>
  <div>
    <!-- Mobile sidebar overlay -->
    <div v-if="uiStore.sidebarOpen" class="relative z-50 md:hidden">
      <div class="fixed inset-0 bg-gray-900/80 transition-opacity" @click="uiStore.toggleSidebar(false)"></div>
      <div class="fixed inset-0 flex">
        <div class="relative mr-16 flex w-full max-w-xs flex-1 flex-col bg-white pt-5 pb-4">
          <div class="absolute top-0 right-0 -mr-12 pt-2">
            <button type="button" class="ml-1 flex h-10 w-10 items-center justify-center rounded-full focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white" @click="uiStore.toggleSidebar(false)">
              <span class="sr-only">Close sidebar</span>
              <XMarkIcon class="h-6 w-6 text-white" aria-hidden="true" />
            </button>
          </div>
          <div class="flex flex-shrink-0 items-center px-4">
            <span class="text-2xl font-bold text-blue-700">SIPDOK</span>
          </div>
          <div class="mt-5 h-0 flex-1 overflow-y-auto">
            <nav class="space-y-1 px-2">
              <router-link v-for="item in navigation" :key="item.name" :to="item.href" :class="[isCurrentRoute(item.href) ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900', 'group flex items-center rounded-md px-2 py-2 text-base font-medium']">
                <component :is="item.icon" :class="[isCurrentRoute(item.href) ? 'text-blue-700' : 'text-gray-400 group-hover:text-gray-500', 'mr-4 h-6 w-6 flex-shrink-0']" aria-hidden="true" />
                {{ item.name }}
              </router-link>
            </nav>
          </div>
          <div class="flex flex-shrink-0 border-t border-gray-200 p-4">
            <div class="flex-shrink-0 w-full group block">
              <div class="flex items-center">
                <div>
                  <div class="inline-block h-9 w-9 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold">
                    {{ authStore.userName.charAt(0).toUpperCase() }}
                  </div>
                </div>
                <div class="ml-3">
                  <p class="text-sm font-medium text-gray-700 group-hover:text-gray-900">{{ authStore.userName }}</p>
                  <p class="text-xs font-medium text-gray-500 group-hover:text-gray-700 capitalize">{{ authStore.userRole }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Desktop sidebar -->
    <div class="hidden md:fixed md:inset-y-0 md:flex md:w-64 md:flex-col">
      <div class="flex flex-grow flex-col overflow-y-auto border-r border-gray-200 bg-white pt-5 pb-4 shadow-sm">
        <div class="flex flex-shrink-0 items-center px-4">
          <span class="text-2xl font-bold text-blue-700">SIPDOK</span>
        </div>
        <div class="mt-8 flex flex-1 flex-col">
          <nav class="flex-1 space-y-1 px-2 pb-4">
            <router-link v-for="item in navigation" :key="item.name" :to="item.href" :class="[isCurrentRoute(item.href) ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900', 'group flex items-center rounded-md px-2 py-2 text-sm font-medium']">
              <component :is="item.icon" :class="[isCurrentRoute(item.href) ? 'text-blue-700' : 'text-gray-400 group-hover:text-gray-500', 'mr-3 h-5 w-5 flex-shrink-0']" aria-hidden="true" />
              {{ item.name }}
            </router-link>
          </nav>
        </div>
        <div class="flex flex-shrink-0 border-t border-gray-200 p-4">
          <div class="flex-shrink-0 w-full group block">
            <div class="flex items-center">
              <div>
                <div class="inline-block h-9 w-9 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold">
                  {{ authStore.userName.charAt(0).toUpperCase() }}
                </div>
              </div>
              <div class="ml-3">
                <p class="text-sm font-medium text-gray-700 group-hover:text-gray-900 truncate max-w-[150px]">{{ authStore.userName }}</p>
                <p class="text-xs font-medium text-gray-500 group-hover:text-gray-700 capitalize">{{ authStore.userRole }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
