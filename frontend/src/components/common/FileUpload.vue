<script setup>
import { ref, computed } from 'vue'
import { DocumentIcon, XMarkIcon, ArrowUpTrayIcon } from '@heroicons/vue/24/outline'
import { formatFileSize } from '@/utils/formatters'

const props = defineProps({
  accept: { type: String, default: '*/*' },
  maxSize: { type: Number, default: 10 * 1024 * 1024 }, // 10MB
  multiple: { type: Boolean, default: false },
  existingFiles: { type: Array, default: () => [] }
})

const emit = defineEmits(['files-changed', 'file-removed'])

const fileInput = ref(null)
const isDragging = ref(false)
const selectedFiles = ref([])

const handleFileSelect = (event) => {
  const files = Array.from(event.target.files)
  addFiles(files)
  if (fileInput.value) fileInput.value.value = ''
}

const handleDrop = (event) => {
  isDragging.value = false
  const files = Array.from(event.dataTransfer.files)
  addFiles(files)
}

const addFiles = (files) => {
  const validFiles = files.filter(file => {
    return file.size <= props.maxSize
  })

  if (!props.multiple) {
    selectedFiles.value = validFiles.slice(0, 1)
  } else {
    selectedFiles.value = [...selectedFiles.value, ...validFiles]
  }

  emit('files-changed', selectedFiles.value)
}

const removeFile = (index) => {
  selectedFiles.value.splice(index, 1)
  emit('files-changed', selectedFiles.value)
}

const removeExisting = (id) => {
  emit('file-removed', id)
}
</script>

<template>
  <div class="w-full">
    <div 
      @dragover.prevent="isDragging = true"
      @dragleave.prevent="isDragging = false"
      @drop.prevent="handleDrop"
      @click="fileInput.click()"
      :class="[
        'mt-2 flex justify-center rounded-lg border border-dashed px-6 py-10 transition-colors cursor-pointer',
        isDragging ? 'border-brand-500 bg-brand-50' : 'border-gray-900/25 bg-white hover:bg-gray-50'
      ]"
    >
      <div class="text-center">
        <ArrowUpTrayIcon class="mx-auto h-12 w-12 text-gray-300" aria-hidden="true" />
        <div class="mt-4 flex text-sm leading-6 text-gray-600 justify-center">
          <label class="relative cursor-pointer rounded-md bg-transparent font-semibold text-brand-700 focus-within:outline-none focus-within:ring-2 focus-within:ring-brand-700 focus-within:ring-offset-2 hover:text-brand-500">
            <span>Upload a file</span>
            <input 
              ref="fileInput"
              type="file" 
              class="sr-only" 
              :accept="accept"
              :multiple="multiple"
              @change="handleFileSelect"
            />
          </label>
          <p class="pl-1">or drag and drop</p>
        </div>
        <p class="text-xs leading-5 text-gray-600">
          Format: {{ accept.replace(/\./g, '').toUpperCase() || 'Any' }} up to {{ formatFileSize(maxSize) }}
        </p>
      </div>
    </div>

    <!-- File List -->
    <ul v-if="existingFiles.length > 0 || selectedFiles.length > 0" role="list" class="mt-4 divide-y divide-gray-100 rounded-md border border-gray-200">
      
      <!-- Existing Files -->
      <li v-for="file in existingFiles" :key="'ex-'+file.id" class="flex items-center justify-between py-3 pl-3 pr-4 text-sm leading-6">
        <div class="flex w-0 flex-1 items-center">
          <DocumentIcon class="h-5 w-5 flex-shrink-0 text-gray-400" aria-hidden="true" />
          <div class="ml-4 flex min-w-0 flex-1 gap-2">
            <span class="truncate font-medium">{{ file.original_name }}</span>
            <span class="flex-shrink-0 text-gray-400">{{ file.file_size_formatted }}</span>
          </div>
        </div>
        <div class="ml-4 flex-shrink-0 flex space-x-2">
          <a :href="file.download_url" target="_blank" class="font-medium text-brand-700 hover:text-brand-500">Download</a>
          <button @click.prevent="removeExisting(file.id)" type="button" class="font-medium text-red-600 hover:text-red-500">Remove</button>
        </div>
      </li>

      <!-- New Selected Files -->
      <li v-for="(file, index) in selectedFiles" :key="'new-'+index" class="flex items-center justify-between py-3 pl-3 pr-4 text-sm leading-6 bg-brand-50">
        <div class="flex w-0 flex-1 items-center">
          <DocumentIcon class="h-5 w-5 flex-shrink-0 text-brand-400" aria-hidden="true" />
          <div class="ml-4 flex min-w-0 flex-1 gap-2">
            <span class="truncate font-medium text-brand-800">{{ file.name }}</span>
            <span class="flex-shrink-0 text-brand-500">{{ formatFileSize(file.size) }}</span>
          </div>
        </div>
        <div class="ml-4 flex-shrink-0">
          <button @click.prevent="removeFile(index)" type="button" class="rounded-md bg-transparent text-gray-400 hover:text-gray-500 focus:outline-none">
            <span class="sr-only">Remove</span>
            <XMarkIcon class="h-5 w-5 text-red-500" aria-hidden="true" />
          </button>
        </div>
      </li>

    </ul>
  </div>
</template>
