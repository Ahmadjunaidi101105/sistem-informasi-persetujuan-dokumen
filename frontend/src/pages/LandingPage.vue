<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import {
  ArrowRightIcon,
  Bars3Icon,
  XMarkIcon,
} from '@heroicons/vue/24/outline'
import {
  DocumentTextIcon,
  ClipboardDocumentCheckIcon,
  BellAlertIcon,
  ChartBarSquareIcon,
  ShieldCheckIcon,
  ArrowDownTrayIcon,
  CheckCircleIcon,
  ChevronDownIcon,
} from '@heroicons/vue/24/solid'

const mobileMenuOpen = ref(false)
const scrolled = ref(false)

const onScroll = () => {
  scrolled.value = window.scrollY > 8
}

onMounted(() => {
  window.addEventListener('scroll', onScroll, { passive: true })
  onScroll()
})

onBeforeUnmount(() => {
  window.removeEventListener('scroll', onScroll)
})

const navLinks = [
  { label: 'Beranda', href: '#beranda' },
  { label: 'Fitur', href: '#fitur' },
  { label: 'Alur Proses', href: '#alur' },
  { label: 'Untuk Siapa', href: '#peran' },
  { label: 'FAQ', href: '#faq' },
]

const stats = [
  { value: '6', label: 'Tahapan Status', hint: 'Draft hingga terbit' },
  { value: '10', label: 'Kategori Dokumen', hint: 'Perizinan & kelayakan' },
  { value: '100%', label: 'Terekam Digital', hint: 'Jejak audit lengkap' },
  { value: '24/7', label: 'Akses Mandiri', hint: 'Pantau kapan saja' },
]

const features = [
  {
    icon: DocumentTextIcon,
    title: 'Pengajuan Terstruktur',
    desc: 'Ajukan permohonan lengkap dengan kategori, prioritas, dan lampiran pendukung dalam satu formulir yang terpandu.',
  },
  {
    icon: ClipboardDocumentCheckIcon,
    title: 'Penilaian Berjenjang',
    desc: 'Penilai mengambil berkas, menelaah dokumen, lalu memutuskan: setujui, minta revisi, atau tolak — semuanya tercatat.',
  },
  {
    icon: BellAlertIcon,
    title: 'Notifikasi Real-time',
    desc: 'Setiap perubahan status langsung diberitahukan ke pihak terkait, sehingga tidak ada berkas yang terlupakan.',
  },
  {
    icon: ChartBarSquareIcon,
    title: 'Dasbor & Statistik',
    desc: 'Ringkasan visual distribusi status, tren bulanan, dan tingkat persetujuan untuk mendukung pengambilan keputusan.',
  },
  {
    icon: ShieldCheckIcon,
    title: 'Akses Sesuai Peran',
    desc: 'Pemohon hanya melihat berkasnya sendiri, penilai melihat seluruh pengajuan. Dibatasi ketat di sisi server.',
  },
  {
    icon: ArrowDownTrayIcon,
    title: 'Ekspor Dokumen',
    desc: 'Unduh rekap pengajuan dalam format Excel atau cetak detail permohonan lengkap beserta riwayat dalam PDF.',
  },
]

const steps = [
  {
    num: '01',
    title: 'Ajukan Permohonan',
    desc: 'Pemohon melengkapi data permohonan dan mengunggah dokumen pendukung, lalu mengirimkannya untuk dinilai.',
    side: 'Pemohon',
  },
  {
    num: '02',
    title: 'Telaah oleh Penilai',
    desc: 'Penilai mengambil berkas untuk ditelaah. Satu berkas hanya ditangani satu penilai agar tidak tumpang tindih.',
    side: 'Penilai',
  },
  {
    num: '03',
    title: 'Keputusan Terbit',
    desc: 'Permohonan disetujui, dikembalikan untuk revisi disertai catatan, atau ditolak dengan alasan yang jelas.',
    side: 'Sistem',
  },
]

const roles = [
  {
    name: 'Pemohon',
    tagline: 'Mengajukan & memantau',
    accent: 'brand',
    points: [
      'Membuat dan menyimpan draft permohonan',
      'Mengunggah dokumen pendukung (PDF, DOC, gambar)',
      'Memantau status permohonan secara mandiri',
      'Memperbaiki berkas saat diminta revisi',
      'Mengunduh detail permohonan dalam PDF',
    ],
  },
  {
    name: 'Penilai',
    tagline: 'Menelaah & memutuskan',
    accent: 'accent',
    points: [
      'Melihat seluruh permohonan yang masuk',
      'Mengambil berkas untuk ditelaah',
      'Memberi keputusan disertai catatan penilaian',
      'Meninjau riwayat penilaian sebelumnya',
      'Mengekspor rekap pengajuan ke Excel',
    ],
  },
]

