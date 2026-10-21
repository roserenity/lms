<template>
  <v-dialog persistent v-model="dialog" max-width="550">
    <v-card class="rounded-lg">
      <v-card-title class="d-flex align-center justify-space-between mb-2">
        <h5 class="primary--text poppins fw600">
          Add pre-requisite to: <span class="secondary--text">{{ module.title }}</span>
        </h5>
        <v-btn icon @click="close">
          <v-icon size="22">mdi-close</v-icon>
        </v-btn>
      </v-card-title>
      <v-form @submit.prevent="add">
        <v-card-text>
          <label class="poppins fw500 f12 secondary--text mb-2">PRE-REQUISITE *</label>
          <v-combobox
            :items="modules"
            item-text="title"
            item-value="id"
            multiple
            item-color="transparent"
            dense
            outlined
            v-model="selected"
            class="general-custom-field roboto f14 fw500 secondary--text"
            hide-details
            v-on:input="prerequisite_module_id = []"
          >
            <template v-slot:item="{ active, item, attrs, on }">
              <v-list-item v-on="on" v-bind="attrs" #default="{ active }">
                <v-list-item-title class="black--text roboto f14 fw500">
                  <v-row no-gutters align="center" style="margin: 0px; padding: 0px">
                    <v-checkbox :input-value="active" dense hide-details class="pb-2" />
                    <span>{{ item.title }}</span>
                    <v-spacer />
                    <div>{{ item.topics.length }}</div>
                  </v-row>
                </v-list-item-title>
              </v-list-item>
            </template>
            <template v-slot:prepend-item>
              <v-text-field
                dense
                outlined
                class="mx-5 general-custom-field roboto f14 fw500 secondary--text"
                hide-details
                prepend-inner-icon="mdi-magnify"
                placeholder="Search"
              />
              <v-row no-gutters align="center" class="mx-5 mb-1 mt-3">
                <div class="f10 secondary--text poppins fw500">MODULE</div>
                <v-spacer />
                <div class="f10 secondary--text poppins fw500">NO OF TOPICS</div>
              </v-row>
            </template>
          </v-combobox>
        </v-card-text>
        <v-divider />
        <v-card-actions class="d-flex align-center justify-end mx-2">
          <v-btn class="text-capitalize poppins" text @click="close">Cancel</v-btn>
          <v-btn
            class="text-capitalize poppins"
            width="140"
            color="primary"
            type="submit"
            v-if="!submitting"
            >Add</v-btn
          >
          <v-btn class="text-capitalize poppins" width="140" color="primary" v-else
            >Adding...</v-btn
          >
        </v-card-actions>
      </v-form>
    </v-card>
  </v-dialog>
</template>

<script>
import { mapActions, mapMutations } from "vuex";
export default {
  props: ["module", "modules", "dialog"],
  data: () => ({
    selected: [],
    prerequisite_module_id: [],
    submitting: false,
  }),

  methods: {
    ...mapActions("instructor", ["addModulePrerequisiteAction"]),
    ...mapMutations(["alertMutation"]),

    close() {
      this.selected = [];
      this.submitting = false;
      this.$emit("close");
    },

    add() {
      if (!this.selected.length > 0) return;
      this.submitting = true;
      this.selected.forEach((el) => {
        this.prerequisite_module_id.push(el.id);
      });

      this.addModulePrerequisiteAction({
        course_id: this.module.course_id,
        module_id: this.module.id,
        uploader_id: this.module.uploader_id,
        prerequisite_module_id: this.prerequisite_module_id,
      }).then(() => {
        this.close();
        this.alertMutation({
          show: true,
          text: "Pre-requisite Successfully Added",
          type: "success"
        });
      });
    },
  },
};
</script>
