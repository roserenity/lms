<template>
    <section id="student-assessment" v-if="!loading">
      <Breadcrumb
        :links="[
          {
            label: 'Back',
            link: true,
            route: (prevRoute && prevRoute.name === 'Instructor Scorm Activity') ? { name: 'Instructor Scorm Activity' , params: { id: $route.params.id, scorm_activity_id: $route.params.scorm_activity_id }, query: { ...prevRoute.query}} : { name: 'Instructor Scorm Activity' , params: { id: $route.params.id, scorm_activity_id: $route.params.scorm_activity_id }, query: { search: '', page: 1, paginate: 10 }},
          },
          {
            label: `${title}`,
            link: false,
            route: null,
          },
        ]"
      />

      <v-sheet class="custom-border border mb-3 pa-10">
        <LinearProgress v-if="loading"/>
        <div v-if="!loading">
          <h3 class="roboto fw600 mb-3">
            {{ user.full_name }}
          </h3>
          <v-row dense>
            <v-col cols="3" class=" text-center" v-if="score">
              <div class="poppins f11 secondary-2--text fw500">STATUS</div>
              <div v-if="score.passed" class="roboto f15 fw500">{{score.passed ? 'PASSED' : 'FAILED'}}</div>
              <div v-else class="roboto f15 fw500">-</div>
            </v-col>
            <v-col cols="3" class=" text-center" v-if="score && score.scorm_activity">
              <div class="poppins f11 secondary-2--text fw500">PASSING SCORE</div>
              <div v-if="score.scorm_activity.passing_score" class="roboto f15 fw500">{{score.scorm_activity.passing_score}}%</div>
              <div v-else class="roboto f15 fw500">-</div>
            </v-col>
            <v-col cols="3" class=" text-center" v-if="score && score.score">
              <div class="poppins f11 secondary-2--text fw500">SCORE</div>
              <div class="roboto f15 fw500">{{score.score}}%</div>
            </v-col>
            <v-col cols="3" class=" text-center">
              <div class="poppins f11 secondary-2--text fw500">DATE SUBMITTED</div>
              <div class="roboto f15 fw500" v-if="score">{{$dateFormat.mmDDyy(score.created_at)}}</div>
            </v-col>
          </v-row>
        </div>
      </v-sheet>

      <v-sheet class="custom-border border pa-10">
        <LinearProgress v-if="loading"/>
        <div class="mb-4 fade" v-for="(item, i) in answers" :key="i" v-if="!loading">
          
          <v-row dense>
            <v-col cols="9" md="10">
              <div class="poppins fw600 f14 secondary-2--text">Question {{ i+1 }} :</div>
              <div class="question-div roboto f15 secondary-1--text mb-4" v-html="item.question"/>
            </v-col>
            <v-col cols="3" md="2" class="ml-auto text-center"  v-if="item.type_of_question !== 'QA_SECTION'">
              <v-alert v-if="item.scorm_activity_answers[0]" dense small :color="item.scorm_activity_answers[0].is_correct ? '#7BC14533' : '#F8835E33'" class="ma-auto f12" :class="item.scorm_activity_answers[0].is_correct ? 'success--text' : 'error--text'">
                  {{ item.scorm_activity_answers[0].is_correct ? 'CORRECT' : 'WRONG' }}
              </v-alert>
            </v-col>
          </v-row>

          <FormLabel :label="'ANSWER:'" />
          <RadioButtons class="f12" :preview="true" v-if="item.type_of_question === 'MULTIPLE_CHOICE'" :choices="getChoices(item.choices)" :answer="item ? getAnswer(item.scorm_activity_answers) : ''" />
          <Checkboxes class="f12" :preview="true" v-else-if="item.type_of_question === 'MULTIPLE_RESPONSE'" :choices="getChoices(item.choices)" :answer="item ? getAnswerMultiple(item.scorm_activity_answers) : []" />          
          <div class="f12 col-md-8 col-12 pa-0" v-else-if="['FILL_IN_THE_BLANK', 'QA_SECTION'].includes(item.type_of_question)">
              <v-textarea class="poppins" outlined dense hide-details auto-grow readonly :value="item ? getAnswer(item.scorm_activity_answers) : ''"></v-textarea>
          </div>
          <MatchingType class="f12" v-else-if="item.type_of_question === 'MATCHING'" :matchingPairs="item ? getAnswer(item.scorm_activity_answers) : []"/>
          
          
          <section v-if="item.type_of_question !== 'QA_SECTION'">
            <FormLabel :label="item.type_of_question !== 'FILL_IN_THE_BLANK' ? 'CORRECT ANSWER:' : 'ACCEPTED ANSWERS:'" />
            <v-sheet class="rounded-lg pa-2 mb-5 f14" color="dark-1">
              <span v-if="['MULTIPLE_CHOICE', 'MULTIPLE_RESPONSE'].includes(item.type_of_question)">
                {{ getCorrectAnswer(item.correct_answers) }}
              </span>
              <span v-else-if="item.type_of_question !== 'MATCHING'">
                {{item.correct_answers.join(', ') }}
              </span>
              <MatchingType class="f12" v-else :matchingPairs="item.correct_answers"/>
            </v-sheet>
          </section>
          <v-divider class="my-8" v-if="i !== answers.length - 1"/>
        </div>
      </v-sheet>
    </section>
    <circular v-else/>
