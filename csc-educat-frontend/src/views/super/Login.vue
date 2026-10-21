<template>
  <v-app >
    <v-row no-gutters class="fill-height">
      <v-col cols="12" lg="8" class="d-flex align-stretch justify-center">
        <v-sheet height="" class="poppins d-flex flex-column align-center justify-center">
          <v-sheet width="380" class="my-auto">
            <!-- <div class=" d-flex justify-center mb-10">
              <div class="">
                <v-img height="55" width="160" :src="`${asset_path}/logo-placeholder.png`"/>
              </div>
            </div> -->
            <div class=" d-flex justify-center mb-8">
              <div class="">
                  <v-img 
                  id="horizontal_logo"
                  width="25vh" 
                  :src="customization.hasOwnProperty('login_logo') ? customization.login_logo : require('../../assets/default/logo.png')"
                  v-on:error="delete customization.login_logo"
                  contain
                  transition="scale-transition"
                />
              </div>
            </div>
            <v-alert type="error" text v-if="errors.email || errors.password">
              <div class="f12 roboto" v-for="(item, i) in errors.email" :key="i">
                {{item}}
              </div>
              <div class="f12 roboto" v-for="(item, i) in errors.password" :key="i">
                {{item}}
              </div>
            </v-alert>
            <v-form class="" @submit.prevent="login">
              <v-text-field outlined dense placeholder="Email Address" 
                type="email"
                v-model="form.email"
                class="login-custom-field my-5 f14 roboto fw500" 
                hide-details
                append-icon="mdi-email-outline"
              />
              
              <v-text-field outlined dense placeholder="Password" 
                :type="!show?'password':'text'"
                v-model="form.password"
                autocomplete="off"
                class="login-custom-field f14 roboto fw500" 
                hide-details 
                :append-icon="!show?'mdi-eye-outline':'mdi-eye-off-outline'"
                @click:append="show=!show"
              />
              <div class="d-flex justify-space-between align-center my-5">
                <div class="d-flex align-center">
                  <v-checkbox hide-details dense/>
                  <div class="mt-2 secondary--text f12">
                    Stay signed in
                  </div>
                </div>
              </div>
              <v-btn large block color="primary" style="border-radius: 10px" type="submit" :loading="loading">Login</v-btn>
            </v-form>
          </v-sheet>  
          <AuthFooter/>
        </v-sheet>
      </v-col>
      <!-- <v-col cols="12" lg="4">
        <v-sheet color="primary" height="100vh" class="d-flex justify-center align-center">
          <v-icon size="160" color="#fff">mdi-image-outline</v-icon>
        </v-sheet>
      </v-col> -->

      <v-col cols="12" lg="4" class="hidden-md-and-down">
        <v-sheet :color="customization.hasOwnProperty('sidebar_color') ? (customization.sidebar_color ? customization.sidebar_color : 'primary') : 'primary'" height="100vh" class="d-flex justify-center align-center">
          <div class="">
              <v-img 
                contain
                max-width="260"
                id="vertical_logo"
                v-if="customization.hasOwnProperty('sidebar_logo')"
                :src="customization.hasOwnProperty('sidebar_logo') ? customization.sidebar_logo : `${asset_path}/favicon-white.png`"
                v-on:error="`${asset_path}/favicon-white.png`"
                transition="scale-transition"
              />
              <v-icon v-else size="160" color="#fff">mdi-shield-account-outline</v-icon>
          </div>
        </v-sheet>
      </v-col>
    </v-row>
  </v-app>
</template>

<script>
import { mapMutations, mapState } from 'vuex'
import AuthFooter from '../../components/AuthFooter.vue'
import axios from '@/services/axios'

export default {
  components: {
    AuthFooter
  },
  data: () => ({
    form: { email: '', password: '' },
    errors: [],
    show: false,
    asset_path: `${process.env.VUE_APP_ASSET_PATH}`,
    loading: false,
    image_keys: ['horizontal_logo', 'vertical_logo', 'sidebar_logo', 'login_logo', 'sidebar_photo', 'favicon'],
    color_keys: ['navigation_background_color', 'navigation_banner_color', 'navigation_link_color', 'navigation_text_icon_color', 'navigation_link_inactive_color', 'sidebar_color', 'mini_navigation_background_color', 'mini_navigation_link_color', 'mini_navigation_icon_color', 'mini_navigation_inactive_link_color', 'primary_buttons_color']
  }),
  methods: {
    ...mapMutations(['signInMutation', 'customizationMutation']),

    async login() {
      this.loading = true
      await this.$api.post('/super-admin/login', this.form)
      .catch(err => {
        this.loading = false
        this.errors = err.response.data.errors
      })
      .then((res) => {
        this.loading = false
        this.signInMutation(res.data.data)
      })
    }
  },
  created() {
    let axiosInstance = axios.create({
      baseURL: process.env.VUE_APP_TENANT_API
    })

    axiosInstance.defaults.headers.common = {
      'X-Requested-With': 'XMLHttpRequest',
      'Access-Control-Allow-Origin': '*'
    }

    axiosInstance.get('tenants/info').then(res => {

      this.image_keys.forEach(key => {
        if(res.data.data.images.findIndex(_item => _item.image_key === key) != -1) {
          this.images[key] = res.data.data.images.find(_item => _item.image_key === key).url
        } else {
          this.images[key] = null
        }
      })

      let custom_string = `{`

      this.image_keys.forEach(key => {
        if(this.images[key]) {
          custom_string = custom_string.concat(`"${key}": "${this.images[key]}",`)
          
          if(key === 'favicon'){
            var favicon = document.querySelector('link[rel="icon"][sizes="16x16"]');
            favicon.href = this.images[key]
            var favicon = document.querySelector('link[rel="icon"][sizes="32x32"]');
            favicon.href = this.images[key]
          }
        }
      })

      this.color_keys.forEach(key => {
        if(res.data.data.hasOwnProperty(key)) {
          if(res.data.data[key] !== '' || !res.data.data[key]) {
            custom_string = custom_string.concat(`"${key}": "${this.color_keys[key]}",`)
          }
        }
      })

      custom_string = custom_string.concat(`}`)

      if(Object.keys(JSON.parse(custom_string)).length !== 0) {
        localStorage.setItem('customization', JSON.stringify(custom_string) )
        this.customizationMutation(JSON.parse(custom_string))
      }

    })

    this.loading = false
  },

  computed: {
    ...mapState({
      customization: (state) => state.customization
    }),
  }
}
</script>

