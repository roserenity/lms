<template>
    <v-row justify="center" align="center">
      <v-col cols="12" lg="5">
          <v-img 
            contain
            max-width="75vw" 
            :src="require('../assets/default/_403.png')" class="mx-auto mb-3" />
        <h1 class="text-center secondary-1--text poppins">
            Forbidden
        </h1>
        <div class="text-center poppins secondary-1--text">
            You don’t have an access to this resource.
        </div>
        <div class="text-center my-10">
          <v-btn dense outlined color="secondary-1" class="text-capitalize" width="200" @click="close">
            Back to Login
          </v-btn>
        </div>
      </v-col>
    </v-row>
  </template>

  <script>
  import { mapMutations } from 'vuex';

  export default {
    mounted(){
      this.removeUserMutation()
      localStorage.removeItem('current')
      localStorage.removeItem('roles')
      localStorage.removeItem('access_payload')
      localStorage.removeItem('access_payload_register')
      localStorage.removeItem('cart')
    },

  methods: {
    ...mapMutations(['removeUserMutation']),

    close() {
      this.removeUserMutation()
      this.$router.push({ name: 'Homepage'})
    }
  },
  
  }
  </script>