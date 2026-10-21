<template>
    <div class="d-flex align-center ml-7">
      <v-text-field
        dense
        outlined
        hide-details
        prepend-inner-icon="mdi-notebook-edit-outline"
        class="no-outline-custom-field roboto f14 fw500 secondary--text"
        v-model="item.title"
        readonly
      >
        <template v-slot:append>
          <v-menu offset-y transition="slide-y-transition" bottom>
            <template v-slot:activator="{ on, attrs }">
              <v-btn icon v-bind="attrs" v-on="on" style="margin: -6px" dense>
                <v-icon size="20"> mdi-dots-vertical </v-icon>
              </v-btn>
            </template>
            <v-list dense class="custom-border">
              <v-list-item link @click="$emit('viewAssessment', { ...item, is_topic: true, topic_or_sub_topic_id: topic_or_subtopic_id, topic_or_subtopic_title: topic_or_subtopic_title })">
                <v-list-item-title class="roboto f14 d-flex align-center">
                  <v-icon size="22" class="mr-1">mdi-eye-outline</v-icon>
                  <div class="">View Assessment</div>
                </v-list-item-title>
              </v-list-item>
              <v-list-item
                v-if="!$isReadOnly.isReadOnly(status, permission)"
                link
                @click="remove(item)"
              >
                <v-list-item-title class="roboto f14 d-flex align-center">
                  <v-icon size="22" class="mr-1">mdi-delete-outline</v-icon>
                  <div class="">Delete Assessment</div>
                </v-list-item-title>
              </v-list-item>
            </v-list>
          </v-menu>
        </template>
      </v-text-field>
      <DeleteDialog 
        :dialog="dialog"
        type="Assessment"
        :loading="loading"
        @close="dialog=false"
        @delete="deleteAssessment"
      />
    </div>
  </template>
  
  <script>
  import ViewMaterial from "./ViewMaterial.vue";
  import { mapActions, mapState, mapMutations } from "vuex";

  export default {
    props: ["item", "topic_or_subtopic_id", "topic_or_subtopic_title", "status", "permission"],
    components: { ViewMaterial },
    data: () => ({
      dialog: false,
      itemToRemove: null,
      loading: false
    }),
    methods: {
      ...mapMutations([
        'alertMutation'
      ]),

      ...mapActions("instructor", [
        "deleteAssessmentAction",
      ]),

      remove(val) {
        this.itemToRemove = val;
        this.dialog = true;
      },

      deleteAssessment(){
        this.loading = true
        this.deleteAssessmentAction({ id: this.itemToRemove.id }).then(() => {
          this.dialog = false
          this.alertMutation({
            show: true,
            text: 'Assessment successfully deleted',
            type: "success"
          })
          this.loading = false
          this.$emit('updateModules')
        }).catch(() => {
          this.dialog = false
          this.loading = false
          this.alertMutation({
            show: true,
            text: 'Something went wrong in deleting your assessment',
            type: "error"
          })
        })
      },
      
    }
  };
  </script>
  