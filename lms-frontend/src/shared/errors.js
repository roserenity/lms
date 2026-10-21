import store from '@/store'

function checker() {
  if(Object.entries(store.getters[`errorsGetters`]).length > 0) return true
  return false;
}

export default {
  install(Vue) {
    Vue.prototype.$errors = checker
  }
}