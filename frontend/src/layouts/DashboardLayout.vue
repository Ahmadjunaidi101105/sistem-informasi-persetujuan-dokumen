<script setup>
import Sidebar from '@/components/common/Sidebar.vue'
import Navbar from '@/components/common/Navbar.vue'
import { useUiStore } from '@/stores/ui'
import { onMounted } from 'vue'

const uiStore = useUiStore()

onMounted(() => {
  uiStore.toggleSidebar(false)
})
</script>

<template>
  <div class="min-h-screen bg-gray-50 flex">
    <Sidebar />
    <div class="flex-1 flex flex-col min-w-0 md:pl-64">
      <Navbar />
      <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
        <slot />
      </main>
    </div>
  </div>
</template>
