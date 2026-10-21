<template>
    <v-row justify="center" align="center">
      <v-col cols="12" lg="5">
        <div class="text-center poppins secondary-1--text">
          Your session has expired.
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
  export default {
    mounted(){
        localStorage.removeItem('current')
        localStorage.removeItem('roles')
        localStorage.removeItem('access_payload')
        localStorage.removeItem('access_payload_register')
        localStorage.removeItem('cart')
    },
  
    methods: {
      close() {
        this.$router.push({ name: 'Homepage'})
      }
    },
  }
  </script>