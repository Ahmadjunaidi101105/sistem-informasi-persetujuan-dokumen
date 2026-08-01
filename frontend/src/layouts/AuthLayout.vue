<script setup>
import { DocumentTextIcon } from '@heroicons/vue/24/solid'
import { ArrowLeftIcon } from '@heroicons/vue/20/solid'
</script>

<template>
  <div class="relative flex min-h-screen flex-col justify-center overflow-hidden bg-brand-800 py-12 font-display sm:px-6 lg:px-8">
    <!-- Decorative background, mirrors the landing page hero -->
    <div class="pointer-events-none absolute inset-0" aria-hidden="true">
      <div class="absolute -left-24 -top-24 h-96 w-96 rounded-full bg-brand-600/40 blur-3xl"></div>
      <div class="absolute -bottom-32 -right-16 h-96 w-96 rounded-full bg-accent-500/20 blur-3xl"></div>
      <svg class="absolute inset-0 h-full w-full opacity-[0.06]" xmlns="http://www.w3.org/2000/svg">
        <defs>
          <pattern id="auth-grid" width="48" height="48" patternUnits="userSpaceOnUse">
            <path d="M48 0H0V48" fill="none" stroke="white" stroke-width="1" />
          </pattern>
        </defs>
        <rect width="100%" height="100%" fill="url(#auth-grid)" />
      </svg>
    </div>

    <div class="relative sm:mx-auto sm:w-full sm:max-w-md">
      <router-link
        to="/"
        class="mx-auto mb-8 flex w-fit items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-semibold text-brand-100 transition hover:bg-white/10 hover:text-white"
      >
        <ArrowLeftIcon class="h-4 w-4" aria-hidden="true" />
        Kembali ke Beranda
      </router-link>

      <div class="text-center">
        <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-white/10 ring-1 ring-inset ring-white/20 backdrop-blur">
          <DocumentTextIcon class="h-8 w-8 text-white" aria-hidden="true" />
        </span>
        <h1 class="mt-5 text-3xl font-extrabold tracking-tight text-white">SIPDOK</h1>
        <p class="mt-2 text-sm text-brand-100">Sistem Informasi Persetujuan Dokumen</p>
      </div>
    </div>

    <div class="relative mt-8 sm:mx-auto sm:w-full sm:max-w-md">
      <div class="bg-white shadow-2xl ring-1 ring-black/5 sm:rounded-2xl">
        <slot />
      </div>
    </div>
  </div>
</template>
