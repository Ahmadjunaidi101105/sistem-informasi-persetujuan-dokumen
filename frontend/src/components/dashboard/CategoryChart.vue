<script setup>
import { computed } from 'vue'
import VueApexCharts from 'vue3-apexcharts'

const props = defineProps({
  data: {
    type: Array, // Array of { category: string, count: number }
    required: true
  }
})

const chartOptions = computed(() => {
  return {
    chart: { type: 'bar', toolbar: { show: false } },
    plotOptions: { bar: { horizontal: true, borderRadius: 4, barHeight: '50%' } },
    colors: ['#8b5cf6'], // purple
    dataLabels: { enabled: true, style: { colors: ['#fff'] } },
    xaxis: { categories: props.data.map(item => item.category) },
    grid: { xaxis: { lines: { show: false } }, yaxis: { lines: { show: false } } }
  }
})

const chartSeries = computed(() => {
  return [{ name: 'Projects', data: props.data.map(item => item.count) }]
})
</script>

<template>
  <div class="bg-white rounded-lg shadow p-4">
    <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">By Category</h3>
    <VueApexCharts type="bar" height="300" :options="chartOptions" :series="chartSeries" />
  </div>
</template>
