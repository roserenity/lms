<template>
  <v-app v-if="!loading">
    <v-row no-gutters class="fill-height">
      <v-col cols="12" lg="8" class="d-flex align-stretch justify-center">
        <v-sheet width="375" class="poppins d-flex flex-column align-center justify-center" >
          <v-container class="my-auto">
            <div class=" d-flex justify-center mb-8">
              <div class="">
                  <v-img 
                  id="horizontal_logo"
                  width="25vh" 
                  :src="customization.hasOwnProperty('login_logo') ? customization.login_logo : require('../assets/default/logo.png')"
                  v-on:error="delete customization.login_logo"
                  contain
                  transition="scale-transition"
                />
              </div>
            </div>
            <alert 
              v-if="msg.show"
              :show = "msg.show"
              :text = "msg.text"
              :type = "msg.type"
            />
            <alert 
              v-if="errs && (errs.email || errs.password)"
              :show = "errs && (errs.email || errs.password)"
              text = "No such email or password"
              type = "error"
            />
            <alert 
              v-if="errs && errs.status"
              :show = "errs && errs.status"
              :text = "errs.status[0]"
              type = "error"
            />
            <v-form ref="form" class="mb-5" @submit.prevent="login" :class="$vuetify.breakpoint.mobile ? 'pa-5': ''">
              <v-text-field outlined dense placeholder="Email Address" 
                type="email"
                autofocus
                v-model="form.email"
                class="login-custom-field my-5 f14 roboto fw500 secondary-1--text" 
                append-icon="mdi-email-outline"
                hide-details="auto"
                :rules="[() => !!form.email || 'The email field is required.']"
              />
              
              <v-text-field outlined dense placeholder="Password" 
                :type="!show?'password':'text'"
                v-model="form.password"
                class="login-custom-field f14 roboto fw500 secondary-1--text" 
                :append-icon="!show?'mdi-eye-outline':'mdi-eye-off-outline'"
                @click:append="show=!show"
                hide-details="auto"
                :rules="[() => !!form.password || 'The password field is required.']"
                autocomplete="off"
              />
              <div class="d-flex justify-space-between align-center my-5">
                <!-- <div class="d-flex align-center">
                  <v-checkbox hide-details dense/>
                  <div class="mt-2 secondary--text f12">
                    Stay signed in
                  </div>
                </div> -->
                <v-spacer/>
                <router-link to="/forgot-password" class="text-decoration-none">
                  <div class="mt-1 mx-1 primary--text f12">
                    Forgot Password
                  </div>
                </router-link>
              </div>
              <v-btn 
                large 
                block 
                color="primary" 
                style="border-radius: 10px" 
                type="submit"
              >
                <v-progress-circular
                v-if="logging"
                indeterminate
                width="5"
                color="#fff"/>
                <div class="" v-else>
                  LOG IN
                </div>
              </v-btn>
            </v-form>
            <div class="poppins text-center secondary--text f12">
              Not registered yet? 
              <router-link to="/signup" class="text-decoration-none">
                  <span class="primary--text">Sign up</span> 
              </router-link>
            </div>
          </v-container>  
          <AuthFooter/>
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
          <!-- <v-icon size="160" color="#fff">mdi-image-outline</v-icon> -->
            <v-img 
              contain
              max-width="250"
              id="vertical_logo"
              v-if="customization.hasOwnProperty('sidebar_logo')"
              :src="customization.sidebar_logo"
              v-on:error="delete customization.sidebar_logo"
              transition="scale-transition"
            />
        </v-sheet>
      </v-col>
    </v-row>
    
    <v-dialog v-model="dialog" persistent max-width="550" transition="scale-transition">
        <v-card>
            <v-card-text >
                <div class="pa-5 d-flex flex-column align-center" max-height="500">
                    <v-img 
                        :src="require('@/assets/images/login-loading.png')"
                        width="320"
                        contain
                        eager
                    />
                    <v-progress-linear
                        indeterminate
                        :color="landing ? 'l-primary' :'primary'"
                        rounded
                        height="4"
                        class="my-3"
                        style="width: 320px;"
                    />
                    
                    <h3 class="text-center secondary-1--text mb-2">
                        Hang tight! We’re getting you set up.
                    </h3>
                    <div class="text-center f12 secondary-1--text" style="width: 320px;">
                        Just a moment! We're getting everything ready in the background to make sure things go smoothly. We appreciate your patience!
                    </div>
                </div>
            </v-card-text>
        </v-card>
    </v-dialog>
  </v-app>
