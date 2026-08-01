<script setup>
import { computed } from 'vue'
import EmptyState from './EmptyState.vue'
import LoadingSpinner from './LoadingSpinner.vue'
import { ChevronUpIcon, ChevronDownIcon } from '@heroicons/vue/20/solid'

const props = defineProps({
  columns: {
    type: Array,
    required: true
  },
  data: {
    type: Array,
    required: true
  },
  loading: {
    type: Boolean,
    default: false
  },
  sortBy: {
    type: String,
    default: ''
  },
  sortOrder: {
    type: String,
    default: 'asc'
  }
})

const emit = defineEmits(['sort'])

const handleSort = (column) => {
  if (column.sortable) {
    emit('sort', column.key)
  }
}
</script>

<template>
  <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg bg-white relative">
    
    <div v-if="loading && data.length > 0" class="absolute inset-0 bg-white/50 backdrop-blur-sm z-10 flex items-center justify-center">
      <LoadingSpinner size="lg" text="Loading..." />
    </div>

    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-300">
        <thead class="bg-gray-50">
          <tr>
            <th 
              v-for="col in columns" 
              :key="col.key" 
              scope="col" 
              class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6"
              :class="[col.sortable ? 'cursor-pointer hover:bg-gray-100' : '']"
              @click="handleSort(col)"
            >
              <div class="group inline-flex items-center">
                {{ col.label }}
                <span v-if="col.sortable" class="ml-2 flex-none rounded text-gray-900">
                  <template v-if="sortBy === col.key">
                    <ChevronUpIcon v-if="sortOrder === 'asc'" class="h-4 w-4 bg-gray-200" aria-hidden="true" />
                    <ChevronDownIcon v-else class="h-4 w-4 bg-gray-200" aria-hidden="true" />
                  </template>
                  <template v-else>
                    <ChevronDownIcon class="h-4 w-4 text-gray-400 opacity-0 group-hover:opacity-100" aria-hidden="true" />
                  </template>
                </span>
              </div>
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 bg-white">
          <tr v-if="loading && data.length === 0">
            <td :colspan="columns.length" class="py-12">
               <div class="flex justify-center">
                 <LoadingSpinner size="md" text="Loading data..." />
               </div>
            </td>
          </tr>
          <tr v-else-if="!loading && data.length === 0">
            <td :colspan="columns.length" class="py-8">
              <EmptyState 
                title="No Data Found" 
                description="There are no records to display at this time."
              />
            </td>
          </tr>
          <template v-else>
            <tr v-for="(row, rowIndex) in data" :key="row.id || rowIndex" class="hover:bg-gray-50">
              <td v-for="col in columns" :key="col.key" class="whitespace-nowrap py-4 pl-4 pr-3 text-sm text-gray-900 sm:pl-6">
                <!-- Fallback if no slot provided -->
                <slot :name="`col-${col.key}`" :row="row" :value="row[col.key]">
                  {{ row[col.key] }}
                </slot>
              </td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>
  </div>
</template>
