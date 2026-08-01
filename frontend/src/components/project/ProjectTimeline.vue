<script setup>
import { computed } from 'vue'
import { STATUS_MAP } from '@/utils/constants'
import { formatDate, timeAgo } from '@/utils/formatters'
import { 
  CheckCircleIcon, 
  XCircleIcon, 
  ArrowPathIcon,
  PlayIcon,
  DocumentTextIcon
} from '@heroicons/vue/20/solid'

const props = defineProps({
  reviews: { type: Array, required: true }
})

const getIcon = (statusTo) => {
  switch (statusTo) {
    case 'approved': return CheckCircleIcon
    case 'rejected': return XCircleIcon
    case 'revised': return ArrowPathIcon
    case 'in_review': return PlayIcon
    default: return DocumentTextIcon
  }
}

const getBgColor = (statusTo) => {
  return STATUS_MAP[statusTo]?.bgClass || 'bg-gray-100'
}

const getTextColor = (statusTo) => {
  return STATUS_MAP[statusTo]?.textClass || 'text-gray-500'
}
</script>

<template>
  <div class="flow-root">
    <ul role="list" class="-mb-8">
      <li v-for="(event, eventIdx) in reviews" :key="event.id">
        <div class="relative pb-8">
          <span v-if="eventIdx !== reviews.length - 1" class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true" />
          <div class="relative flex space-x-3">
            <div>
              <span :class="[getBgColor(event.status_to), 'h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white']">
                <component :is="getIcon(event.status_to)" :class="[getTextColor(event.status_to), 'h-5 w-5']" aria-hidden="true" />
              </span>
            </div>
            <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
              <div>
                <p class="text-sm text-gray-500">
                  <span class="font-medium text-gray-900">{{ event.reviewer?.name || 'Sistem' }}</span> mengubah status ke
                  <span class="font-medium text-gray-900">{{ STATUS_MAP[event.status_to]?.label || event.status_to }}</span>
                </p>
                <div v-if="event.notes" class="mt-2 text-sm text-gray-700 bg-gray-50 rounded-md p-3 border border-gray-100">
                  {{ event.notes }}
                </div>
              </div>
              <div class="whitespace-nowrap text-right text-sm text-gray-500">
                <div :title="formatDate(event.created_at)">{{ timeAgo(event.created_at) }}</div>
              </div>
            </div>
          </div>
        </div>
      </li>
      <li v-if="reviews.length === 0">
        <p class="text-sm text-gray-500 py-4 text-center">Belum ada riwayat penilaian.</p>
      </li>
    </ul>
  </div>
</template>
