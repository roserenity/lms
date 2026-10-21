<template>
  <section id="assessment-builder">
    <div class="fw500 f12 error--text">
      Learners have already submitted responses to this assessment. Modifying questions or structure at this stage may lead to inconsistencies in grading or confusion for learners.
    </div>
    <v-expansion-panels
      accordion
      flat
      style="display: block !important;"
    >
      <draggable
        v-model="questions"
        ghost-class="ghost"
        :disabled="$vuetify.breakpoint.mobile ? true : $isReadOnly.isReadOnly(status, permission)"
        @end="() => { sortAssessmentQuestions(questions) ,dragging = false }"
        @start="dragging = true"
      >
        <transition-group type="transition" name="flip-list" tag="div">
          <v-expansion-panel
            v-for="(item, i) in assessment_questions"
            :key="item.id"
            class="panels my-2 secondary-5"
            active-class="toggled"
          >
            <v-expansion-panel-header
              class="roboto f14 fw500"
              :id="`panel-header-${i}`"
            >
              <div class="d-flex align-center">
                <!-- Drag Handle -->
                <v-icon class="mr-2 drag-handle" v-if="!$isReadOnly.isReadOnly(status, permission)">
                  mdi-drag
                </v-icon>

                <!-- Question Label -->
                Question {{ i + 1 }}:
                <span
                  v-if="item.type_of_question"
                  class="ml-5 fw500 text-uppercase secondary-2--text"
                >
                  {{ item.type_of_question.replace(/_/g, " ") }}
                </span>

                <span
                  v-if="item.score"
                  class="ml-5 fw500 text-uppercase secondary-4--text"
                >
                  {{ item.score }} {{ item.score > 1 ? 'pts' : 'pt' }}
                </span>
              </div>

              <v-spacer />

              <div class="d-flex justify-end" v-if="!$isReadOnly.isReadOnly(status, permission)">
                <v-btn icon @click.stop="removeQuestion(item.id)">
                  <v-icon size="18">mdi-trash-can-outline</v-icon>
                </v-btn>
              </div>
            </v-expansion-panel-header>

            <v-expansion-panel-content style="margin-top: -20px">
              <Choices 
                :item="item"
                :assessment_type="type_of_assessment"
                :assessment_has_submissions="assessment.assessment_has_submissions"
                :status="status"
                :permission="permission"
                :updateLoading="updateLoading"
                :successSaved="successSaved"
                :dragging="dragging"
                @inputAnswer="inputAnswer"
                @updateQuestion="updateQuestion"
                @addChoices="addChoices"
                @cancel="cancel"
              />
            </v-expansion-panel-content>
          </v-expansion-panel>
        </transition-group>
      </draggable>
    </v-expansion-panels>
    <div>
      <v-btn
        v-if="!$isReadOnly.isReadOnly(status, permission)"
        outlined
        color="primary"
        class=" float-right poppins f12 fw600 text-capitalize my-3"
        @click="$emit('addQuestion', 'not tab')"
        :loading="loading"
      >
        <v-icon left>mdi-plus</v-icon>
        Add Question
      </v-btn>
      <v-btn
        v-if="!$isReadOnly.isReadOnly(status, permission)"
        outlined
        color="primary"
        class=" float-right poppins f12 fw600 text-capitalize my-3 mr-1"
        @click="importQuestionDialog = true"
      >
        <v-icon left>mdi-file-import-outline</v-icon>
        Import Questions
      </v-btn>
    </div>
    <delete-dialog
      :dialog="dialog"
      :id="idToRemove"
      :course_id="this.$store.state.instructor.course.id"
      store="instructor/removeQuestionAction"
      type="Question"
      @close="dialog = false"
    />
    <ImportQuestionPoolDialog 
        :dialog="importQuestionDialog"
        :type_of_question_pool="'assessment'"
        :submit_loading="submitLoading"
        @close="importQuestionDialog = false"
        @submit="assignQuestionPool"
    />
  </section>
