<template>
    <v-dialog persistent v-model="dialog" max-width="550">
      <v-card>
        <v-card-title class="d-flex align-center justify-space-between ma-0 pa-0">
          <div class="poppins primary--text px-5 py-3 fw600">
            {{ action === 'mine' ? 'Move' : 'Duplicate and Move' }} Rubric
          </div>
          <v-btn icon @click="$emit('close')" class="mr-2">
            <v-icon size="20">mdi-close</v-icon>
          </v-btn>
        </v-card-title>
        <v-card-text class="py-4">
          <FormLabel
            :label="'RUBRIC TITLE'"
          />
          <v-text-field
            dense
            outlined
            class="general-custom-field secondary-1--text f14 fw600 roboto mb-2"
            hide-details
            v-model="data.title"
            v-on:input="updateAssessment(data)"
          />
          <div class="my-3 poppins fw500">COURSES</div>
          <v-divider />
          <v-list dense v-if="courses.length > 0">
            <v-list-item-group v-model="selected" active-class="active border">
              <v-list-item
                v-for="(course, i) in courses"
                :key="i"
                :value="course.id"
                :disabled="data.course_id == course.id"
              >
                <template v-slot:default="{ active }">
                  <v-list-item-content>
                    <v-list-item-title
                      class="roboto f14"
                      :class="active ? 'primary--text' : ''"
                      v-text="course.title"
                    />
                  </v-list-item-content>
                  <v-list-item-action>
                    <v-icon size="20" color="primary">{{
                      active ? "mdi-check" : ""
                    }}</v-icon>
                  </v-list-item-action>
                </template>
              </v-list-item>
            </v-list-item-group>
          </v-list>
          <div class="text-center poppins f14 primary--text fw500 my-5" v-else>
            No course found
          </div>
        </v-card-text>
        <v-card-actions class="d-flex align-center justify-end mx-2">
          <div class="d-flex align-center">
            <v-btn @click="$emit('close')" text class="poppins text-capitalize f13"
              >Cancel</v-btn
            >
            <v-btn
              color="primary"
              class="poppins text-capitalize f13"
              @click="move"
              :loading="loading"
              >Move</v-btn
            >
          </div>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </template>
  
  <script>
  import { mapActions, mapMutations, mapState } from "vuex";
  export default {
    props: ["dialog", "data", "courses", "action"],
    data: () => ({
      selected: [],
      loading: false,
    }),
  
    methods: {
      ...mapActions("instructor", [
        "updateAssessmentAction",
        "moveAssessmentAction",
        "moveAssessmentSharedWithMeAction",
        "showSharedWithMeAction",
      ]),
      ...mapMutations(["alertMutation"]),
  
      updateAssessment(data) {
        this.action == "mine" ? this.updateAssessmentAction(data) : "";
      },
  
      move() {
        this.loading = true;
        if (this.action == "mine") {
          this.moveAssessmentAction({
            assessment_id: this.data.id,
            course_id: this.selected,
          }).then(() => {
            this.loading = false;
            this.selected = [];
            this.alertMutation({ 
              show: true, 
              text: "Assessment Successfully Moved!",
              type: "success" });
            this.$emit("close");
          }).catch(() => {
            this.loading = false;
            this.alertMutation({ show: true, text: "Something went wrong!", type: "error" });
          })
        } else {
          this.moveAssessmentSharedWithMeAction({
            assessment_id: this.data.id,
            course_id: this.selected,
            title: this.data.title,
          }).then(() => {
            this.loading = false;
            this.selected = [];
            this.$emit("close");
            this.alertMutation({ 
              show: true, 
              text: "Assessment has been Moved!",
              type: "success" });
            this.showSharedWithMeAction();
          }).catch(() => {
            this.loading = false;
            this.alertMutation({ show: true, text: "Something went wrong!", type: "error" });
          })
        }
      },
    },
  };
  </script>
  
  <style>
  .active {
    background-color: transparent !important;
    border-radius: 5px !important;
  }
  </style>
  