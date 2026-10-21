<template>
  <section>
    <LinearProgress v-if="loading" />
    <v-card class="custom-border" height="100%">
      <v-sheet style="background-color: rgba(63, 93, 208, 0.04)" rounded class="px-3 py-10 d-flex flex-column align-center" min-height="55vh">
        <div class="f21 fw600 roboto secondary-1--text text-center">
          {{data.title}}
        </div>
        <div v-html="data.description ? $dompurifier(data.description) : ''" class="f14 fw500 roboto secondary-1--text ma-5 mx-5"></div>
        <v-sheet color="transparent" max-width="500" class="my-10 mx-auto">
          <div class="row justify-center f14">
            <div class="col-6 d-flex align-center fw500">
              Due date: <span class="fw400 mx-2" v-if="data.due_date">{{JSON.parse(data.due_date) ? JSON.parse(data.due_date).end : ''}}</span>
            </div>
            <div class="col-6 fw500">
              Total Points: <span class="fw400 mx-2">{{ assessment.total_points }}</span>
            </div>
            <div class="col-6 fw500">
              No of Questions: <span class="fw400 mx-2">{{ assessment.assessment_questions_count }}</span>
            </div>
            <div class="col-6 fw500">
              Time Limit: <span class="fw400 mx-2">{{ data.time_limit ? (data.time_limit === 1 ? data.time_limit+' minute' : data.time_limit+' minutes') : 'none'}}</span>
            </div>
            <div class="col-6 fw500">
              No of attempts done: <span class="fw400 mx-2">{{assessment.assessment_score ? assessment.assessment_score.attempts : 0}}</span>
            </div>
            <div class="col-6 fw500">
              No of allowed attempts: <span class="fw400 mx-2"> {{ data.max_attempt == null ? 1 : data.max_attempt}}</span>
            </div>
            <div class="col-6 fw500">
              Date accomplished: <span class="fw400 mx-2">{{ data.assessment_score.created_at && $dateFormat.mmDDyy(data.assessment_score.created_at) }}</span>
            </div>
            <div class="col-6 fw500">
              Time accomplished: <span class="fw400 mx-2"> {{ data.assessment_score.created_at && $dateFormat.hhMM(data.assessment_score.created_at) }}</span>
            </div>
            <div class="col-12 fw500" v-if="data.require_checking && (assessment.assessment_score ?  assessment.assessment_score.attempts > 0 : false)">
              Checked Status: <span> {{ assessment.assessment_scores[0].checked ? 'CHECKED' : 'PENDING' }} </span>
            </div>
          </div>
        </v-sheet>
        <v-alert 
          v-if="assessment.assessment_score && assessment.assessment_score.attempts && (assessment.assessment_score >= (data.max_attempt == null ?  1 : data.max_attempt))"
          outlined type="error" 
          class="col-lg-8 mx-auto"
        >
          <div class="f14 roboto fw500 error--text">You have reached the maximum number of attempts</div>
        </v-alert>
        <v-alert 
          v-else-if="data.assessment_is_locked"
          outlined color="error" 
          class="col-lg-8 mx-auto d-flex flex-row"
          icon="mdi-lock-outline"
          dense
        >
          <div class="f14 roboto fw500 error--text">This assessment is locked. </div>
        </v-alert>
        <v-alert 
          v-else-if="assessment.assessment_questions_count === 0"
          outlined color="error" 
          type="warning"
          class="col-lg-8 mx-auto d-flex flex-row"
          dense
        >
          <div class="f14 roboto fw500 error--text">There are no questions available in this assessment</div>
        </v-alert>
        <div class="text-center">
          <!-- <div class="col-12 fw600 f14" v-show="data.assessment_questions.every((question) => question.type_of_question != 'identification' && question.type_of_question != 'essay')">
            My {{ data.accepted_score ? data.accepted_score.charAt(0).toUpperCase() +  data.accepted_score.slice(1) : ""}} score: <span class=" fw600 f15 mx-2"> {{ data.assessment_score.score }} </span>
          
          </div> -->
          <v-btn 
            class="poppins f12 fw500 mb-3" 
            color="primary"
            @click="start"
            v-if="assessment.assessment_questions_count > 0"
            :disabled="data.assessment_is_locked || (assessment.assessment_score && assessment.assessment_score.attempts >= (data.max_attempt == null ?  1 : data.max_attempt))"
          >
            Start the Quiz
          </v-btn>
        </div>
        <div class="text-center">
          <v-alert 
            v-if="data.assessment_has_essay && data.assessment_score && !data.assessment_score.checked && data.assessment_score.attempts > 0"
            outlined type="warning" 
            class="col-lg-8 mx-auto"
          >
            <div class="f14 roboto fw500 warning--text">Grading in progress. Answers will be visible once complete.</div>
          </v-alert>
          <v-btn 
            text
            class="poppins f12 fw500 text-capitalize" 
            color="primary"
            @click="$emit('viewAccepted')"
            v-else-if="data.assessment_score && data.assessment_score.attempts > 0"
          >
            View Accepted Submission
          </v-btn>
        </div>
      </v-sheet>
    </v-card>
  </section>
</template>

<script>
import { mapActions, mapState, mapMutations } from 'vuex';

export default {
  props: ['data'],

  data: () => ({
    loading: false,
    answers: []
  }),

  created(){
    this.getAssessment()
  },

  methods: {
    ...mapActions('usr', [
      'getAssessmentAction'
    ]),

    ...mapMutations('usr', ['startAssessmentMutation']),
    ...mapMutations(['alertMutation']),

    getAssessment(){
      this.loading = true
      this.answers = []
      this.getAssessmentAction(this.data.uuid).then(() => {
        this.assessment.assessment_questions.forEach(el => {
          this.answers.push({
            question_id: el.id,
            answer: "",
            is_require_support_answer: el.require_support_answer,
            is_require_file_attachment: el.require_file_attachment,
            support_answer: "",
            file: '',
            errorMessages: '',
            loading: false
          })
        });

        this.loading = false
        this.$emit('setAnswers', this.answers)
      }).catch(() => {
        this.alertMutation({
            show: true,
            text: 'Something went wrong in fetching your assessment',
            type: "error"
        })
        this.loading = false
      })
    },
    
    start() {
      this.startAssessmentMutation({
        action: 'start',
        status: true
      })

      this.$emit('start')    
    },
  },

  computed: {
    ...mapState('usr', {
      assessment: (state) => state.assessments,
      startAssessment: (state) => state.startAssessment,
    }),
  },

  watch: {
    data(){
      this.getAssessment()
    }
  }
}
</script>