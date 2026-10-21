<template>
  <v-dialog persistent v-model="dialog" max-width="380">
    <v-card rounded="lg">
      <v-card-text>
        <v-btn icon absolute right class="mt-3" @click="$emit('cancel')">
          <v-icon>mdi-close</v-icon>
        </v-btn>
        <div class="text-center pt-10">
          <v-icon size="90" color="danger-1">mdi-alert-circle</v-icon>
          <h3 class="poppins black--text mt-5">
            {{ `Delete ${type} ?` }}
          </h3>
          <div class="secondary--text roboto f14 col-lg-11 mx-auto">
            {{ `You will not be able to undo this action.` }}
          </div>
        </div>
      </v-card-text>
      <v-divider />
      <v-card-actions class="d-flex align-center justify-center">
        <v-btn text class="text-capitalize" width="100" @click="$emit('cancel')"
          >Cancel</v-btn
        >
        <v-btn
          :loading="deleting"
          outlined
          color="danger-1"
          class="text-capitalize"
          width="100"
          @click="del"
          >Delete</v-btn
        >
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
export default {
  props: ["dialog", "type"],
  data: () => ({
    deleting: false
  }),
  methods: {
    del() {
      this.deleting = true
      this.$emit('delete')
    }
  },
  watch: {
    dialog(val) {
      if(!val) this.deleting=false
    }
  }
};
</script>

