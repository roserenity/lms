import Vue from 'vue'
import VueGtag from "vue-gtag";
import router from '../router'

Vue.use(VueGtag, {
  config: { 
    id: "UA-203440965-1",
    params: {
      send_page_view: true
    }
  }
}, router);