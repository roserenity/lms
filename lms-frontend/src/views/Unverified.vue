<template>
  <v-app >
    <v-row no-gutters class="fill-height">
      <v-col cols="12" lg="8" class="d-flex align-stretch justify-center">
        <v-sheet max-width="600" class="poppins d-flex flex-column align-left">
          <v-container class="mt-15" >
            <h2 class="poppins primary--text my-3 mt-15">
              Verification
            </h2>
            <div class="f14 roboto secondary-1--text my-5">
              To continue using the platform, please verify your email address.
            </div>
            <alert 
              v-if="msg.show"
              :show = "msg.show"
              :text = "msg.text"
              :type = "msg.type"
            />
            
            <v-btn color="primary" class="f12 my-5" style="border-radius: 10px" @click="sendVerification" :loading="loading"> SEND  VERIFICATION EMAIL</v-btn>
            <!-- <v-alert
              outlined
              type="success"
              text
              v-show="success"
              class="mt-5"
              dense
              prominent
              icon="mdi-send-circle-outline"
              transition="scroll-y-transition"
              fab
            >
              We've sent you a link to confirm your email address, please check your inbox.
            </v-alert>
            <v-alert type="error" text v-if="errors" class="mt-5" 
            :class="$vuetify.breakpoint.mobile? 'mb-n7':'mb-5'" dense transition="scroll-y-transition">
                We encountered an error upon sending an email. Try again.
            </v-alert> -->
          </v-container> 

          <v-btn 
            text
            color="primary" 
            class="f12 my-5" 
            style="border-radius: 10px" 
            @click="returnToLogin" 
            :loading="returnToLoginLoading"> 
            Return to Login
          </v-btn> 
        </v-sheet>
             
      </v-col>
      <v-col cols="12" lg="4" class="hidden-md-and-down">
        <v-img 
          v-if="customization.hasOwnProperty('sidebar_photo')"
          height="100vh"
          :src="customization.hasOwnProperty('sidebar_photo') && customization.sidebar_photo"
          transition="scale-transition"
          id="sidebar_photo"
          position="left"
        />
        <v-sheet v-else :color="customization.hasOwnProperty('sidebar_color') ? (customization.sidebar_color ? customization.sidebar_color : 'primary') : 'primary'" height="100vh" class="d-flex justify-center align-center">
          <!-- <v-icon size="160" color="#fff">mdi-image-outline</v-icon> -->
          <div class="">
            <v-icon size="160" color="#fff">mdi-shield-key-outline</v-icon>
          </div>
        </v-sheet>
      </v-col>
    </v-row>
  </v-app>
</template>

<script>
import { mapActions, mapState } from 'vuex';


export default {
  data: () => ({
    loading: false,
    success: false,
    errors: null,
    returnToLoginLoading: false,
    msg: {
      show: false,
      text: '',
      type: ''
    }
  }),
  mounted() {
  },
  methods: {
    ...mapActions(['logoutAction', 'sendVerificationMailAction']),
    returnToLogin() {
      this.returnToLoginLoading = true

      localStorage.removeItem('current')
      localStorage.removeItem('roles')
      localStorage.removeItem('access_payload')
      localStorage.removeItem('roles')
      localStorage.removeItem('current')
      localStorage.removeItem('access_payload_register')
      this.$router.push('/')
      // if(localStorage.getItem('access_payload')){
      //   this.logoutAction().then(() => {
      //     this.returnToLoginLoading = false
      //   })
      // } else {
      //   this.$router.push('/')
      // }
    },

    sendVerification(){
      this.loading = true
      this.msg = {
        show: false,
        text: '',
        type: ''
      }
      if(localStorage.getItem('access_payload')){
        this.sendVerificationMailAction().then(res => {
          this.msg = {
            show: true,
            text: res,
            type: "success"
          }
          this.loading = false
        }).catch(e=> {
          this.msg = {
            show: true,
            text: "Invalid token. Please try logging in again",
            type: "error"
          }
          this.loading = false
        })
      } else {
        this.msg = {
          show: true,
          text: "Invalid token. Please try logging in again",
          type: "error"
        }
        this.loading = false
      }
    },
  },
  computed: {
    ...mapState({
      customization: (state) => state.customization
    }),
  }
}
</script>

<style>
</style>