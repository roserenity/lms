<template>
    <v-dialog persistent v-model="dialog.status" max-width="350">
      <v-card v-if="dialog">
        <v-card-text class="text-center py-5">
          <div class="poppins f16 fw500">
            Changes you made may not be saved.
          </div>
        </v-card-text>
        <v-divider />
        <v-card-actions class="d-flex align-center justify-center mt-0  py-3">
          <ButtonPrimary
            :label="'Cancel'"
            @click="$emit('cancel')"
            :outlined="true"
          />

          <ButtonPrimary
            :label="'Ok'"
            @click="$emit('submit')"
          />
        </v-card-actions>
      </v-card>
    </v-dialog>
  </template>
  
  <script>
  export default {
    props: ['dialog']
  }
  </script>
  