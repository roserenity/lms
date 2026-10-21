<template>
  <v-row justify="center" align="center" no-gutters>
    <v-col cols="12" lg="5">
      <v-img 
        contain
        max-width="78vw" 
        :src="require('../assets/default/_403.png')" class="mx-auto mb-3" />
      <h1 class="text-center secondary-1--text poppins">Forbidden</h1>
      <div class="text-center text-wrap poppins secondary-1--text">
        You don’t have an access to this resource.
      </div>
      <div class="text-center my-10">
        <v-btn dense outlined color="secondary-1" class="text-capitalize rounded-lg" width="200" @click="close">
          Close this page
        </v-btn>
      </div>
    </v-col>
  </v-row>
</template>

<script>
export default {
  methods: {
    close() {
      localStorage.removeItem('current')
      localStorage.removeItem('roles')
      localStorage.removeItem('access_payload')
      localStorage.removeItem('access_payload_register')
      window.open("https://google.com", "_self").close();
    }
  },
}
</script>

