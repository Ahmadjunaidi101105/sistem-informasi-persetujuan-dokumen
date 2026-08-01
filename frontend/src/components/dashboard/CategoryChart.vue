<script setup>
import { computed } from 'vue'
import VueApexCharts from 'vue3-apexcharts'

const props = defineProps({
  /** [{ name: string, count: number }] as returned by the dashboard endpoint. */
  data: {
    type: Array,
    default: () => [],
  },
})

// Show the busiest categories first so the chart stays readable.
const rows = computed(() =>
  [...props.data]
    .map((item) => ({ name: item.name, count: Number(item.count) || 0 }))
    .sort((a, b) => b.count - a.count)
)

const hasData = computed(() => rows.value.some((r) => r.count > 0))

const chartOptions = computed(() => ({
  chart: { type: 'bar', toolbar: { show: false }, fontFamily: 'inherit' },
  plotOptions: { bar: { horizontal: true, borderRadius: 5, barHeight: '60%', distributed: false } },
  colors: ['#039855'],
  dataLabels: { enabled: false },
  grid: { borderColor: '#f1f2f3', xaxis: { lines: { show: true } }, yaxis: { lines: { show: false } } },
  tooltip: { y: { formatter: (v) => `${v} permohonan` } },
  xaxis: {
    categories: rows.value.map((r) => r.name),
    labels: { style: { colors: '#6b7280', fontSize: '12px' } },
    axisBorder: { show: false },
    axisTicks: { show: false },
  },
  yaxis: { labels: { style: { colors: '#6b7280', fontSize: '12px' } } },
}))

const chartSeries = computed(() => [{ name: 'Permohonan', data: rows.value.map((r) => r.count) }])
</script>

<template>
  <div class="rounded-xl bg-white p-5 shadow ring-1 ring-ink-900/5">
    <h3 class="mb-4 text-base font-bold text-ink-900">Permohonan per Kategori</h3>
    <VueApexCharts v-if="hasData" type="bar" height="300" :options="chartOptions" :series="chartSeries" />
    <p v-else class="flex h-[300px] items-center justify-center text-sm text-ink-700/60">
      Belum ada data untuk ditampilkan.
    </p>
  </div>
</template>
