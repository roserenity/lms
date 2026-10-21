<template>
  <section>
    <v-app>
      <v-row no-gutters class="fill-height">
        <v-col cols="12" lg="8" class="d-flex align-stretch justify-center">
          <v-sheet width="375" class="poppins d-flex flex-column align-center justify-center" v-if="!loading">
            <v-container class="my-auto">
              <div class=" d-flex flex-column justify-center">
                  <div class="d-flex justify-center mb-2">
                      <v-img 
                      id="horizontal_logo"
                      height="15vh" 
                      :src="customization.hasOwnProperty('login_logo') ? customization.login_logo : require('../assets/default/logo.png')"
                      v-on:error="require('../assets/default/logo.png')"
                      contain
                      transition="scale-transition"
                      />
                  </div>
                  <label class="secondary-2--text f12 fw600 poppins">CODE</label>
                  <v-textarea
                      :value="$route.params.certificate_uuid"
                      type="text"
                      class="f12 general-custom-field roboto fw500 secondary-1--text mb-3"
                      hide-details
                      outlined
                      dense
                      auto-grow
                      readonly
                      :rows="1"
                  />
                  <section class="d-flex flex-row success--text f10 fw600" v-if="success">
                      <v-icon color="success" small class="mr-1">
                          mdi-check-circle
                      </v-icon>
                      This certification is valid.
                  </section>
                  <section class="d-flex flex-row error--text f10 fw600" v-if="!success">
                      <v-icon color="error" small class="mr-1">
                          mdi-alert-circle
                      </v-icon>
                      This certification is invalid.
                  </section>
                  <v-divider class="my-3"/>

                  <label class="secondary-2--text f14 fw600 poppins text-center" v-if="!success">No details found.</label>

                  <section class="d-flex flex-column" v-if="success">
                      <label class="primary--text f16 fw600 poppins mb-3">Certificate Details</label>
                      <label class="secondary-2--text f12 fw600 poppins">FULL NAME</label>
                      <v-textarea
                          readonly
                          type="text"
                          class="f12 general-custom-field roboto fw500 secondary-1--text mb-3"
                          hide-details
                          outlined
                          dense
                          auto-grow
                          :rows="1"
                          :value="`${certificate.user.first_name ? certificate.user.first_name : 'User'} ${certificate.user.middle_name ? getMiddleInitials(certificate.user.middle_name) : ''} ${certificate.user.last_name ? certificate.user.last_name : 'Account'} ${certificate.user.suffix ? certificate.user.suffix : ''}`"
                      />
                      <label class="secondary-2--text f12 fw600 poppins">CERTIFICATE</label>
                      <v-textarea
                          readonly
                          type="text"
                          class="f12 general-custom-field roboto fw500 secondary-1--text mb-3"
                          hide-details
                          outlined
                          dense
                          auto-grow
                          :rows="1"
                          :value="certificate.course.title ? certificate.course.title : 'Untitled'"
                      />
                      <label class="secondary-2--text f12 fw600 poppins">DATE ISSUED</label>
                      <v-textarea
                          readonly
                          type="text"
                          class="f12 general-custom-field roboto fw500 secondary-1--text mb-3"
                          hide-details
                          outlined
                          dense
                          auto-grow
                          :rows="1"
                          :value="$dateFormat.mmDDyy(certificate.image.updated_at)"
                      />
                      <ButtonPrimary
                          @click="modal = true"
                          :loading="loading1"
                          :label="'View Certificate'"
                      />
                  </section>
              </div>
            </v-container>  
          </v-sheet>
          <v-sheet v-else class="poppins d-flex flex-column align-center justify-center">
              <label class="secondary-2--text f14 fw600 poppins text-center">Validating ...</label>
              <circular />
          </v-sheet>
              
        </v-col>
        <v-col cols="12" lg="4" class="hidden-md-and-down">
          <v-carousel v-if="Object.keys(customization).filter(_key => _key.includes('side_carousel')).length > 0" hide-delimiters cycle :show-arrows="false" height="100vh">
            <v-carousel-item :key="index" v-for="(item, index) in Object.keys(customization).filter(_key => _key.includes('side_carousel'))">
              <v-sheet
                color="transparent"
                height="100vh"
                class="d-flex align-center justify-center"
              >
              <v-img
                  height="100vh"
                  :src="customization[item]"
                />
              </v-sheet>
            </v-carousel-item>
          </v-carousel>
          <v-sheet v-else
            :color="customization.hasOwnProperty('sidebar_color') ? (customization.sidebar_color ? customization.sidebar_color : 'primary') : 'primary'" height="100vh" class="d-flex justify-center align-center">
              <v-img 
                contain
                max-width="250"
                id="vertical_logo"
                v-if="customization.hasOwnProperty('sidebar_logo')"
                :src="customization.sidebar_logo"
                v-on:error="delete customization.sidebar_logo"
                transition="scale-transition"
              />
              <v-icon v-else size="160" color="#fff">mdi-certificate-outline</v-icon>
          </v-sheet>
        </v-col>
      </v-row>
    </v-app>
    <v-dialog 
      v-if="!loading && success"
      v-model="modal"
      :width="$vuetify.breakpoint.mobile ? '80vw' : '40vw'"
        persistent>
        <v-card>
            <section v-if="!loading" class="d-flex flex-column align-center poppins justify-center success--text py-1 mx-1">
                <v-btn icon @click="modal = false" class="ml-auto" dense>
                    <v-icon>mdi-close</v-icon>
                </v-btn>
                <div class="container-frame">
                    <iframe 
                        class="responsive-iframe"
                        :src="`${certificate.image.url}`">
                    </iframe>
                </div>
            </section>
        </v-card>
    </v-dialog>
  </section>
