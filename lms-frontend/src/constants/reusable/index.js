import Vue from 'vue'

Vue.component('breadcrumbs', () => import('./Breadcrumb.vue'))

// props id, type, store, action 
Vue.component('delete-dialog', () => import('./DeleteDialog.vue'))
Vue.component('DeleteDialog', () => import('./DeleteDialogV2.vue'))

Vue.component('circular', () => import('./CircularProg.vue'))

Vue.component('delete-toast', () => import('./ToastDelete.vue'))
Vue.component('avatar', () => import('./Avatar.vue'))
