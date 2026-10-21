<template>
  <section>
    <LinearProgress v-if="loading" />
    <v-card class="custom-border" height="100%">
        <v-sheet style="background-color: rgba(63, 93, 208, 0.04)" rounded class="px-3 py-8 d-flex flex-column align-center" min-height="55vh">
            <div class="f21 fw600 roboto secondary-1--text text-center">
                {{ eval.title }}
            </div>
            <div v-html="eval.description ? $dompurifier(eval.description) : ''" class="f14 roboto secondary-1--text ma-5 mx-5"></div>
            <v-sheet color="transparent" max-width="500" class="my-10 mx-auto">
              <div class="row justify-center f14">
                <div class="col-6 fw500" v-if="eval.hasOwnProperty('course_id')">
                  No of attempts done: <span class="fw400 mx-2"> {{ eval.hasOwnProperty('student_course_evaluations_count') ? eval.student_course_evaluations_count : 0 }}</span>
                </div>
                <div class="col-6 fw500" v-if="eval.hasOwnProperty('module_id')">
                  No of attempts done: <span class="fw400 mx-2"> {{ eval.hasOwnProperty('student_module_evaluations_count') ? eval.student_module_evaluations_count : 0 }}</span>
                </div>
                <div class="col-6 fw500 text-center" v-if="eval.hasOwnProperty('submission_limit')">
                  No. of allowed attempts: <span class="fw400 mx-2">{{ eval.submission_limit }}</span>
                </div>
                <div class="col-6 fw500" v-if="eval.hasOwnProperty('course_id')">
                  Date accomplished: <span class="fw400 mx-2">{{ eval.student_course_evaluations.length > 0 ? $dateFormat.mmDDyy(eval.student_course_evaluations[0].created_at) : ''}}</span>
                </div>
                <div class="col-6 fw500 text-center" v-if="eval.hasOwnProperty('course_id')">
                  Time accomplished: <span class="fw400 mx-2">{{ eval.student_course_evaluations.length > 0 ? $dateFormat.hhMM(eval.student_course_evaluations[0].created_at) : ''}}</span>
                </div>
                <div class="col-6 fw500" v-if="eval.hasOwnProperty('module_id')">
                  Date accomplished: <span class="fw400 mx-2">{{ eval.student_module_evaluations.length > 0 ? $dateFormat.mmDDyy(eval.student_module_evaluations[0].created_at) : ''}}</span>
                </div>
                <div class="col-6 fw500 text-center" v-if="eval.hasOwnProperty('module_id')">
                  Time accomplished: <span class="fw400 mx-2">{{ eval.student_module_evaluations.length > 0 ? $dateFormat.hhMM(eval.student_module_evaluations[0].created_at) : ''}}</span>
                </div>
              </div>
            </v-sheet>
            <v-alert 
              v-if="disabledStart(eval.submission_limit, eval.hasOwnProperty('student_course_evaluations_count') ? eval.student_course_evaluations_count : eval.student_module_evaluations_count)"
              outlined type="error" 
              class="col-lg-8 mx-auto f12"
              dense
            >
              You have reached the maximum number of attempts
            </v-alert>
            <v-alert 
              v-else-if="eval.hasOwnProperty('course_id') ? eval.course_evaluation_questions_count === 0 : eval.module_evaluation_questions_count === 0"
              outlined color="error" 
              type="warning"
              class="col-lg-8 mx-auto roboto f12 d-flex flex-row"
              dense
            >
              There are no questions available in this evaluation
            </v-alert>
            <v-alert 
              v-else-if="eval.hasOwnProperty('course_id') ? eval.course_evaluation_is_locked : eval.module_evaluation_is_locked"
              outlined color="error" 
              class="col-lg-8 mx-auto d-flex flex-row"
              icon="mdi-lock-outline"
              dense
            >
              <div class="f14 roboto fw500 error--text">This evaluation is locked. </div>
            </v-alert>
            <div class="text-center">
              <ButtonPrimary 
                :label="'Start Evaluation'"
                v-if="eval.hasOwnProperty('course_id') ? eval.course_evaluation_questions_count > 0 : eval.module_evaluation_questions_count > 0"
                :disabled="(eval.hasOwnProperty('course_id') ? eval.course_evaluation_is_locked : eval.module_evaluation_is_locked) || disabledStart(eval.submission_limit, eval.hasOwnProperty('student_course_evaluations_count') ? eval.student_course_evaluations_count : eval.student_module_evaluations_count)"
                @click="$emit('start')"
              />
            </div>
            <div class="text-center">
              <v-btn 
                text
                class="poppins f12 fw500 text-capitalize" 
                color="primary"
                v-if="current_content_type === 'course_evaluation' ? eval.student_course_evaluations_count > 0 : eval.student_module_evaluations_count > 0"
                @click="$emit('viewSubmission')"
              >
                View Latest Submission
              </v-btn>
            </div>
        </v-sheet>
    </v-card>
  </section>
</template>

<script>
import { mapActions, mapState, mapMutations } from 'vuex';

export default {
  props: ['evaluation', 'current_content_type'],
  data: () => ({
    eval: {},
    loading: false
  }),
  methods: {
    ...mapActions('usr', [
      'getModuleEvaluationAction',
      'getCourseEvaluationAction'
    ]),

    disabledStart(max_attempt, attempts) {
      if(attempts >= max_attempt) {
        return true
      } else false
    },

    getModuleEvaluation(){
      this.loading = true
      this.getModuleEvaluationAction(this.evaluation.id).then(res => {
          this.eval = res
          this.loading = false
      }).catch(() => {
          this.loading = false
      })
    },
    
    getCourseEvaluation(){
      this.loading = true
      this.getCourseEvaluationAction(this.evaluation.id).then(res => {
          this.eval = res
          this.loading = false
      }).catch(() => {
          this.loading = false
      })
    },

  },

  created(){
    if(this.current_content_type === 'module_evaluation') {
      this.getModuleEvaluation()
    }
    if(this.current_content_type === 'course_evaluation') {
      this.getCourseEvaluation()
    }
  },

  watch: {
    evaluation(){
      if(this.current_content_type === 'module_evaluation') {
        this.getModuleEvaluation()
      }
      if(this.current_content_type === 'course_evaluation') {
        this.getCourseEvaluation()
      }
    }
  }
}
</script>
