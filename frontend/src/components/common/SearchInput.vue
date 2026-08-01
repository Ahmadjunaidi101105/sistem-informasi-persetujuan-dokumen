<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  modelValue: {
    type: String,
    default: ''
  },
  placeholder: {
    type: String,
    default: 'Search...'
  },
  debounceMs: {
    type: Number,
    default: 300
  }
})

const emit = defineEmits(['update:modelValue'])

const internalValue = ref(props.modelValue)
let timeout = null

watch(() => props.modelValue, (newVal) => {
  internalValue.value = newVal
})

const onInput = (event) => {
  internalValue.value = event.target.value
  
  if (timeout) clearTimeout(timeout)
  
  timeout = setTimeout(() => {
    emit('update:modelValue', internalValue.value)
  }, props.debounceMs)
}

const clear = () => {
  internalValue.value = ''
  emit('update:modelValue', '')
  if (timeout) clearTimeout(timeout)
}
</script>

<template>
  <div class="relative rounded-md shadow-sm">
    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
      <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
        <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd" />
      </svg>
    </div>
    <input 
      type="text" 
      :value="internalValue"
      @input="onInput"
      class="block w-full rounded-md border-0 py-1.5 pl-10 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6" 
      :placeholder="placeholder" 
    />
    <div v-if="internalValue" class="absolute inset-y-0 right-0 flex items-center pr-3">
      <button type="button" @click="clear" class="text-gray-400 hover:text-gray-500 focus:outline-none">
        <span class="sr-only">Clear search</span>
        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
        </svg>
      </button>
    </div>
  </div>
</template>
