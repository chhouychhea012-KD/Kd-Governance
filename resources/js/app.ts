import './bootstrap'
import '../css/app.css'

import { createInertiaApp } from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import { createApp, h } from 'vue'
import type { DefineComponent } from 'vue'

import moment from 'moment'
import { createPinia } from 'pinia'
import { ZiggyVue } from 'ziggy-js'

import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'
import 'vuetify/styles'
import '@mdi/font/css/materialdesignicons.css'

import { VDateInput } from 'vuetify/labs/VDateInput'

const pinia = createPinia()

const vuetify = createVuetify({
  components: {
    ...components,
    VDateInput,
  },
  directives,
  defaults: {
    VBtn: {
      variant: 'filled',
    },
  },
  theme: {
    defaultTheme: 'light',
    themes: {
      light: {
        colors: {
          primary: '#1976D2',
        },
      },
    },
  },
})

const appName =
  (import.meta.env && import.meta.env.VITE_APP_NAME) || 'KD Governance'

createInertiaApp({
  title: (title) => (title ? `${title} - ${appName}` : appName),

  resolve: (name) =>
    resolvePageComponent(
      `./pages/${name}.vue`,
      import.meta.glob<DefineComponent>('./pages/**/*.vue')
    ),

  setup({ el, App, props, plugin }) {
    const vueApp = createApp({ render: () => h(App, props) })

    vueApp.config.globalProperties.$moment = moment

    vueApp
      .use(plugin)
      .use(vuetify)
      .use(ZiggyVue)
      .use(pinia)
      .mount(el)
  },

  progress: {
    color: '#4B5563',
  },
})