const faqs = [
  {
    q: 'Siapa saja yang dapat mendaftar di SIPDOK?',
    a: 'Pendaftaran mandiri ditujukan untuk Pemohon, yaitu pihak yang mengajukan dokumen kelayakan. Akun Penilai disiapkan oleh pengelola sistem karena menyangkut kewenangan penilaian.',
  },
  {
    q: 'Format dan ukuran dokumen apa yang diterima?',
    a: 'Dokumen dapat diunggah dalam format PDF, DOC, DOCX, JPG, JPEG, atau PNG dengan ukuran maksimal 10 MB per berkas. Setiap permohonan wajib melampirkan minimal satu dokumen sebelum dapat diajukan.',
  },
  {
    q: 'Apa yang terjadi jika permohonan saya diminta revisi?',
    a: 'Status permohonan berubah menjadi Perlu Revisi dan Anda menerima catatan dari penilai. Berkas dapat Anda perbaiki, lengkapi dokumennya, lalu diajukan kembali tanpa perlu membuat permohonan baru.',
  },
  {
    q: 'Apakah permohonan yang ditolak bisa diajukan ulang?',
    a: 'Permohonan yang ditolak bersifat final dan tetap tersimpan sebagai riwayat. Namun Anda tetap dapat membuat permohonan baru dengan perbaikan sesuai alasan penolakan yang diberikan.',
  },
  {
    q: 'Bagaimana keamanan dokumen yang saya unggah?',
    a: 'Dokumen disimpan di luar direktori publik dengan nama berkas teracak, dan hanya dapat diakses oleh pemilik permohonan serta penilai yang berwenang melalui tautan terautentikasi.',
  },
]

const openFaq = ref(0)
const toggleFaq = (i) => {
  openFaq.value = openFaq.value === i ? -1 : i
}

const year = new Date().getFullYear()
</script>