</template>

<style scoped>
.container-frame {
  position: relative;
  overflow: hidden;
  width: 100%; /* 16:9 Aspect Ratio (divide 9 by 16 = 0.5625) */
  height: 500px;
}

.responsive-iframe {
  height: 500px;
}

</style>

<script>
import { mapMutations, mapActions, mapState } from 'vuex'
import axios from '@/services/axios'

export default {
  data: () => ({
    success: null,
    errors: [],
    certificate: null,
    loading: true,
    asset_path: `${process.env.VUE_APP_ASSET_PATH}`,
    images: {},
    modal: false,
    image_keys: ['horizontal_logo', 'vertical_logo', 'sidebar_logo', 'login_logo', 'sidebar_photo', 'favicon'],
    color_keys: ['navigation_background_color', 'navigation_banner_color', 'navigation_link_color', 'navigation_text_icon_color', 'navigation_link_inactive_color', 'sidebar_color', 'mini_navigation_background_color', 'mini_navigation_link_color', 'mini_navigation_icon_color', 'mini_navigation_inactive_link_color', 'primary_buttons_color']
  }),
  created() {
    this.getCustomizationAction()
    let axiosInstance = axios.create({
      baseURL: process.env.VUE_APP_TENANT_API
    })

    axiosInstance.defaults.headers.common = {
      'X-Requested-With': 'XMLHttpRequest',
      'Access-Control-Allow-Origin': '*'
    }
    axiosInstance.get('tenants/info').then(res => {

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

      let custom_string = `{`

      this.image_keys.forEach(key => {
        if(this.images.length > 0) {
          if(key === 'side_carousel'){
            this.images.filter(_item => _item.image_key.includes('side_carousel')).forEach((_item, key) => {
              custom_string = custom_string.concat(`"${key}": "${this.images[key]}",`)
            })
          } else if(this.images[key]) {
            custom_string = custom_string.concat(`"${key}": "${this.images[key]}",`)

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
            custom_string = custom_string.concat(`"${key}": "${this.color_keys[key]}",`)
          }
        }
      })

      custom_string = custom_string.concat(`}`)

      if(Object.keys(JSON.parse(custom_string)).length !== 0) {
        localStorage.setItem('customization', JSON.stringify(custom_string) )
        this.customizationMutation(JSON.parse(custom_string))
      }

      localStorage.setItem('organization', res.data.data.info.organization )
      this.tenantMutation(res.data.data.info.organization)

    })
    this.loading = false
  },
  methods: {
    ...mapMutations(['signInMutation', 'tenantMutation', 'customizationMutation']),
    ...mapActions(['getCustomizationAction']),

    getMiddleInitials(_middle_name) {
      let initials = []
      let _names = _middle_name.split(' ')
      _names.forEach(item => {
        initials.push(item[0])
      })
      let _middle_initial = initials.join('. ')
      return  `${_middle_initial}.`
    }
  },

  computed: {
    ...mapState({
      customization: (state) => state.customization
    }),
  },

  async mounted() {
    this.loading = true

    return await new Promise((resolve, reject) => {
        this.$api.get(`tenants/view/certificate/${this.$route.params.certificate_uuid}`).then(res => {
            this.success = true
            this.loading = false

            this.certificate = res.data.data

            resolve(res.data.data)

        }).catch(errs => {
            this.success = false
            this.loading = false
        })
    })
    
  }
}
</script>