</template>

<script>
import draggable from "vuedraggable";
import Choices from "./Choices.vue";
import { mapActions, mapMutations, mapState } from "vuex";
import ImportQuestionPoolDialog from "../questionpool/ImportQuestionPoolDialog.vue";

export default {
  props: ["assessment", "loading", "type_of_assessment", "status", "permission", "id", "course_id"],
  components: {
    Choices,
    ImportQuestionPoolDialog,
    draggable
  },
  data: () => ({
    type: 0,
    old: null,
    dialog: false,
    idToRemove: null,
    updateLoading: false,
    successSaved: false,
    submitLoading: false,
    importQuestionDialog: false,
    dragging: false,
  }),
  methods: {
    ...mapActions("instructor", ["sortAssessmentQuestionsAction", "updateCourseUpdatedAt", "updateQuestionAction", "getAssessmentsAction", 'inputAnswerMutation', 'assignQuestionPoolToAssessmentAction']),
    ...mapMutations("instructor", ["addChoicesMutation", "inputAnswerMutation"]),
    ...mapMutations(["alertMutation"]),

    removeQuestion(id) {
      this.idToRemove = id;
      this.dialog = true;
    },

    addChoices(choices) {
      this.addChoicesMutation(choices);
    },

    updateQuestion(question){
      this.updateLoading = true;
      this.updateQuestionAction(question).then(() => {
        this.updateLoading = false;
        this.successSaved = true;
        setTimeout(() => {
          this.successSaved = false;
        }, 2000);
      }).catch(() => {
        this.alertMutation({
          show: true,
          text: "Something went wrong.",
          type: "error"
        })
      });
    },

    inputAnswer(answer) {
      this.inputAnswerMutation(answer);
    },

    cancel() {
      this.getAssessmentsAction({ course_id: this.course_id });
    },

    assignQuestionPool(ids) {
      this.submitLoading = true
      this.assignQuestionPoolToAssessmentAction({
          question_pool_ids: ids,
          assessment_id: this.id,
      }).then(() => {
          this.submitLoading = false
          this.alertMutation({
              show: true,
              text: 'Questions successfully imported.',
              type: "success"
          })
          this.importQuestionDialog = false
          
          this.getAssessmentsAction({ course_id: this.course_id });
          this.updateCourseUpdatedAt(this.course_id)
      }).catch(() => {
          this.submitLoading = false
          this.alertMutation({
              show: true,
              text: 'Something went wrong.',
              type: "error"
          })
          this.importQuestionDialog = false
      })
    },

    sortAssessmentQuestions(items){
      
      let payload = {
        assessment_id: this.assessment.id,
        id: []
      }
      this.questions.forEach((item, i) => {
        payload.id.push(item.id)
      }) 
      this.sortAssessmentQuestionsAction(payload).then(() => {
        this.alertMutation({
            show: true,
            text: 'Successfully sorted questions',
            type: "success"
        })
        this.updateCourseUpdatedAt(this.course_id)
      }).catch(() => {
        this.alertMutation({
            show: true,
            text: 'Something went wrong in sorting questions',
            type: "error"
        })
      })
    }
  },

  computed: {
    ...mapState("instructor", {
      course: (state) => state.course,
      assessment_questions: (state) => state.assessment_questions,
    }),
    questions: {
      get() {
          return this.$store.state["instructor"].assessment_questions;
      },
      set(val) {
          return this.$store.commit("instructor/assessmentQuestionsMutation", val);
      },
    }
  },

  watch: {
    dialog(val) {
      if (!val) {
        this.getAssessmentsAction({
          course_id: this.$store.state.instructor.course.id,
        });
      }
    },
  },
};
</script>

<style scoped>
.toggled {
  background-color: #ffff !important;
  border: 1px solid #bdbdbd !important;
  border-radius: 3px !important;
}
.tox.tox-tinymce {
  border-radius: 5px !important;
}
.block-panel {
  display: block !important;
}
</style>
