<script setup>
defineProps({
  icon: {
    type: String,
    default: null // You can pass SVG path data here or use an icon component slot
  },
  title: {
    type: String,
    required: true
  },
  description: {
    type: String,
    required: true
  },
  actionLabel: {
    type: String,
    default: ''
  }
})

defineEmits(['action'])
</script>

<template>
  <div class="text-center py-12 px-4">
    <div v-if="icon" class="mx-auto h-12 w-12 text-gray-400 mb-4 flex items-center justify-center">
      <svg class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="icon" />
      </svg>
    </div>
    <div v-else class="mx-auto h-12 w-12 text-gray-400 mb-4 flex items-center justify-center">
      <!-- Default Icon (Document) -->
      <svg class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
      </svg>
    </div>
    <h3 class="mt-2 text-sm font-semibold text-gray-900">{{ title }}</h3>
    <p class="mt-1 text-sm text-gray-500">{{ description }}</p>
    <div v-if="actionLabel" class="mt-6">
      <button 
        type="button" 
        @click="$emit('action')"
        class="inline-flex items-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600"
      >
        {{ actionLabel }}
      </button>
    </div>
  </div>
</template>
