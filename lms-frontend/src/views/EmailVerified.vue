<template>
  <v-app >
    <v-row no-gutters class="fill-height" >
      <v-col cols="12" lg="8" class="d-flex align-stretch justify-center">
        <v-sheet max-width="600" class="poppins d-flex flex-column align-center justify-center" v-if="loading">
          <v-progress-linear
            indeterminate
            primary
            class="mt-10"
          ></v-progress-linear>
        </v-sheet>

        <v-sheet max-width="600" class="poppins d-flex flex-column align-center justify-center" v-if="!loading">
          <v-container class="d-flex flex-column" v-if="success">
            <h2 class="poppins success--text my-3 mt-15">
              Email Verification Success!
            </h2>
            <span class="f14 roboto secondary-1--text my-3">
              You may now access the platform.
            </span>

            <v-progress-linear
              indeterminate
              primary
              class="mt-3"
            ></v-progress-linear>
            
            <span class="f12 roboto secondary-1--text my-3 text-center">
              Redirecting ...
            </span>
            
          </v-container>  
          <v-container class="d-flex flex-column" v-if="success === false">
            <h2 class="poppins error--text my-3 mt-15">
              Email Verification Failed!
            </h2>
            <span class="f14 roboto secondary-1--text my-3">
              Something went wrong. You may log in with your account and resend an email verification.
            </span>

            <v-progress-linear
              indeterminate
              primary
              class="mt-3"
            ></v-progress-linear>
            
            <span class="f12 roboto secondary-1--text my-3 text-center">
              Redirecting ...
            </span>
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
              <v-icon v-else size="160" color="#fff">mdi-email-check-outline</v-icon>
          </div>
        </v-sheet>
      </v-col>
    </v-row>
  </v-app>
</template>

<script>
import store from '@/store';
import { mapState, mapActions } from 'vuex'

export default {
  data: () => ({
    success: null,
    loading: false,
    asset_path: `${process.env.VUE_APP_ASSET_PATH}`,
  }),
  mounted(){
    let obj = JSON.parse(localStorage.getItem('access_payload'))

    this.loading = true

    if(obj && obj.token) {
      this.verifyEmailAction({ 
        hash: this.$route.query.hash, 
        token: obj.token, 
        baseURL: process.env.VUE_APP_TENANT_API
      }).then( ()=> {
        this.loading = false
        this.success = true
            
        setTimeout(() => {
          this.$router.push({ name: 'Homepage'})
        }, 3000);
        // setTimeout(() => {
        //   store.dispatch('authAction').then(()=>{
        //       if(store.getters.userGetters.role=='USER') {
        //         this.$router.push('/dashboard')
        //       } else if (store.getters.userGetters.role=='ADMIN') {
        //         this.$router.push('/admin')
        //       } else if (store.getters.userGetters.role=='INSTRUCTOR') {
        //         this.$router.push('/instructor')
        //       }
        //   })
        // }, 3000);
      }).catch(() => {
        this.loading = false
        this.success = false

        setTimeout(() => 
          this.$router.push({ name: 'Homepage'})
        , 9000);
      })
    } else if(localStorage.getItem('access_payload_register')){
      this.verifyEmailAction({ 
        hash: this.$route.query.hash, 
        token: localStorage.getItem('access_payload_register'), 
        baseURL: process.env.VUE_APP_TENANT_API
      }).then( ()=> {
        this.loading = false
        this.success = true
            
        setTimeout(() => {
          this.$router.push({ name: 'Homepage'})
        }, 3000);
      }).catch(() => {
        this.loading = false
        this.success = false

        setTimeout(() => 
          this.$router.push({ name: 'Homepage'})
        , 9000);
      })
    } else {
      this.verifyEmailAction({ 
        hash: this.$route.query.hash, 
        baseURL: process.env.VUE_APP_TENANT_API
      }).then( ()=> {
        this.loading = false
        this.success = true
            
        setTimeout(() => {
          this.$router.push({ name: 'Homepage'})
        }, 3000);
      }).catch(() => {
        this.loading = false
        this.success = false

        setTimeout(() => 
          this.$router.push({ name: 'Homepage'})
        , 9000);
      })
    }
  },

  methods: {
    ...mapActions(['verifyEmailAction'])
  },

  computed: {
    ...mapState({
      customization: (state) => state.customization
    }),
  }
};
</script>

<style scoped>
.centered-input >>> input {
  text-align: center;
}
</style>
