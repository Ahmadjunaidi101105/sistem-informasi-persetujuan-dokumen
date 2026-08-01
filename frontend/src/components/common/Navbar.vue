<script setup>
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useUiStore } from '@/stores/ui'
import NotificationBell from './NotificationBell.vue'
import { Bars3Icon } from '@heroicons/vue/24/outline'
import { Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/vue'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()
const uiStore = useUiStore()

const pageTitle = computed(() => {
  return route.meta.title || route.name?.toString().split('.').pop() || 'SIPDOK'
})

const handleLogout = async () => {
  await authStore.logout()
  router.push('/login')
}
</script>

<template>
  <div class="sticky top-0 z-10 flex h-16 flex-shrink-0 bg-white shadow border-b border-gray-200">
    <button type="button" class="border-r border-gray-200 px-4 text-gray-500 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500 md:hidden" @click="uiStore.toggleSidebar(true)">
      <span class="sr-only">Open sidebar</span>
      <Bars3Icon class="h-6 w-6" aria-hidden="true" />
    </button>
    
    <div class="flex flex-1 justify-between px-4 sm:px-6 lg:px-8">
      <div class="flex flex-1 items-center">
        <h1 class="text-xl font-semibold text-gray-900 capitalize">{{ pageTitle }}</h1>
      </div>
      <div class="ml-4 flex items-center md:ml-6 space-x-4">
        
        <NotificationBell />

        <!-- Profile dropdown -->
        <Menu as="div" class="relative ml-3">
          <div>
            <MenuButton class="flex max-w-xs items-center rounded-full bg-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
              <span class="sr-only">Open user menu</span>
              <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold hover:bg-blue-200 transition">
                {{ authStore.userName.charAt(0).toUpperCase() }}
              </div>
            </MenuButton>
          </div>
          <transition enter-active-class="transition ease-out duration-100" enter-from-class="transform opacity-0 scale-95" enter-to-class="transform opacity-100 scale-100" leave-active-class="transition ease-in duration-75" leave-from-class="transform opacity-100 scale-100" leave-to-class="transform opacity-0 scale-95">
            <MenuItems class="absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none">
              <div class="px-4 py-2 border-b border-gray-100">
                <p class="text-sm font-medium text-gray-900 truncate">{{ authStore.userName }}</p>
                <p class="text-xs text-gray-500 truncate">{{ authStore.user?.email }}</p>
              </div>
              <MenuItem v-slot="{ active }">
                <button @click="handleLogout" :class="[active ? 'bg-gray-100' : '', 'block w-full text-left px-4 py-2 text-sm text-gray-700']">
                  Sign out
                </button>
              </MenuItem>
            </MenuItems>
          </transition>
        </Menu>
      </div>
    </div>
  </div>
</template>
