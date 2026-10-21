<template>
    <v-dialog persistent v-model="dialog" max-width="530">
      <v-card rounded="lg">
        <v-card-text>
          <v-btn icon absolute right class="mt-3" @click="$emit('close')">
            <v-icon>mdi-close</v-icon>
          </v-btn>
          <div class="text-center py-5">
            <v-icon size="50" color="success">mdi-check-circle</v-icon>
            <h4 class="poppins black--text mt-2">
                {{ msg }}
            </h4>         
          </div>
        </v-card-text>
      </v-card>
    </v-dialog>
  </template>
  
  <script>
  export default {
    props: {
      dialog: Boolean,
      msg: String
    },
  };
  </script>
  
  <style scoped>
  .centered-input >>> input {
    text-align: center;
  }
  </style>
  