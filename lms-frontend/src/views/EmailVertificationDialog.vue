<template>
    <v-dialog persistent v-model="dialog" max-width="530">
      <v-card rounded="lg">
        <v-card-text>
          <v-btn icon absolute right class="mt-3" @click="$emit('close')">
            <v-icon>mdi-close</v-icon>
          </v-btn>
          <div class="text-center py-5">
            <v-icon size="50" color="success">mdi-check-circle</v-icon>
            <h4 class="poppins black--text my-5">
                Thank you for signing up. We've sent you a link to confirm your email address, please check your inbox.
            </h4>            
            <alert 
              v-if="msg.show"
              :show = "msg.show"
              :text = "msg.text"
              :type = "msg.type"
            />
            <span>Didn't received an email? </span>
            <v-btn text class="text-capitalized f12 primary--text" :loading="loading" @click="$emit('resend')">
              RESEND VERIFICATION LINK
            </v-btn>
          </div>
        </v-card-text>
      </v-card>
    </v-dialog>
  </template>
  
  <script>
  export default {
    props: {
      dialog: false,
      loading: false,
      msg: Object
    },
  };
  </script>
  
  <style scoped>
  .centered-input >>> input {
    text-align: center;
  }
  </style>
  