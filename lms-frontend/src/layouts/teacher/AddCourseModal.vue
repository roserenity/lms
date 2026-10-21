<template>
  <v-dialog persistent v-model="dialog" max-width="400">
    <v-card v-if="!success">
      <v-card-title class="d-flex align-center justify-space-between">
        <h4 class="primary--text poppins fw600">Create Course</h4>
        <v-btn icon @click="$emit('close')">
          <v-icon size="18">mdi-close</v-icon>
        </v-btn>
      </v-card-title>
      <v-card-text class="mt-2">
        <FormTextFieldRequired
            :label="'TITLE'"
            :error="errs.title"
            :value.sync="title"
          />
        <!-- <label class="poppins fw500 f12">TITLE</label>
        <v-text-field
          outlined
          dense
          autofocus
          :hide-details="!errs.title"
          class="general-custom-field roboto f14 secondary-1--text fw500 mb-3"
          v-model="title"
          :error-messages="errs.title"
        /> -->
      </v-card-text>
      <v-divider />
      <v-card-actions class="">
        <v-spacer />
        <v-btn text class="poppins fw600 text-capitalize" @click="$emit('close')"
          >Cancel</v-btn
        >
        <v-btn
          v-if="!saving"
          class="poppins fw500 text-capitalize"
          color="primary"
          @click="save"
        >
          Module Editor
          <v-icon right>mdi-arrow-right</v-icon>
        </v-btn>
        <v-btn v-else class="poppins fw500 text-capitalize" color="primary">
          Saving...
        </v-btn>
      </v-card-actions>
    </v-card>
    <v-card v-else class="text-center pa-5">
      <h2 class="primary--text fw600 poppins">Course created!</h2>
      <div class="secondary--text f14 fw600 poppins">
        Directing you to Module editor...
      </div>
    </v-card>
  </v-dialog>
</template>

<script>
import { mapActions, mapState } from "vuex";
export default {
  props: ["dialog"],
  computed: {
    ...mapState({
      user: (state) => state.user,
      errors: (state) => state.errors,
    }),
    ...mapState("instructor", {
      course: (state) => state.course,
    }),
  },
  data: () => ({
    success: false,
    title: "",
    saving: false,
    errs: [],
  }),
  watch: {
    dialog(val) {
      this.title = ''
      this.errs = {}
    }
  },
  methods: {
    ...mapActions("instructor", ["addCourseAction"]),
    save() {
      this.errs = {}
      this.saving = true;
      this.addCourseAction({
        title: this.title,
        status: 'DRAFT',
        uploader_id: this.user.id,
        training_hours: 1,
        credit_training_hours: 1,
        training_credits: 1
      }).then(() => {
        //if (this.$errors()) return (this.success = false), (this.errs = this.errors), (this.saving = false);
        this.saving = false;
        this.success = true;
        setTimeout(() => {
          this.$router.push({
            name: "Instructor Manage Course",
            params: { id: this.course.id, status: 'unpublish'},
          });
          this.success = false
          this.$emit("close");
        }, 2000);
      }).catch(e => {
        this.errs = e
        this.saving = false
        this.success = false
      })
    },
  },
};
</script>
