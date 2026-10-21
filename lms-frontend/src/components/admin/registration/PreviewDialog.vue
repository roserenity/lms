<template>
    <v-dialog v-model="modal" persistent max-width="550">
      <v-card >
        <v-card-title class="d-flex flex-row align-center justify-center mb-2">
          <v-spacer/>
            <h5 v-if="item.custom_registration_fields.length != 0" class="primary--text poppins fw600 text-center">Preview : <span class="text-capitalize secondary-2--text">{{ item.title }}</span></h5>
          <v-spacer/>
          <v-btn icon :class="item.custom_registration_fields.length == 0 && 'ml-auto'" icon small @click="onEdit = false, $emit('close')" :disabled="loading">
          <v-icon size="22">mdi-close</v-icon>
          </v-btn>
        </v-card-title>
        <v-card-text>
            <v-container v-if="item.custom_registration_fields.length === 0" class="text-center">
                <v-icon size="50" color="danger-1">mdi-alert-circle</v-icon>
                <h3 class="poppins black--text mt-5">
                    No available preview
                </h3>
                <div class="secondary-1--text roboto f14 col-lg-9 mx-auto">
                    Add at least one(1) field on this page
                </div>
            </v-container>
            <section v-else>
                <div class="poppins f18 fw600 secondary-1--text">
                    {{ item.table_name }}
                </div>
                <div v-for="(column, i) in item.custom_registration_fields" :key="i" class="my-3 d-flex flex-column">
                  <label class="caption text-uppercase">{{column.input_field_label}} <span v-if="column.is_required">*</span></label>
                  <input
                      v-if="column.input_field_type === 'file'"
                      :type="column.input_field_type"
                      dense
                      disabled
                      class="roboto f14 fw500 secondary-2--text"
                      hide-details
                  />
                  <FormSelectArray 
                    v-else-if="column.input_field_type === 'dropdown'"
                    :items="column.data"
                  />
                  <FormTextFieldOptional
                    v-else
                    :type="column.input_field_type"
                    :readonly="column.input_field_type !== 'date'"
                  />
                </div>
            </section>
        </v-card-text>
      </v-card>
    </v-dialog>
  </template>

<script>
import { mapActions, mapState, mapMutations } from 'vuex'

export default {
  props: ['modal', 'item'],
  computed: {
    ...mapState({
      errors: (state) => state.errors,
    })
  },
  data: () => ({
    onEdit: false,
    className: null
  }),
  methods: {
    ...mapActions('admin', [
      'updateClassesAction',
    ]),

    ...mapMutations(['alertMutation']),

    cancel() {
      this.$emit('close')
    },
  }
}
</script>