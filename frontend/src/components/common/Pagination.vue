<script setup>
import { computed } from 'vue'

const props = defineProps({
  meta: {
    type: Object,
    required: true,
  },
})

const emit = defineEmits(['page-change'])

const changePage = (page) => {
  if (page === '...') return
  if (page >= 1 && page <= props.meta.last_page && page !== props.meta.current_page) {
    emit('page-change', page)
  }
}

const from = computed(() => {
  if (!props.meta.total) return 0
  return (props.meta.current_page - 1) * props.meta.per_page + 1
})

const to = computed(() => {
  return Math.min(props.meta.current_page * props.meta.per_page, props.meta.total || 0)
})

/**
 * A windowed page list. Rendering every page was unusable on real data —
 * 9.000 records at 15 per page produced 600 buttons and a horizontally
 * scrolling toolbar. Shows first and last page, the current page with two
 * neighbours either side, and an ellipsis for the gaps.
 */
const pages = computed(() => {
  const last = Number(props.meta.last_page) || 1
  const current = Number(props.meta.current_page) || 1
  const window = 2

  if (last <= 7) {
    return Array.from({ length: last }, (_, i) => i + 1)
  }

  const result = [1]
  const start = Math.max(2, current - window)
  const end = Math.min(last - 1, current + window)

  if (start > 2) result.push('...')
  for (let p = start; p <= end; p++) result.push(p)
  if (end < last - 1) result.push('...')
  result.push(last)

  return result
})
</script>

<template>
  <div
    v-if="meta.total > 0"
    class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6"
  >
    <!-- Mobile -->
    <div class="flex flex-1 items-center justify-between sm:hidden">
      <button
        @click="changePage(meta.current_page - 1)"
        :disabled="meta.current_page === 1"
        class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50"
      >
        Sebelumnya
      </button>
      <span class="text-sm text-gray-600">
        Hal. {{ meta.current_page }} / {{ meta.last_page }}
      </span>
      <button
        @click="changePage(meta.current_page + 1)"
        :disabled="meta.current_page === meta.last_page"
        class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50"
      >
        Berikutnya
      </button>
    </div>

    <!-- Desktop -->
    <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
      <div>
        <p class="text-sm text-gray-700">
          Menampilkan
          <span class="font-medium">{{ from }}</span>
          &ndash;
          <span class="font-medium">{{ to }}</span>
          dari
          <span class="font-medium">{{ meta.total }}</span>
          data
        </p>
      </div>
      <div>
        <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Navigasi halaman">
          <button
            @click="changePage(meta.current_page - 1)"
            :disabled="meta.current_page === 1"
            class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0 disabled:cursor-not-allowed disabled:opacity-50"
          >
            <span class="sr-only">Sebelumnya</span>
            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
              <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
            </svg>
          </button>

          <template v-for="(page, i) in pages" :key="`${page}-${i}`">
            <span
              v-if="page === '...'"
              class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-500 ring-1 ring-inset ring-gray-300"
            >
              &hellip;
            </span>
            <button
              v-else
              @click="changePage(page)"
              :aria-current="page === meta.current_page ? 'page' : undefined"
              :class="[
                page === meta.current_page
                  ? 'relative z-10 inline-flex items-center bg-brand-700 px-4 py-2 text-sm font-semibold text-white focus:z-20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-700'
                  : 'relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0',
              ]"
            >
              {{ page }}
            </button>
          </template>

          <button
            @click="changePage(meta.current_page + 1)"
            :disabled="meta.current_page === meta.last_page"
            class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0 disabled:cursor-not-allowed disabled:opacity-50"
          >
            <span class="sr-only">Berikutnya</span>
            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
              <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
            </svg>
          </button>
        </nav>
      </div>
    </div>
  </div>
</template>
