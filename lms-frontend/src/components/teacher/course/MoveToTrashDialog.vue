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
            {{ `Are you sure you want to move this ${type.toLowerCase()} to trash?` }}
          </h3>
          <div class="secondary--text roboto f14 col-lg-9 mx-auto">
            {{
              `All of the data under this ${type.toLowerCase()} will be permanently deleted after 30 days. You may opt to restore this in the Trash Page.`
            }}
          </div>
          <div class="roboto f4 fw600 mb-5">Type 'move to trash' to confirm</div>
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
              Move to trash
            </v-btn>
            <v-btn
              v-else
              color="danger-1"
              dark
              class="text-capitalize poppins f12"
              elevation="0"
            >
              Moving to trash...
            </v-btn>
          </v-form>
        </div>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>

<script>
import { mapMutations, mapActions } from "vuex";
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
      "moveToTrashAction",
      "deleteModuleAction",
      "deleteTopicAction",
      "deleteSubtopicAction",
      "getCourseModulesAction"
    ]),

    checkInput(e) {
      this.remove = false;
      if (e === "move to trash") this.remove = true;
    },

    detach() {
      if (this.text === "move to trash") {
        this.deleting = true;
        const payload = { id: this.item.id, course_id: this.course_id };

        if (this.type === "Course") {
          this.moveToTrashAction(payload).then(() => {
            this.successClose();
          }).catch(() => {
            this.errorClose();
          });
        }
        if (this.type === "Module") {
          this.deleteModuleAction(payload).then(() => {
            this.getCourseModulesAction(this.course_id)
            this.successClose();
          }).catch(() => {
            this.errorClose();
          })
        }
        if (this.type === "Topic") {
          this.deleteTopicAction(payload).then(() => {
            this.successClose();
          }).catch(() => {
            this.errorClose();
          });
        }
        if (this.type === "Sub-topic") {
          this.deleteSubtopicAction(payload).then(() => {
            this.successClose();
          }).catch(() => {
            this.errorClose();
          });
        }
      }
    },

    close() {
      this.text = "";
      this.$emit("close");
      this.deleting = false;
    },

    successClose(){
      this.text = "";
      this.$emit("close");
      this.$emit("fetchCourses");
      this.alertMutation({
        show: true,
        text: `${this.type} Successfully Moved to Trash`,
        type: "success"
      });
      this.deleting = false;
    },

    errorClose(){
      this.text = "";
      this.$emit("close");
      this.alertMutation({
        show: true,
        text: `Something went wrong`,
        type: "error"
      });
      this.deleting = false;
    }
  },
};
</script>

<style scoped>
.centered-input >>> input {
  text-align: center;
}
</style>
