<template>
  <v-app >
    <v-row no-gutters class="fill-height">
      <v-col cols="12" lg="8" class="d-flex flex-column align-center justify-center">
        <v-sheet width="380" class="primary-font">
          <v-container class="my-auto">
            <section class="d-flex flex-row">
              <v-spacer />
              <v-btn text dense class="primary-font mb-5 px-1 ml-auto" color="primary" @click="$router.push({ name: 'Login'})">
                Go to login
                <v-icon>mdi-chevron-right</v-icon>
              </v-btn>
            </section>
            <h2 class="primary-font primary--text mb-3">
              Reset Password
            </h2>
            <alert 
              v-if="msg.show"
              :show = "msg.show"
              :text = "msg.text"
              :type = "msg.type"
            />
            <FormLabel :label="'Email Address'" />
            <v-text-field 
              hide-details="auto" outlined dense 
              class="secondary-font f14"
              v-model="$route.query.email"
              :readonly="true"
              :error-messages="errors && errors.email"
            />
            <v-form class="" ref="form">
              <FormLabel :label="'Password *'" />

              <v-text-field outlined dense 
                class="secondary-font f14"
                :error-messages="errors && errors.password"
                v-model="password"
                :append-icon="!show?'mdi-eye-outline':'mdi-eye-off-outline'"
                @click:append="show=!show"
                :type="!show?'password':'text'"
                autocomplete="off"
                hide-details="auto"
                :rules="[() => 
                  !!password || 'The password field is required.',
                  /^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*_-]).{8,}$/.test(password) || 'Meet the conditions below:',]"
              />

              <section dense class="pt-2 f12 secondary-2--text text-left mb-2 fw500">
                  <PasswordRules :password="password"/>
              </section>
              <FormLabel :label="'Retype Password *'" />
              <v-text-field outlined dense 
                class="secondary-font f14 mb-3" 
                :error-messages="errors && errors.password_confirmation"
                v-model="password_confirmation"
                :append-icon="!show1?'mdi-eye-outline':'mdi-eye-off-outline'"
                @click:append="show1=!show1"
                autocomplete="off"
                hide-details="auto"
                :type="!show1?'password':'text'"
                :rules="[() => password === password_confirmation || 'The password and re-typed password does not match']"
              />
              <v-btn @click="resetPass" large block color="primary" class="border" :loading="loading1">Reset Password</v-btn>
              <!-- <v-btn @click="resendResetPass" large text block color="primary" class="border mt-1" :loading="loading2">Resend Password Reset link</v-btn> -->

            </v-form>
          </v-container>  
        </v-sheet>
      </v-col>
      <v-col cols="12" lg="4" class="hidden-md-and-down">
        <v-img 
          v-if="customization.hasOwnProperty('sidebar_photo')"
          height="100vh"
          :src="customization.hasOwnProperty('sidebar_photo') && customization.sidebar_photo"
          v-on:error="()=>{
            delete customization.sidebar_photo
          }"
          transition="scale-transition"
          id="sidebar_photo"
          position="left"
        />
        <v-sheet v-else :color="customization.hasOwnProperty('sidebar_color') ? (customization.sidebar_color ? customization.sidebar_color : 'primary') : 'primary'" height="100vh" class="d-flex justify-center align-center">
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
            <v-icon v-else size="160" color="#fff">mdi-image-outline</v-icon>
          </div>
        </v-sheet>
      </v-col>
    </v-row>
  </v-app>
</template>

<script>
import { mapState, mapActions } from 'vuex'
import AuthFooter from '../components/AuthFooter.vue'
import PasswordRules from '@/components/reusable/forms/PasswordRules.vue'
import FormLabel from '@/components/reusable/forms/FormLabel.vue'

export default {
  components: {
    AuthFooter,
    PasswordRules,
    FormLabel
  },
  data: () => ({
    password: '',
    password_confirmation: '',
    show: false,
    show1: false,
    loading1: false,
    loading2: false,
    msg: {
      show: false,
      text: '',
      type: ''
    },
    errors: {}
  }),
  methods: {
    ...mapActions(['resetPasswordAction', 'forgotPasswordAction']),

    resetPass() {
      if(this.$refs.form.validate()) {
        this.loading1 = true
        this.msg = {
          show: false,
          text: '',
          type: ''
        }

        this.errors = {}
        this.resetPasswordAction({
          token: this.$route.params.token,
          email: this.$route.query.email,
          password: this.password,
          password_confirmation: this.password_confirmation
        }).then(res => {
          this.msg = {
            show: true,
            text: res,
            type: "success"
          }
          this.loading1 = false
          this.$refs.form.reset()
        }).catch((e) => {
          if(e === 400) {
            this.msg = {
              show: true,
              text: "Invalid token. Please request another Password Reset link",
              type: "error"
            }
          } else {
            this.errors = e
          }
          this.loading1 = false
        })
      }
    },

    resendResetPass() {
      this.loading2 = true
      this.forgotPasswordAction({email: this.$route.query.email, url: window.location.origin}).then(res => {
        this.msg = {
          show: true,
          text: res,
          type: "success"
        }
        this.loading2 = false
      }).catch(() => {
        this.loading2 = false
      })
    }
  },
  
  computed: {
    ...mapState({
      customization: (state) => state.customization
    }),
  }
}
</script>

