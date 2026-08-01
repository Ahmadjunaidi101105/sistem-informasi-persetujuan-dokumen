<script setup>
import { computed } from 'vue'
import VueApexCharts from 'vue3-apexcharts'
import { STATUS_MAP } from '@/utils/constants'

const props = defineProps({
  /**
   * Either [{ year, month, count }] (pemohon) or
   * [{ year, month, count, status }] (penilai, grouped per decision).
   */
  data: {
    type: Array,
    default: () => [],
  },
  title: {
    type: String,
    default: 'Tren Pengajuan Bulanan',
  },
})

const MONTH_LABELS = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des']

const SERIES_COLORS = {
  approved: '#039855',
  revised: '#f7941d',
  rejected: '#ef4444',
  _default: '#039855',
}

const periodKey = (row) => `${Number(row.year)}-${String(Number(row.month)).padStart(2, '0')}`

const periodLabel = (key) => {
  const [year, month] = key.split('-')
  return `${MONTH_LABELS[Number(month) - 1]} ${year}`
}

// Every period present in the payload, chronologically ordered.
const periods = computed(() => [...new Set(props.data.map(periodKey))].sort())

// The penilai payload splits each period across decision statuses.
const isGrouped = computed(() => props.data.some((row) => 'status' in row))

const chartSeries = computed(() => {
  if (!isGrouped.value) {
    const totals = new Map(props.data.map((row) => [periodKey(row), Number(row.count) || 0]))
    return [{ name: 'Pengajuan', data: periods.value.map((p) => totals.get(p) || 0) }]
  }

  const statuses = [...new Set(props.data.map((row) => row.status))]
  return statuses.map((status) => {
    const totals = new Map(
      props.data.filter((row) => row.status === status).map((row) => [periodKey(row), Number(row.count) || 0])
    )
    return {
      name: STATUS_MAP[status]?.label || status,
      data: periods.value.map((p) => totals.get(p) || 0),
    }
  })
})

const seriesColors = computed(() => {
  if (!isGrouped.value) return [SERIES_COLORS._default]
  return [...new Set(props.data.map((row) => row.status))].map(
    (status) => SERIES_COLORS[status] || SERIES_COLORS._default
  )
})

const hasData = computed(() => periods.value.length > 0)

const chartOptions = computed(() => ({
  chart: { type: 'line', toolbar: { show: false }, fontFamily: 'inherit' },
  stroke: { curve: 'smooth', width: 3 },
  colors: seriesColors.value,
  markers: { size: 4, strokeWidth: 0 },
  dataLabels: { enabled: false },
  grid: { borderColor: '#f1f2f3' },
  legend: { show: isGrouped.value, position: 'bottom' },
  xaxis: {
    categories: periods.value.map(periodLabel),
    labels: { style: { colors: '#6b7280', fontSize: '12px' } },
    axisBorder: { show: false },
    axisTicks: { show: false },
  },
  yaxis: { labels: { style: { colors: '#6b7280' } } },
  tooltip: { y: { formatter: (v) => `${v} permohonan` } },
}))
</script>

<template>
  <div class="rounded-xl bg-white p-5 shadow ring-1 ring-ink-900/5">
    <h3 class="mb-4 text-base font-bold text-ink-900">{{ title }}</h3>
    <VueApexCharts v-if="hasData" type="line" height="300" :options="chartOptions" :series="chartSeries" />
    <p v-else class="flex h-[300px] items-center justify-center text-sm text-ink-700/60">
      Belum ada data untuk ditampilkan.
    </p>
  </div>
</template>
