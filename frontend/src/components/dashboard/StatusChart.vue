<script setup>
import { computed } from 'vue'
import VueApexCharts from 'vue3-apexcharts'

const props = defineProps({
  data: {
    type: Object,
    required: true
  }
})

const chartOptions = computed(() => {
  return {
    chart: { type: 'bar', toolbar: { show: false } },
    colors: ['#3b82f6', '#f59e0b', '#10b981', '#ef4444', '#f97316', '#6b7280'],
    plotOptions: { bar: { borderRadius: 4, horizontal: false, distributed: true } },
    dataLabels: { enabled: false },
    legend: { show: false },
    xaxis: {
      categories: Object.keys(props.data).map(key => {
        const map = { draft: 'Draft', submitted: 'Submitted', in_review: 'In Review', approved: 'Approved', revised: 'Revised', rejected: 'Rejected' }
        return map[key] || key
      })
    }
  }
})

const chartSeries = computed(() => {
  return [{
    name: 'Projects',
    data: Object.values(props.data)
  }]
})
</script>

<template>
  <div class="bg-white rounded-lg shadow p-4">
    <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Distribusi Status</h3>
    <VueApexCharts type="bar" height="300" :options="chartOptions" :series="chartSeries" />
  </div>
</template>
