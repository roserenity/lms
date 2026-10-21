<template>
    <section>
      <v-dialog persistent max-width="450" v-model="dialog">
        <v-img :src="require('@/assets/images/default_success_bg.png')" 
            class="d-flex align-center justify-center"
            >
            <h3 class="poppins text-center">
                You’ve successfully submitted
                your evaluation!
            </h3>
        </v-img>
      </v-dialog>
    </section>
  </template>
  
  <script>
  export default {
    props: ['dialog'],
    data: () => ({
      asset_path: `${process.env.VUE_APP_ASSET_PATH}`,
    })
  } 
  </script>