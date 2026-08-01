<script setup>
import { computed } from 'vue'

const props = defineProps({
  title: { type: String, required: true },
  value: { type: [Number, String], required: true },
  icon: { type: Object, required: true }, // Heroicon component
  color: { type: String, default: 'blue' },
  trend: { type: Number, default: null }
})

const colorClass = computed(() => {
  const map = {
    blue: 'text-blue-600 bg-blue-100',
    amber: 'text-amber-600 bg-amber-100',
    green: 'text-green-600 bg-green-100',
    red: 'text-red-600 bg-red-100'
  }
  return map[props.color] || map.blue
})
</script>

<template>
  <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
    <div class="flex items-center">
      <div :class="['flex-shrink-0 rounded-md p-3', colorClass]">
        <component :is="icon" class="h-6 w-6" aria-hidden="true" />
      </div>
      <div class="ml-5 w-0 flex-1">
        <dl>
          <dt class="truncate text-sm font-medium text-gray-500">{{ title }}</dt>
          <dd>
            <div class="text-2xl font-bold text-gray-900">{{ value }}</div>
          </dd>
        </dl>
      </div>
    </div>
    <div class="mt-4" v-if="trend !== null">
      <p :class="[trend >= 0 ? 'text-green-600' : 'text-red-600', 'flex items-baseline text-sm font-semibold']">
        <span v-if="trend > 0">+</span>{{ trend }}%
        <span class="ml-2 text-sm font-normal text-gray-500">dari bulan lalu</span>
      </p>
    </div>
  </div>
</template>