</template>

<script>
import { mapActions, mapMutations, mapState } from 'vuex'
import AuthFooter from '../components/AuthFooter.vue'
export default {
  components: {
    AuthFooter
  },
  data: () => ({
    form: { email: '', password: '' },
    show: false,
    logging: false,
    loading: true,
    asset_path: `${process.env.VUE_APP_ASSET_PATH}`,
    landing_path: `${process.env.VUE_APP_LANDING_DOMAIN}`,
    images: {},
    errs: [],
    msg: {
      show: false,
      text: '',
      type: ''
    },
    dialog: false,
  }),
  created() {
    this.loading = true
    this.getCustomizationAction().then(res => {
      this.images = res
      this.loading = false
    })
  },
  methods: {
    ...mapMutations(['signInMutation', 'tenantMutation', 'customizationMutation']),
    ...mapActions(['getCustomizationAction', 'loginAction']),
    ...mapActions('usr', ['addToCart']),

   login() {
      this.errs = []
      if(this.$refs.form.validate()){
        this.logging=true
        // this.dialog = true
        // setTimeout(() => {
          this.loginAction(this.form).then(res => {
            if( res.status === 'active') {
              this.signInMutation(res)
              if(res.role === 'USER') {
                  if(localStorage.getItem('cart')) {
                      if(JSON.parse(localStorage.getItem('cart')).length > 0) {
                          let _cart = JSON.parse(localStorage.getItem('cart'))
                          let _course_ids = []
                          _cart.forEach(_item => {
                              _course_ids.push(_item.id)
                          })
                          this.addToCart({ course_id: _course_ids }).then(() => {
                              localStorage.removeItem('cart')
                              setTimeout(() => {
                                this.dialog = false
                                this.msg = {
                                  show: true,
                                  text: "Login Successful!",
                                  type: "success"
                                }
                              }, 2000)
                          })
                      } else {
                          this.dialog = false
                          localStorage.removeItem('cart')
                          this.msg = {
                            show: true,
                            text: "Login Successful!",
                            type: "success"
                          }
                      }
                  } else {
                    this.dialog = false
                    this.msg = {
                      show: true,
                      text: "Login Successful!",
                      type: "success"
                    }
                  }
              }
            } else {
              this.msg = {
                show: true,
                text: "Your organization has disabled this account. Please contact your administrator.",
                type: "error"
              }
            }
            this.logging = false
          }).catch(e => {
            this.dialog = false
            this.errs = e
            this.logging = false
          })
        // }, 3000)
      }
    }
  },

  computed: {
    ...mapState({
      customization: (state) => state.customization,
      errors: (state) => state.errors,
    }),

    errorMessage(){
      if(this.errs.email) {
        if(this.errs.email[0] === 'Invalid email or password. Please check your credentials and try again.') {
          return 'No such email or password'
        } else return this.errs.email[0]
      } else if (this.errs.password) {
        if(this.errs.password[0] === 'Invalid email or password. Please check your credentials and try again.') {
          return 'No such email or password'
        } else return this.errs.password[0]
      } else if (this.errs.status) {
        return this.errs.status[0]
      } else return 'Something went wrong. Please try again later.'
    }
  }
  // beforeRouteEnter(to, from, next) {
  //   let obj = JSON.parse(localStorage.getItem('access_payload'))
  //   if (obj && obj.token) {
  //     if(role() === 'user') {
  //       router.push('/dashboard')
  //     } else if (role() === 'admin') {
  //       router.push('/admin')
  //     } else if (role() === 'instructor') {
  //       router.push('/instructor')
  //     }
  //   }
  //   next()
  // }
}
</script>