<script setup>
import { computed } from 'vue'
import { STATUS_MAP } from '@/utils/constants'

const props = defineProps({
  status: {
    type: String,
    required: true
  }
})

const statusConfig = computed(() => {
  return STATUS_MAP[props.status] || { 
    label: props.status, 
    bgClass: 'bg-gray-100', 
    textClass: 'text-gray-800' 
  }
})
</script>

<template>
  <span 
    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
    :class="[statusConfig.bgClass, statusConfig.textClass]"
  >
    {{ statusConfig.label }}
  </span>
</template>
