<template>
  <section>
    <v-sheet class="fade" color="transparent" :height="lists?'': '80vh'">
      <v-row align="center" justify="center" class="fill-height">
        <v-col cols="11">
          <v-btn 
            text 
            color="primary" 
            dense 
            class="px-1" 
            @click="backAssessment">
            <v-icon small>
            mdi-chevron-left
            </v-icon>
            BACK
          </v-btn>
          <v-sheet id="form" class="roboto custom-border border pa-10 mb-5">
            <div class="ma-2">
                <label class="poppins f11 secondary-2--text fw500">ASSESSMENT TITLE</label>
                <h3 class="roboto fw600">
                  {{`${data.title}`}}
                </h3>
            </div>
            <div class="d-flex flex-wrap justify-space-around align-start">
              <div class="ma-2 text-center">
                <div class="poppins f11 secondary-2--text fw500">DUE DATE</div>
                <div class="roboto f15 fw500" v-if="data.due_date">{{(JSON.parse(data.due_date).end ? $dateFormat.mmDDyy(JSON.parse(data.due_date).end) : '-')}}</div>
                <div class="roboto f15 fw500" v-else>-</div>
              </div>
              <div class="ma-2 text-center">
                <div class="poppins f11 secondary-2--text fw500">DATE SUBMITTED</div>
                <div class="roboto f15 fw500">{{$dateFormat.mmDDyy(assessment.assessment_score.created_at)}}</div>
              </div>
              <div class="ma-2 text-center">
                <div class="poppins f11 secondary-2--text fw500">YOUR SCORE</div>
                <v-alert dense small :color="'#BDBDBD33'" class="ma-auto">
                  {{ assessment.assessment_score.percentage }} %
                  <div class="poppins f10 fw500"> ( {{ assessment.assessment_score.score % assessment.assessment_score.score === 0 ? parseInt(assessment.assessment_score.score) : assessment.assessment_score.score }} / {{ assessment.total_points }} pts ) </div>
                </v-alert>
              </div>
              <div class="ma-2 text-center">
                <div class="poppins f11 secondary-2--text fw500">CHECKED STATUS</div>
                <v-alert v-if="!data.require_checking" dense small color="#BDBDBD33" class="ma-auto">
                  -
                </v-alert>
                <v-alert v-if="data.require_checking" dense small :color="assessment.checked ? '#7BC14533' : '#BDBDBD33'" class="ma-auto" :class="assessment.checked ? 'success--text' : 'secondary-2--text'">
                    {{ assessment.checked ? 'CHECKED' : 'PENDING' }}
                </v-alert>
              </div>
              <div class="ma-2 text-center">
                <div class="poppins f11 secondary-2--text fw500">STATUS</div>
                <v-alert v-if="!data.passing_score" dense small color="#BDBDBD33" class="ma-auto">
                  -
                </v-alert>
                <v-alert v-else dense small :color="assessment.assessment_score.status === 'PASSED' ? '#7BC14533' : '#E49E3233'" class="ma-auto" :class="assessment.assessment_score.status === 'PASSED' ? 'success--text' : 'error--text'">
                    {{ assessment.assessment_score.status }}
                </v-alert>
              </div>
            </div>
          </v-sheet>
          
          <v-sheet id="form" class="roboto custom-border border pa-10 mb-5">
            <div v-for="(item, i) in assessment.assessment_questions" :key="item.id">
                <QuizHeader 
                  :i="i" 
                  :item="item" 
                  :checked="assessment.assessment_scores[0].checked"
                  :score="getIndividualScore(assessment.assessment_scores[0].question_answer, item.id) ? getIndividualScore(assessment.assessment_scores[0].question_answer, item.id)[0] : null"
                  :start="false"
                />
                <ChoicesWithAnswers 
                  :assessment_question="item"
                  :checked="assessment.assessment_scores[0].checked"
                  :answer="assessment.assessment_scores[0].question_answer.find(_item => _item.assessment_question_id === item.assessment_answer.assessment_question_id)" 
                  :i="i" 
                  :item="item"
                />
                <v-divider class="my-10" v-if="i+1 != assessment.assessment_questions_count"/>
            </div>
          </v-sheet>
        </v-col>
      </v-row>
    </v-sheet>
  </section>
</template>

<script>
import { mapState } from 'vuex';
import OnePager from '@/components/user/assessment/OnePager.vue';
import ChoicesWithAnswers from '@/components/user/assessment/ChoicesWithAnswers.vue';
import QuizHeader from '@/components/user/assessment/QuizHeader.vue';

export default {
  props: ['data'],

  components: {
    OnePager,
    ChoicesWithAnswers,
    QuizHeader
  },
  
  data: () => ({
    lists: true,
    indexFile: null,
    formHasErrors: false,
    errorFile: false,
    fileTypes: /(\.jpg|\.jpeg|\.png|\.docx|\.pdf)$/i /** regex */
  }),

  mounted(){
      // console.log(this.answers)
  },
  
  computed: {
    ...mapState('usr', {
      assessment: (state) => state.assessments,
    })
  },

  methods: {
      backAssessment(){
          this.$emit('backSubmitted')
      },

      calculatePercentage(a, b){
        return ((a/b) * 100).toFixed(2) + ' %';
      },

      getIndividualScore(item, id){
        return item.filter(_item => _item.assessment_question_id === id);
      }
    }
  }
  </script>