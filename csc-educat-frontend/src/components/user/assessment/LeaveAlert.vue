<template>
  <v-dialog persistent v-model="dialog.status" max-width="350">
    <v-card v-if="dialog.action == 'reload'">
      <v-card-title class="d-flex align-center justify-center">
        <h4 class="primary--text poppins fw600">Reload Site ?</h4>
        <!-- <v-btn icon @click="$emit('cancel')" small>
          <v-icon size="18">mdi-close</v-icon>
        </v-btn> -->
      </v-card-title>
      <v-card-text class="text-center mt-1">
        <div class="poppins f14 fw500">
          Changes you made may not be saved.
        </div>
      </v-card-text>
      <v-card-actions class="d-flex align-center justify-center mt-0 pt-0">
        <v-btn text class="text-capitalize poppins ">Cancel</v-btn>
        <v-btn text class="text-capitalize poppins primary--text fw600">Reload</v-btn>
      </v-card-actions>
    </v-card>
    <v-card v-if="dialog.action == 'leave'">
      <v-card-text class="text-center pt-3">
        <div class="poppins f14 fw500">
          Changes you made may not be saved.
        </div>
      </v-card-text>
      <v-card-actions class="d-flex align-center justify-center mt-0 pt-0">
        <v-btn text class="text-capitalize poppins " @click="$emit('cancel')">Cancel</v-btn>
        <v-btn text class="text-capitalize poppins primary--text fw600" @click="$emit('submit')">Ok</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
export default {
  props: ['dialog']
}
</script>
