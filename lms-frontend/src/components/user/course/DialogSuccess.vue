<template>
    <section>
      <v-dialog persistent max-width="450" v-model="dialog">
        <v-img :src="require('@/assets/images/default_success_bg.png')" 
          class="d-flex align-center justify-center"
          >
            <h3 class="poppins text-center success--text">
              You’ve successfully submitted
              your {{ type }}!
            </h3>
        </v-img>
      </v-dialog>
    </section>
  </template>
  
  <script>
  export default {
    props: ['dialog', 'type'],
    data: () => ({
    })
  } 
  </script>