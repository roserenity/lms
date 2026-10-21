<template>
  <section>
      <v-sheet min-height="85vh" max-height="85vh" class="fade overflow-y-auto scroller d-flex align-stretch flex-column">
        <div class="ma-5">
          <h3 class="poppins">Change Password</h3>
          <b><label class="caption error--text">You will have to re-login again after successfully changing your password.</label></b>
        </div>
        
        <v-form ref="form">
          <div class="ma-5">
            <label class="caption">CURRENT PASSWORD</label>
            <v-text-field 
              outlined 
              dense 
              class="roboto f14 fw500 general-custom-field"
              :type="!show?'password':'text'"
              :append-icon="!show?'mdi-eye-outline':'mdi-eye-off-outline'"
              @click:append="show=!show"
              v-model="form.current_password"
              autocomplete="off"
              :error-messages="errors && errors.current_password"
              :rules="[() => !!form.current_password || 'This field is required']"
              required
              hide-details="auto"
            />
            <label class="caption">NEW PASSWORD</label>
            <v-text-field 
              autocomplete="new-password"
              outlined 
              dense 
              class="roboto f14 fw500 general-custom-field"
              :type="!show1?'password':'text'"
              :append-icon="!show1?'mdi-eye-outline':'mdi-eye-off-outline'"
              @click:append="show1=!show1"
              v-model="form.password"
              :error-messages="errors && errors.password"
              :rules="[() => !!form.password || 'This field is required']"
              required
              hide-details="auto"
            />

            <PasswordRules :password="form.password" class="mb-3"/>

            <label class="caption">PASSWORD CONFIRMATION</label>
            <v-text-field 
              autocomplete="new-password"
              outlined 
              dense 
              class="roboto f14 fw500 general-custom-field"
              :type="!show2?'password':'text'"
              :append-icon="!show2?'mdi-eye-outline':'mdi-eye-off-outline'"
              @click:append="show2=!show2"
              :error-messages="errors && errors.password_confirmation"
              v-model="form.password_confirmation"
              :rules="[() => !!form.password_confirmation || 'This field is required']"
              required
              hide-details="auto"
            />
          </div>
          
          </v-form>
          <v-divider class="mt-auto"/>
          
          <div class="ma-5">
            <v-btn @click="submit" color="primary" class="poppins text-capitalize fw600" :loading="loading">Save changes</v-btn>
            <v-btn @click="clear()" color="secondary-4" text class="poppins text-capitalize fw600">Cancel</v-btn>
          </div>
      </v-sheet>
      <v-dialog v-model="dialog" width="auto">
        <v-card class="pa-5 d-flex align-center justify-center">
          <circular class="mr-2" />
          <h3 class="text-center poppins">Logging you out...</h3>
        </v-card>
      </v-dialog>
  </section>
</template>

<script>
import { mapActions, mapMutations, mapState } from 'vuex'
import PasswordRules from '../../components/reusable/forms/PasswordRules.vue'

export default {
  data: () => ({
    show: false,
    show1: false,
    show2: false,

    dialog: false,

    form: {
      current_password: '',
      password: '',
      password_confirmation: ''
    },

    loading: false
  }),
  components: {
    PasswordRules
  },
  methods: {
    ...mapActions(['changeUserPasswordAction']), 
    ...mapMutations(['alertMutation', 'signoutMutation']),

    submit() {
      if(this.$refs.form.validate()) { 
        this.loading = true
        this.changeUserPasswordAction({ form: this.form }).then(res => {
          this.alertMutation({
            show: true,
            text: 'Password Successfully Updated!',
            type: "success"
          })
          this.loading = false
          this.dialog = true
          setTimeout(() => {
            this.signoutMutation()
          }, 2000)
        }).catch(() => {
          this.loading = false
        })
      }
    },

    clear(){
      this.form ={
        current_password: '',
        password: '',
        password_confirmation: ''
      }
    },
  },
  computed: {
    ...mapState({
      errors: (state) => state.errors
    })
  }

}
</script>