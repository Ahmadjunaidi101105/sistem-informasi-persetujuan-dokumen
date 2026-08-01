<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-700 to-blue-900 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
      <h2 class="text-3xl font-extrabold text-white">SIPDOK</h2>
      <p class="mt-2 text-sm text-blue-200">Sistem Informasi Persetujuan Dokumen</p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
      <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
        <slot />
      </div>
    </div>
  </div>
</template>
