import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import './assets/css/main.css'

const app = createApp(App)
const pinia = createPinia()

app.use(pinia)

// Router will be added in Phase 5
// app.use(router)

app.mount('#app')
