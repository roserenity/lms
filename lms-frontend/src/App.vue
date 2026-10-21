<template>
  <v-app id="app">
    <router-view/>
  </v-app>
</template>

<style scoped>
</style>

<script>
import { mapMutations, mapState } from 'vuex'
import axios from './services/axios'

export default {
  data: () => ({
    images: {},
    image_keys: ['horizontal_logo', 'vertical_logo', 'sidebar_logo', 'login_logo', 'favicon', 'side_carousel'],
    color_keys: ['navigation_background_color', 'navigation_banner_color', 'navigation_link_color', 'navigation_text_icon_color', 'navigation_link_inactive_color', 'sidebar_color', 'mini_navigation_background_color', 'mini_navigation_link_color', 'mini_navigation_icon_color', 'mini_navigation_inactive_link_color', 'primary_buttons_color']
  }),
  computed: {
    ...mapState({
        user: (state) => state.user,
    }),
  },
  watch: {
    user(){
      if (window.gtag && this.user?.id) {
        console.log('here1')
        gtag('config', process.env.VUE_APP_GA_TRACKING_ID, {
          user_id: this.user?.id,
          cookie_flags: 'SameSite=None;Secure'
        });

        window.gtag('event', 'lms_access', {
          user_id: this.user?.id,
          page_path: this.$route.fullPath,
          page_title: this.$route.name,
          cookie_flags: 'SameSite=None;Secure'
        });
      }
    } 
  },
  created(){
    window.addEventListener('storage', this.onStorageChange);
  },
  mounted(){
    document.title =  process.env.VUE_APP_NAME;
    
    let _customization = localStorage.getItem('customization')
    let _organization = localStorage.getItem('organization')

    if(_organization) {
      this.tenantMutation(localStorage.getItem('organization'))
    }
    if(_customization) {
      this.customizationMutation(JSON.parse(_customization))      
    }

    if (!_customization || !_organization) {
      let axiosInstance = axios.create({
        baseURL: process.env.VUE_APP_TENANT_API
      })

      axiosInstance.defaults.headers.common = {
        'X-Requested-With': 'XMLHttpRequest',
        'Access-Control-Allow-Origin': '*'
      }
      axiosInstance.get('tenants/info').then(res => {

        if(!_organization) {
          localStorage.setItem('organization', res.data.data.info.organization)
        }

        this.image_keys.forEach(key => {
          if(key === 'side_carousel'){
            res.data.data.images.filter(_item => _item.image_key.includes('side_carousel')).forEach((_item, key) => {
              this.images[key] = _item
            })
          } else if(res.data.data.images.findIndex(_item => _item.image_key === key) != -1) {
            this.images[key] = res.data.data.images.find(_item => _item.image_key === key).url
          } else {
            this.images[key] = null
          }
        })

        let customization = {};

        this.image_keys.forEach(key => {
          if(this.images.length > 0) {
            if(key === 'side_carousel'){
              this.images.filter(_item => _item.image_key.includes('side_carousel')).forEach((_item, key) => {
                customization[key] = this.images[key]
              })
            } else if(this.images[key]) {
              customization[key] = this.images[key]

              if(key === 'favicon'){
                var favicon = document.querySelector('link[rel="icon"][sizes="16x16"]');
                favicon.href = this.images[key]
                var favicon = document.querySelector('link[rel="icon"][sizes="32x32"]');
                favicon.href = this.images[key]
              }
            }
          }
        })

        this.color_keys.forEach(key => {
          if(res.data.data.hasOwnProperty(key)) {
            if(res.data.data[key] !== '' || !res.data.data[key]) {
              customization[key] = this.color_keys[key]
            }
          }
        })
        
        if(Object.keys(JSON.parse(custom_string)).length !== 0) {
          localStorage.setItem('customization', JSON.stringify(customization) )
          this.customizationMutation(customization)
        }
      })
    }
  },

  methods: {
    ...mapMutations(['tenantMutation', 'customizationMutation']),

    onStorageChange(event) {
      if (event.key === 'access_payload') {
        if (event.newValue === null) {
          this.message = 'access_payload has been deleted.';
          if(this.user && this.user.hasOwnProperty('access_payload')) {
            localStorage.setItem('access_payload', this.user.access_payload )
          } else {
            localStorage.removeItem('access_payload')
          }
        } else {
          this.message = 'access_payload has been updated.';
        }
      }
    }
  },
  
  destroyed() {
    // Remove the event listener when the component is destroyed
    window.removeEventListener('storage', this.onStorageChange);
  },
}
</script>