<template>
  <div class="min-h-screen bg-white font-display">
    <!-- ============ NAVBAR ============ -->
    <header
      :class="[
        'fixed inset-x-0 top-0 z-50 transition-all duration-300',
        scrolled ? 'bg-white/95 shadow-sm backdrop-blur border-b border-ink-50' : 'bg-transparent',
      ]"
    >
      <nav class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8" aria-label="Navigasi utama">
        <a href="#beranda" class="flex items-center gap-3">
          <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-700 shadow-lg shadow-brand-700/25">
            <DocumentTextIcon class="h-6 w-6 text-white" aria-hidden="true" />
          </span>
          <span class="leading-tight">
            <span class="block text-lg font-extrabold tracking-tight text-ink-900">SIPDOK</span>
            <span class="block text-[11px] font-medium text-ink-700/70">Persetujuan Dokumen</span>
          </span>
        </a>

        <div class="hidden items-center gap-8 lg:flex">
          <a
            v-for="link in navLinks"
            :key="link.href"
            :href="link.href"
            class="text-sm font-semibold text-ink-700 transition hover:text-brand-700"
          >
            {{ link.label }}
          </a>
        </div>

        <div class="hidden items-center gap-3 lg:flex">
          <router-link
            to="/login"
            class="rounded-lg px-4 py-2.5 text-sm font-semibold text-ink-800 transition hover:bg-ink-50"
          >
            Masuk
          </router-link>
          <router-link
            to="/register"
            class="inline-flex items-center gap-1.5 rounded-lg bg-accent-500 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-accent-500/25 transition hover:bg-accent-600"
          >
            Daftar
            <ArrowRightIcon class="h-4 w-4" aria-hidden="true" />
          </router-link>
        </div>

        <button
          type="button"
          class="rounded-lg p-2 text-ink-800 lg:hidden"
          :aria-expanded="mobileMenuOpen"
          aria-controls="mobile-menu"
          @click="mobileMenuOpen = !mobileMenuOpen"
        >
          <span class="sr-only">{{ mobileMenuOpen ? 'Tutup menu' : 'Buka menu' }}</span>
          <Bars3Icon v-if="!mobileMenuOpen" class="h-6 w-6" aria-hidden="true" />
          <XMarkIcon v-else class="h-6 w-6" aria-hidden="true" />
        </button>
      </nav>

      <!-- Mobile menu -->
      <div v-show="mobileMenuOpen" id="mobile-menu" class="border-t border-ink-50 bg-white px-4 pb-6 pt-2 lg:hidden">
        <a
          v-for="link in navLinks"
          :key="link.href"
          :href="link.href"
          class="block rounded-lg px-3 py-3 text-sm font-semibold text-ink-800 hover:bg-ink-50"
          @click="mobileMenuOpen = false"
        >
          {{ link.label }}
        </a>
        <div class="mt-4 grid grid-cols-2 gap-3">
          <router-link
            to="/login"
            class="rounded-lg border border-ink-50 px-4 py-2.5 text-center text-sm font-semibold text-ink-800"
          >
            Masuk
          </router-link>
          <router-link
            to="/register"
            class="rounded-lg bg-accent-500 px-4 py-2.5 text-center text-sm font-semibold text-white"
          >
            Daftar
          </router-link>
        </div>
      </div>
    </header>

    <main>
      <!-- ============ HERO ============ -->
      <section id="beranda" class="relative overflow-hidden bg-brand-800 pt-32 pb-24 sm:pt-40 sm:pb-32">
        <!-- Decorative background -->
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
          <div class="absolute -left-24 -top-24 h-96 w-96 rounded-full bg-brand-600/40 blur-3xl"></div>
          <div class="absolute -bottom-32 -right-16 h-96 w-96 rounded-full bg-accent-500/20 blur-3xl"></div>
          <svg class="absolute inset-0 h-full w-full opacity-[0.07]" xmlns="http://www.w3.org/2000/svg">
            <defs>
              <pattern id="grid" width="48" height="48" patternUnits="userSpaceOnUse">
                <path d="M48 0H0V48" fill="none" stroke="white" stroke-width="1" />
              </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#grid)" />
          </svg>
        </div>

        <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <div class="grid items-center gap-16 lg:grid-cols-12">
            <div class="animate-rise lg:col-span-7">
              <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-1.5 text-xs font-semibold text-brand-100 ring-1 ring-inset ring-white/20">
                <span class="h-1.5 w-1.5 rounded-full bg-accent-400"></span>
                Layanan Perizinan &amp; Kelayakan Dokumen
              </span>

              <h1 class="mt-6 text-4xl font-extrabold leading-[1.1] tracking-tight text-white sm:text-5xl lg:text-6xl">
                Persetujuan Dokumen
                <span class="relative whitespace-nowrap">
                  <span class="relative text-accent-400">Lebih Cepat</span>
                </span>
                <span class="block">dan Transparan</span>
              </h1>

              <p class="mt-6 max-w-xl text-lg leading-relaxed text-brand-100">
                SIPDOK menyatukan pengajuan, penelaahan, dan persetujuan dokumen kelayakan
                dalam satu alur digital. Tidak ada berkas tercecer, setiap keputusan tercatat
                jelas beserta alasannya.
              </p>

              <div class="mt-10 flex flex-col gap-4 sm:flex-row">
                <router-link
                  to="/register"
                  class="inline-flex items-center justify-center gap-2 rounded-xl bg-accent-500 px-7 py-4 text-base font-bold text-white shadow-xl shadow-accent-900/30 transition hover:bg-accent-600 hover:shadow-2xl"
                >
                  Ajukan Permohonan
                  <ArrowRightIcon class="h-5 w-5" aria-hidden="true" />
                </router-link>
                <router-link
                  to="/login"
                  class="inline-flex items-center justify-center gap-2 rounded-xl bg-white/10 px-7 py-4 text-base font-bold text-white ring-1 ring-inset ring-white/25 backdrop-blur transition hover:bg-white/20"
                >
                  Masuk ke Akun
                </router-link>
              </div>

              <dl class="mt-14 grid max-w-lg grid-cols-2 gap-x-8 gap-y-6 sm:grid-cols-4">
                <div v-for="s in stats" :key="s.label">
                  <dt class="text-3xl font-extrabold text-white">{{ s.value }}</dt>
                  <dd class="mt-1 text-xs font-semibold uppercase tracking-wide text-brand-200">{{ s.label }}</dd>
                </div>
              </dl>
            </div>

            <!-- Status flow preview card -->
            <div class="animate-rise lg:col-span-5">
              <div class="rounded-2xl bg-white/95 p-6 shadow-2xl ring-1 ring-black/5 backdrop-blur sm:p-8">
                <div class="flex items-center justify-between border-b border-ink-50 pb-4">
                  <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-ink-700/60">Contoh Permohonan</p>
                    <p class="mt-1 font-mono text-sm font-bold text-ink-900">PRJ-{{ year }}-00142</p>
                  </div>
                  <span class="rounded-full bg-brand-50 px-3 py-1 text-xs font-bold text-brand-700 ring-1 ring-inset ring-brand-200">
                    Disetujui
                  </span>
                </div>

                <ol class="mt-6 space-y-5">
                  <li
                    v-for="(t, i) in [
                      { label: 'Permohonan diajukan', meta: 'Dokumen lengkap terlampir', done: true },
                      { label: 'Diambil untuk ditelaah', meta: 'Ditangani satu penilai', done: true },
                      { label: 'Catatan revisi ditindaklanjuti', meta: 'Perbaikan dikirim ulang', done: true },
                      { label: 'Keputusan diterbitkan', meta: 'Notifikasi terkirim', done: true },
                    ]"
                    :key="i"
                    class="flex gap-4"
                  >
                    <div class="flex flex-col items-center">
                      <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-brand-600">
                        <CheckCircleIcon class="h-5 w-5 text-white" aria-hidden="true" />
                      </span>
                      <span v-if="i < 3" class="mt-1 h-full w-px flex-1 bg-brand-200"></span>
                    </div>
                    <div class="pb-1">
                      <p class="text-sm font-bold text-ink-900">{{ t.label }}</p>
                      <p class="text-xs text-ink-700/70">{{ t.meta }}</p>
                    </div>
                  </li>
                </ol>

                <div class="mt-6 rounded-xl bg-accent-50 p-4 ring-1 ring-inset ring-accent-200">
                  <p class="text-xs font-semibold text-accent-800">
                    Setiap perpindahan status tercatat lengkap dengan waktu, penilai, dan catatannya.
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Wave divider -->
        <div class="pointer-events-none absolute inset-x-0 bottom-0" aria-hidden="true">
          <svg class="block w-full" viewBox="0 0 1440 80" fill="none" preserveAspectRatio="none" style="height: 60px">
            <path d="M0 80V40C240 10 480 0 720 12C960 24 1200 60 1440 46V80H0Z" fill="white" />
          </svg>
        </div>
      </section>

      <!-- ============ FITUR ============ -->
      <section id="fitur" class="bg-white py-24 sm:py-32">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <div class="mx-auto max-w-2xl text-center">
            <span class="text-sm font-bold uppercase tracking-widest text-brand-600">Fitur Utama</span>
            <h2 class="mt-3 text-3xl font-extrabold tracking-tight text-ink-900 sm:text-4xl">
              Semua yang dibutuhkan dalam satu sistem
            </h2>
            <p class="mt-4 text-lg text-ink-700/80">
              Dirancang mengikuti alur kerja nyata pengelolaan dokumen kelayakan, dari berkas
              masuk sampai keputusan diterbitkan.
            </p>
          </div>

          <div class="mt-16 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <article
              v-for="f in features"
              :key="f.title"
              class="group relative overflow-hidden rounded-2xl border border-ink-50 bg-white p-7 transition duration-300 hover:-translate-y-1 hover:border-brand-200 hover:shadow-xl hover:shadow-brand-900/5"
            >
              <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-50 ring-1 ring-inset ring-brand-100 transition group-hover:bg-brand-600">
                <component :is="f.icon" class="h-6 w-6 text-brand-700 transition group-hover:text-white" aria-hidden="true" />
              </span>
              <h3 class="mt-5 text-lg font-bold text-ink-900">{{ f.title }}</h3>
              <p class="mt-2 text-sm leading-relaxed text-ink-700/80">{{ f.desc }}</p>
              <span class="absolute right-0 top-0 h-24 w-24 translate-x-8 -translate-y-8 rounded-full bg-accent-500/0 transition duration-300 group-hover:bg-accent-500/5"></span>
            </article>
          </div>
        </div>
      </section>

      <!-- ============ ALUR ============ -->
      <section id="alur" class="relative overflow-hidden bg-ink-900 py-24 sm:py-32">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
          <div class="absolute left-1/2 top-0 h-72 w-72 -translate-x-1/2 rounded-full bg-brand-600/20 blur-3xl"></div>
        </div>

        <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <div class="mx-auto max-w-2xl text-center">
            <span class="text-sm font-bold uppercase tracking-widest text-accent-400">Alur Proses</span>
            <h2 class="mt-3 text-3xl font-extrabold tracking-tight text-white sm:text-4xl">
              Tiga langkah, satu alur yang jelas
            </h2>
            <p class="mt-4 text-lg text-white/60">
              Setiap tahap punya penanggung jawab dan aturan perpindahan status yang tegas,
              sehingga tidak ada berkas yang berhenti tanpa kejelasan.
            </p>
          </div>

          <div class="mt-16 grid gap-8 lg:grid-cols-3">
            <div
              v-for="(s, i) in steps"
              :key="s.num"
              class="relative rounded-2xl border border-white/10 bg-white/5 p-8 backdrop-blur transition hover:border-accent-400/40 hover:bg-white/[0.07]"
            >
              <div class="flex items-center justify-between">
                <span class="font-mono text-4xl font-extrabold text-accent-400/90">{{ s.num }}</span>
                <span class="rounded-full bg-brand-600/20 px-3 py-1 text-xs font-bold text-brand-200 ring-1 ring-inset ring-brand-400/30">
                  {{ s.side }}
                </span>
              </div>
              <h3 class="mt-6 text-xl font-bold text-white">{{ s.title }}</h3>
              <p class="mt-3 text-sm leading-relaxed text-white/60">{{ s.desc }}</p>

              <ArrowRightIcon
                v-if="i < steps.length - 1"
                class="absolute -right-4 top-1/2 hidden h-8 w-8 -translate-y-1/2 text-white/15 lg:block"
                aria-hidden="true"
              />
            </div>
          </div>

          <!-- Status chips -->
          <div class="mt-14 flex flex-wrap items-center justify-center gap-3">
            <span
              v-for="(st, i) in ['Draft', 'Diajukan', 'Ditelaah', 'Perlu Revisi', 'Disetujui', 'Ditolak']"
              :key="st"
              :class="[
                'rounded-full px-4 py-2 text-sm font-semibold ring-1 ring-inset',
                i === 4
                  ? 'bg-brand-600/20 text-brand-200 ring-brand-400/40'
                  : i === 5
                    ? 'bg-red-500/10 text-red-300 ring-red-400/30'
                    : i === 3
                      ? 'bg-accent-500/15 text-accent-300 ring-accent-400/30'
                      : 'bg-white/5 text-white/70 ring-white/15',
              ]"
            >
              {{ st }}
            </span>
          </div>
        </div>
      </section>

      <!-- ============ PERAN ============ -->
      <section id="peran" class="bg-ink-50/60 py-24 sm:py-32">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <div class="mx-auto max-w-2xl text-center">
            <span class="text-sm font-bold uppercase tracking-widest text-brand-600">Untuk Siapa</span>
            <h2 class="mt-3 text-3xl font-extrabold tracking-tight text-ink-900 sm:text-4xl">
              Dua peran, kewenangan yang tegas
            </h2>
            <p class="mt-4 text-lg text-ink-700/80">
              Pembatasan akses diberlakukan di sisi server, bukan sekadar menyembunyikan tombol.
            </p>
          </div>

          <div class="mt-16 grid gap-8 lg:grid-cols-2">
            <div
              v-for="r in roles"
              :key="r.name"
              class="overflow-hidden rounded-2xl bg-white shadow-lg ring-1 ring-ink-900/5"
            >
              <div
                :class="[
                  'px-8 py-7',
                  r.accent === 'brand' ? 'bg-brand-700' : 'bg-accent-600',
                ]"
              >
                <h3 class="text-2xl font-extrabold text-white">{{ r.name }}</h3>
                <p class="mt-1 text-sm font-medium text-white/80">{{ r.tagline }}</p>
              </div>
              <ul role="list" class="space-y-4 px-8 py-8">
                <li v-for="p in r.points" :key="p" class="flex gap-3">
                  <CheckCircleIcon
                    :class="[
                      'h-5 w-5 shrink-0',
                      r.accent === 'brand' ? 'text-brand-600' : 'text-accent-500',
                    ]"
                    aria-hidden="true"
                  />
                  <span class="text-sm text-ink-700">{{ p }}</span>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </section>

      <!-- ============ FAQ ============ -->
      <section id="faq" class="bg-white py-24 sm:py-32">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
          <div class="text-center">
            <span class="text-sm font-bold uppercase tracking-widest text-brand-600">FAQ</span>
            <h2 class="mt-3 text-3xl font-extrabold tracking-tight text-ink-900 sm:text-4xl">
              Pertanyaan yang sering diajukan
            </h2>
          </div>

          <dl class="mt-12 divide-y divide-ink-50 border-y border-ink-50">
            <div v-for="(f, i) in faqs" :key="f.q" class="py-2">
              <dt>
                <button
                  type="button"
                  class="flex w-full items-start justify-between gap-4 py-5 text-left"
                  :aria-expanded="openFaq === i"
                  @click="toggleFaq(i)"
                >
                  <span class="text-base font-bold text-ink-900">{{ f.q }}</span>
                  <ChevronDownIcon
                    :class="[
                      'h-5 w-5 shrink-0 text-brand-600 transition-transform duration-200',
                      openFaq === i ? 'rotate-180' : '',
                    ]"
                    aria-hidden="true"
                  />
                </button>
              </dt>
              <dd v-show="openFaq === i" class="pb-5 pr-10">
                <p class="text-sm leading-relaxed text-ink-700/80">{{ f.a }}</p>
              </dd>
            </div>
          </dl>
        </div>
      </section>

      <!-- ============ CTA ============ -->
      <section class="bg-white pb-24 sm:pb-32">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <div class="relative overflow-hidden rounded-3xl bg-brand-800 px-6 py-16 text-center shadow-2xl sm:px-16">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
              <div class="absolute -left-16 -top-16 h-64 w-64 rounded-full bg-brand-600/40 blur-3xl"></div>
              <div class="absolute -bottom-20 -right-10 h-64 w-64 rounded-full bg-accent-500/25 blur-3xl"></div>
            </div>

            <div class="relative mx-auto max-w-2xl">
              <h2 class="text-3xl font-extrabold tracking-tight text-white sm:text-4xl">
                Siap mengajukan permohonan Anda?
              </h2>
              <p class="mt-4 text-lg text-brand-100">
                Buat akun Pemohon, lengkapi berkas, dan pantau proses penilaiannya
                sampai keputusan diterbitkan.
              </p>
              <div class="mt-10 flex flex-col justify-center gap-4 sm:flex-row">
                <router-link
                  to="/register"
                  class="inline-flex items-center justify-center gap-2 rounded-xl bg-accent-500 px-8 py-4 text-base font-bold text-white shadow-xl shadow-accent-900/30 transition hover:bg-accent-600"
                >
                  Daftar Sekarang
                  <ArrowRightIcon class="h-5 w-5" aria-hidden="true" />
                </router-link>
                <router-link
                  to="/login"
                  class="inline-flex items-center justify-center rounded-xl bg-white/10 px-8 py-4 text-base font-bold text-white ring-1 ring-inset ring-white/25 transition hover:bg-white/20"
                >
                  Sudah punya akun
                </router-link>
              </div>
            </div>
          </div>
        </div>
      </section>
    </main>

    <!-- ============ FOOTER ============ -->
    <footer class="bg-ink-950 py-14">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col items-start justify-between gap-10 sm:flex-row">
          <div class="max-w-sm">
            <div class="flex items-center gap-3">
              <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-700">
                <DocumentTextIcon class="h-6 w-6 text-white" aria-hidden="true" />
              </span>
              <span class="text-lg font-extrabold tracking-tight text-white">SIPDOK</span>
            </div>
            <p class="mt-4 text-sm leading-relaxed text-white/50">
              Sistem Informasi Persetujuan Dokumen — mengelola pengajuan, penilaian,
              dan persetujuan dokumen kelayakan secara digital dan terekam.
            </p>
          </div>

          <div class="grid grid-cols-2 gap-10 sm:gap-16">
            <div>
              <h3 class="text-xs font-bold uppercase tracking-widest text-white/40">Navigasi</h3>
              <ul role="list" class="mt-4 space-y-3">
                <li v-for="link in navLinks" :key="link.href">
                  <a :href="link.href" class="text-sm text-white/60 transition hover:text-accent-400">{{ link.label }}</a>
                </li>
              </ul>
            </div>
            <div>
              <h3 class="text-xs font-bold uppercase tracking-widest text-white/40">Akun</h3>
              <ul role="list" class="mt-4 space-y-3">
                <li>
                  <router-link to="/login" class="text-sm text-white/60 transition hover:text-accent-400">Masuk</router-link>
                </li>
                <li>
                  <router-link to="/register" class="text-sm text-white/60 transition hover:text-accent-400">Daftar Pemohon</router-link>
                </li>
              </ul>
            </div>
          </div>
        </div>

        <div class="mt-12 border-t border-white/10 pt-8">
          <p class="text-xs text-white/40">&copy; {{ year }} SIPDOK. Seluruh hak cipta dilindungi.</p>
        </div>
      </div>
    </footer>
  </div>
</template>
