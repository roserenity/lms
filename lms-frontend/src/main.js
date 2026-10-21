import Vue from 'vue'
import App from './App.vue'
import router from './router'
import store from './store'
import vuetify from './plugins/vuetify'
import './shared'

Vue.config.productionTip = false

if('serviceWorker' in navigator) {
  window.addEventListener('load', async() => {
    try {
      const registrations = await navigator.serviceWorker.getRegistrations();
      for (let registration of registrations) {
        await registration.unregister();
        console.log('Service worker unregistered:', registration);
      }
    } catch (err) {
      console.error('Error during service worker unregistration:', err);
    }
  })
}

new Vue({
  vuetify,
  router,
  store,
  render: h => h(App)
}).$mount('#app')
