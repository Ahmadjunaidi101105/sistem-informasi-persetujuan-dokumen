<script setup>
import { computed } from 'vue'
import VueApexCharts from 'vue3-apexcharts'

const props = defineProps({
  data: {
    type: Array,
    required: true
  }
})

const chartOptions = computed(() => {
  return {
    chart: { type: 'line', toolbar: { show: false } },
    stroke: { curve: 'smooth', width: 3 },
    colors: ['#3b82f6'],
    xaxis: { categories: props.data.map(item => item.month) },
    markers: { size: 4 }
  }
})

const chartSeries = computed(() => {
  return [{ name: 'Submissions', data: props.data.map(item => item.count) }]
})
</script>

<template>
  <div class="bg-white rounded-lg shadow p-4">
    <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Trend Pengajuan Bulanan</h3>
    <VueApexCharts type="line" height="300" :options="chartOptions" :series="chartSeries" />
  </div>
</template>
