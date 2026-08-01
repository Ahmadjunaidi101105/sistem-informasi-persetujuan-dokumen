<script setup>
import { computed } from 'vue'
import VueApexCharts from 'vue3-apexcharts'
import { STATUS_MAP } from '@/utils/constants'

const props = defineProps({
  data: {
    type: Object,
    default: () => ({}),
  },
})

/**
 * Colour is resolved per status key rather than by position: the API returns
 * status counts in arbitrary key order, so a positional palette would paint
 * "approved" with the colour meant for "draft".
 */
const STATUS_COLORS = {
  draft: '#9ca3af',
  submitted: '#0284c7',
  in_review: '#f59e0b',
  approved: '#039855',
  revised: '#f7941d',
  rejected: '#ef4444',
}

// Keep a stable, meaningful order that follows the workflow.
const ORDER = ['draft', 'submitted', 'in_review', 'approved', 'revised', 'rejected']

const entries = computed(() =>
  ORDER.filter((key) => key in props.data).map((key) => ({
    key,
    label: STATUS_MAP[key]?.label || key,
    color: STATUS_COLORS[key] || '#9ca3af',
    count: Number(props.data[key]) || 0,
  }))
)

const hasData = computed(() => entries.value.some((e) => e.count > 0))

const chartOptions = computed(() => ({
  chart: { type: 'bar', toolbar: { show: false }, fontFamily: 'inherit' },
  colors: entries.value.map((e) => e.color),
  plotOptions: { bar: { borderRadius: 6, horizontal: false, distributed: true, columnWidth: '55%' } },
  dataLabels: { enabled: false },
  legend: { show: false },
  grid: { borderColor: '#f1f2f3' },
  tooltip: { y: { formatter: (v) => `${v} permohonan` } },
  xaxis: {
    categories: entries.value.map((e) => e.label),
    labels: { style: { colors: '#6b7280', fontSize: '12px' } },
    axisBorder: { show: false },
    axisTicks: { show: false },
  },
  yaxis: { labels: { style: { colors: '#6b7280' } } },
}))

const chartSeries = computed(() => [
  { name: 'Permohonan', data: entries.value.map((e) => e.count) },
])
</script>

<template>
  <div class="rounded-xl bg-white p-5 shadow ring-1 ring-ink-900/5">
    <h3 class="mb-4 text-base font-bold text-ink-900">Distribusi Status</h3>
    <VueApexCharts v-if="hasData" type="bar" height="300" :options="chartOptions" :series="chartSeries" />
    <p v-else class="flex h-[300px] items-center justify-center text-sm text-ink-700/60">
      Belum ada data untuk ditampilkan.
    </p>
  </div>
</template>
