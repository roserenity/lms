<template>
   <v-dialog v-model="dialog" max-width="420" persistent>
    <v-card>
      <v-card-title class="d-flex align-center justify-space-between">
        <h4>Lesson</h4>
        <div class="">
          <v-btn icon @click="$emit('close')">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </div>
      </v-card-title>
      <v-divider></v-divider>
      <v-card-text>
        <v-form class="mt-5">
          <label class="caption nunito">TITLE *</label>
          <v-text-field outlined dense hide-details class="mb-2"/>
          <label class="caption nunito">DESCRIPTION</label>
          <v-textarea rows="2" outlined dense hide-details class="mb-2"/>
          <label class="caption nunito">TYPE</label>
          <v-select outlined dense hide-details class="mb-2"/>
          <!-- <label class="caption nunito">MATERIAL</label>
          <v-file-input rows="2" outlined dense hide-details prepend-icon="" append-icon="mdi-paperclip"/> -->
        </v-form>
      </v-card-text>
      <v-divider/>
      <v-card-actions class="d-flex justify-end">
        <div class="">
          <v-btn color="success">Save</v-btn>
        </div>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
export default {
  props: ['dialog']
}
</script>
