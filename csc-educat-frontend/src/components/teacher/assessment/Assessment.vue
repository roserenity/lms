<template>
  <v-sheet class="custom-border border pa-5 mb-2">
    <div class="d-flex justify-space-between">
      <div class="row pa-0">
        <div class="col-12 col-sm-4">
          <FormLabel
            :label="'TITLE *'"
          />
          <v-text-field
            @click.stop
            dense
            hide-details
            class="secondary-1--text f14 fw600 poppins"
            v-model="item.title"
            v-on:change="updateAssessment(item)"
            error-messages="errors.title"
            :disabled="$isReadOnly.isReadOnly(status, permission)"
          />
        </div>
        <div class="col-12 col-sm-4" v-if="item.topic_or_subtopic_title">
          <FormLabel
            :label="'TOPIC / SUBTOPIC'"
          />
          <v-text-field
            dense
            hide-details
            class="secondary-1--text f14 fw600 poppins"
            readonly
            v-model="item.topic_or_subtopic_title"
            disabled
          />
        </div>
        <div class="col-12 col-sm-4">
          <FormLabel
            :label="'TYPE *'"
          />
          <v-select
            @click.stop
            dense
            :items="types"
            item-text="text"
            item-value="value"
            hide-details
            class="secondary-1--text f14 fw600 poppins"
            v-model="item.type_of_assessment"
            v-on:change="updateAssessment(item)"
            error-messages="errors.type_of_assessment"
            :disabled="$isReadOnly.isReadOnly(status, permission)"
          />
        </div>
      </div>
      
      <v-menu transition="slide-y-transition" bottom offset-y class="align-self-start">
        <template v-slot:activator="{ on, attrs }">
          <v-btn v-bind="attrs" v-on="on" icon v-if="!$isReadOnly.isReadOnly(status, permission)">
            <v-icon size="18">mdi-dots-vertical</v-icon>
          </v-btn>
        </template>
        <v-list dense class="custom-border">
          <v-list-item link @click="share(item)">
            <v-list-item-title class="roboto f14 d-flex align-center">
              <v-icon size="22" class="mr-1">mdi-share</v-icon>
              <div class="">Share</div>
            </v-list-item-title>
          </v-list-item>
          <v-list-item link @click="remove(item)">
            <v-list-item-title class="roboto f14 d-flex align-center">
              <v-icon size="22" class="mr-1">mdi-delete-outline</v-icon>
              <div class="">Delete</div>
            </v-list-item-title>
          </v-list-item>
        </v-list>
      </v-menu>
    </div>
    
    <AssessmentContent :assessment="item" :assessment_questions="assessment_questions" class="mt-2" :status="status" :permission="permission"/>
    <ShareDialog :data="toShare" :dialog="shareDialog" @close="shareDialog = false" />
   <delete-dialog
      :dialog="dialog"
      :id="item.id"
      :course_id="item.course_id"
      type="Assessment"
      store="instructor/deleteAssessmentAction"
      @close="dialog = false"
    />
  </v-sheet>
</template>

<script>
import draggable from "vuedraggable";
import { mapActions } from "vuex";
import AssessmentContent from "./AssessmentContent.vue";
import ShareDialog from "./AssessmentShareDialog.vue";
import types from '../../../constants/assessmenttype'

export default {
  props: ["item", "assessment_questions", "status", "permission"],
  components: {
    draggable,
    AssessmentContent,
    ShareDialog,
  },
  data: () => ({
    types,
    itemToRemove: null,
    idToRemove: null,
    dialog: false,
    shareDialog: false,
    toShare: null,
  }),
  methods: {
    ...mapActions("instructor", [
      "updateAssessmentAction",
      "getAssessmentsAction",
      "getInstructorsToShareAction",
    ]),

    updateAssessment(item) {
      if(item.max_attempt == null) {
        item.max_attempt = 1;
      }
      this.updateAssessmentAction(item);
    },

    share(data) {
      this.getInstructorsToShareAction().then(() => {
        this.shareDialog = true;
        this.toShare = data;
      });
    },

    remove(val) {
      this.dialog = true;
    },
  },
  created() {
      this.$store.commit("instructor/assessmentQuestionsMutation", this.item.assessment_questions);
  },
  watch: {
    dialog(val) {
      if (!val){
        this.getAssessmentsAction(this.item).then(()=>{
          this.$emit('closeAssessment')
        })
        
      }
        return 
    },

    item(val) {
      if(val) {
        this.$store.commit("instructor/assessmentQuestionsMutation", val.assessment_questions);
      }
    },
  },
};
</script>