</template>

<script>
import { mapActions, mapMutations, mapState } from "vuex";
import Question from "@/components/teacher/grading/Question.vue";
import RadioButtons from '@/components/teacher/evaluation/RadioButtons.vue';
import Checkboxes from '@/components/teacher/evaluation/Checkboxes.vue';
import MatchingType from '@/components/teacher/evaluation/MatchingType.vue';
import Breadcrumb from "../../layouts/teacher/InstructorBreadcrumbs.vue";

export default {
  components: {
    Question,
    RadioButtons,
    Checkboxes,
    MatchingType,
    Breadcrumb
  },
  methods: {
    ...mapActions('instructor', ['getScormActivitiesRespondentAnswerAction', 'getScormActivityQuestionsAction']),

    ...mapMutations(['alertMutation']),

    getData(){
      if(!this.loading) {
        this.loading = true
        this.getScormActivitiesRespondentAnswerAction({
          course_id: this.$route.params.id,
          scorm_activity_id: this.$route.params.scorm_activity_id,
          user_id: this.$route.params.user_id
        }).then(res => {
          this.user = res.user
          this.score = res.score
          this.title = res.score.scorm_activity.title
        }).catch(() => {
          this.alertMutation({
            show: true,
            text: `Something went wrong.`,
            type: "error"
          })
        }).finally(() => {
          this.loading = false
        })
      }
    },

    getChoices(data){
      let choices = {}

      data.forEach(choice => {
        choices[choice.id] = choice.title
      })

      return choices;
    },

    getAnswer(data){
      console.log(data)
      if(data.length > 0){
        if( typeof(data[0].answer) === 'string' || Array.isArray(data[0].answer) ) {
          if(data[0].answer.length > 0 && data[0].answer[0].id) {
            return data[0].answer[0].id
          }
          return data[0].answer
        }
        if( data[0].answer.length > 0) {
          return data[0].answer[0].id
        }
      }
      return ''
    },

    getAnswerMultiple(data){
      let answers = []
      if(data.length > 0){
        if( data[0].answer.length > 0) {
          data[0].answer.forEach(ans => {
            answers.push(ans.id)
          })
        }
      }
      return answers
    },

    getCorrectAnswer(data){
      if(!data) return ''
      return data.map(answer => answer.title)        // Extract the titles
        .join(', '); 
    },

  },
  mounted(){
    this.getData()
  },
  computed: {
    ...mapState('instructor', {
        answers: (state) => state.scorm_activity_answers,
    }),
  },

  data: () => ({
    loading: false,
    prevRoute: null,
    user: null,
    score: null,
    title: ''
  }),

  watch: { 
    $route(to, from) { 
      this.getData()
    }
  },

  beforeRouteEnter(to, from, next) {
    next(vm => {
        vm.prevRoute = from          
    })
  },
};
</script>