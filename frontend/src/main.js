import { createApp } from 'vue'
import { createPinia } from 'pinia'

import App from './App.vue'
import router from './router/index.js'
import { globalErrorHandler, setupAuthExpiredListener } from './utils/errorHandler.js'
import './assets/base.css'

const app   = createApp(App)
const pinia = createPinia()

app.use(pinia)
app.use(router)

app.config.errorHandler = globalErrorHandler

setupAuthExpiredListener()

app.mount('#app')
