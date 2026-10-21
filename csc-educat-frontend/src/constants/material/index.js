import Vue from 'vue'

Vue.component('url', () => import('./Url.vue'))
Vue.component('vplayer', () => import('./Video.vue'))
Vue.component('vimeo', () => import('./Vimeo.vue'))
Vue.component('docx', () => import('./Document.vue'))
Vue.component('scorm', () => import('./Scorm.vue')) 
