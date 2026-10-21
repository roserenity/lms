<template>
  <v-app >
    <v-row no-gutters class="fill-height">
      <v-col cols="12" lg="8" class="d-flex align-stretch justify-center">
        <v-sheet height="" class="d-flex flex-column align-center justify-center">
          <v-container width="auto" class="my-auto">
            <v-btn text dense class="primary-font mb-5 px-1" color="primary" @click="$router.push({ name: 'Homepage'})">
              <v-icon left>mdi-chevron-left</v-icon>
              Return to login
            </v-btn>
            <div class="">
              <h2 class="primary-font primary--text">
                Forgot Password
              </h2>
              <FormLabel :label="'Enter your email address below and we’ll send you an instruction to reset your password.'"/>
            </div>
            <alert 
              :show = "msg.show"
              :text = "msg.text"
              :type = "msg.type"
            />
            <v-form ref="form" class="mt-5">
              <FormTextFieldRequired
                :label="'EMAIL ADDRESS *'"
                :value.sync="email"
                :error="errors.email"
              />
              <ButtonPrimary 
                  @click="submit"
                  :label="'Reset Password'"
                  :block="true"
                  :loading="loading"
                  class="mt-3"
              />
            </v-form>
          </v-container>  
          <AuthFooter/>
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
export default {
  components: {
    AuthFooter
  },
  data: () => ({
    email: '',
    loading: false,
    asset_path: `${process.env.VUE_APP_ASSET_PATH}`,
    msg: {
      show: false,
      text: '',
      type: ''
    }
  }),
  methods: {
    ...mapActions(['forgotPasswordAction']),

    submit() {
      if(this.$refs.form.validate()){
        this.loading = true
        this.msg = {
          show: false,
          text: '',
          type: ''
        }
        this.forgotPasswordAction({email: this.email, url: window.location.origin}).then(res => {
          this.msg = {
            show: true,
            text: res,
            type: "success"
          }
          this.loading = false
        }).catch(() => {
          this.loading = false
        })

      }
    }
  },

  computed: {
    ...mapState({
      customization: (state) => state.customization,
      errors: (state) => state.errors,
    }),
  }
}
</script>

