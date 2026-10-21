<template>
  <v-dialog persistent v-model="dialog" max-width="530">
    <v-card rounded="lg">
      <v-card-text>
        <v-btn icon absolute right class="mt-3" @click="close">
          <v-icon>mdi-close</v-icon>
        </v-btn>
        <div class="text-center py-5">
          <v-icon size="90" color="danger-1">mdi-alert-circle</v-icon>
          <h3 class="poppins black--text mt-5">
            {{ `Are you sure you want to delete this ${type} ?` }}
          </h3>
          <div class="secondary--text roboto f14 col-lg-9 mx-auto">
            {{
              `This action cannot be undone. All of the data under this ${type} will be permanently deleted.`
            }}
          </div>
          <div class="roboto f4 fw600 mb-5">Type ‘delete’ to confirm</div>
          <v-form @submit.prevent="detach">
            <v-text-field
              v-model="text"
              class="col-lg-9 mx-auto general-custom-field roboto f14 secondary-1--text fw500 centered-input"
              outlined
              dense
              autofocus
              v-on:input="checkInput($event)"
            />
            <v-btn
              v-if="!deleting"
              type="submit"
              :color="remove ? 'danger-1' : '#FFA0A0'"
              class="text-capitalize poppins f12 white--text"
              elevation="0"
            >
              Delete {{ type }}
            </v-btn>
            <v-btn
              v-else
              color="danger-1"
              dark
              class="text-capitalize poppins f12"
              elevation="0"
            >
              Deleting...
            </v-btn>
          </v-form>
        </div>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>

<script>
import { mapActions, mapMutations } from "vuex";
export default {
  props: ["dialog", "item", "type", "course_id"],
  data: () => ({
    remove: false,
    text: "",
    deleting: false,
  }),
  methods: {
    ...mapMutations(["alertMutation"]),

    ...mapActions("instructor", [
      "deleteCourseAction",
      "deleteModuleAction",
      "deleteTopicAction",
      "deleteSubtopicAction",
      "getCourseModulesAction"
    ]),

    checkInput(e) {
      this.remove = false;
      if (e === "delete") this.remove = true;
    },

    detach() {
      if (this.text === "delete") {
        this.deleting = true;
        const payload = { id: this.item.id, course_id: this.course_id };

        if (this.type === "Course") {
          this.deleteCourseAction(payload).then(() => {
            this.close();
            this.$emit('fetchCourses')
          }).catch(() => {
            this.onCloseError()
          });
        }
        if (this.type === "Module") {
          this.deleteModuleAction(payload).then(() => {
            this.getCourseModulesAction(this.course_id)
            this.close();
          }).catch(() => {
            this.onCloseError()
          });
        }
        if (this.type === "Topic") {
          this.deleteTopicAction(payload).then(() => {
            this.close();
          });
        }
        if (this.type === "Sub-topic") {
          this.deleteSubtopicAction(payload).then(() => {
            this.close();
          });
        }
      }
    },

    close() {
      this.text = "";
      this.$emit("close");
      if (this.deleting) {
        this.alertMutation({
          show: true,
          text: `${this.type} Successfully Deleted`,
          type: "success"
        });
      }
      this.deleting = false;
    },

    onCloseError() {
      this.text = "";
      this.$emit("close");
      this.alertMutation({
        show: false,
        text: `Something went wrong in deleting`,
        type: "error"
      });
      this.deleting = false;
    },
  },
};
</script>

<style scoped>
.centered-input >>> input {
  text-align: center;
}
</style>
