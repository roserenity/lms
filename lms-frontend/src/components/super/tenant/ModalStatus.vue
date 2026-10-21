<template>
  <v-dialog v-model="dialog" persistent max-width="350">
    <v-card>
      <v-card-text>
        <v-btn icon absolute right class="mt-3" @click="$emit('close')">
          <v-icon>mdi-close</v-icon>
        </v-btn>
        <div class="text-center pt-8 pb-5">
          <v-icon size="90" color="danger-1">mdi-alert-circle</v-icon>
          <h3 class="poppins black--text mt-5">
            {{ `Change status to ${type} ?` }}
          </h3>
          <!-- <div class="secondary--text roboto f14 col-lg-11 mx-auto">
            {{ `You will not be able to undo this action.` }}
          </div> -->
        </div>
      </v-card-text>
      <v-divider />
      <v-card-actions class="d-flex align-center justify-center py-5">
        <v-btn text class="text-capitalize" width="100" @click="$emit('close')"
          >No</v-btn
        >
        <v-btn
          outlined
          color="primary"
          class="text-capitalize"
          width="100"
          @click="$emit('emitChange')"
          >Yes</v-btn
        >
      </v-card-actions>
    </v-card>
    
  </v-dialog>
</template>

<script>
export default {
  props: ['dialog', 'type']
}
</script>