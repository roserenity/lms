<template>
  <section id="student-assessment" v-if="!loading">
    <v-btn text class="poppins mb-3" color="primary" dense 
      @click="(prevRoute && prevRoute.name === 'Instructor Grading Assessment') ? $router.back() : $router.push({ name: 'Instructor Grading Assessment' , params: { id: $route.params.id }, query: { search: '', page: 1, paginate: 10 }})" @displayStudentAssessment="getData()">
      <v-icon small>
        mdi-chevron-left
      </v-icon>
      Back
    </v-btn>
    <HeaderSearch :data="gradingState" @updateStatus="updateStatus" :user_list="gradingUsers" :sum_score="sum_score" :status="checked"/>
    <v-sheet class="custom-border border my-7">
      <!-- <Question v-for="(item, i) in assessment_questions" :key="i" :i="i" :question="item" :answer="[]" @save="save" :loading="saveLoading"/> -->
      <Question v-for="(item, i) in gradingState.question_answers" :key="i" :i="i" :data="item" @save="save" :loading="saveLoading"/>
    </v-sheet>
  </section>
  <circular v-else/>
</template>

<script>
import { mapActions, mapMutations, mapState } from "vuex";
import HeaderSearch from "@/components/teacher/grading/HeaderSearch.vue";
import Question from "@/components/teacher/grading/Question.vue";

export default {
  components: {
    HeaderSearch,
    Question,
  },
  methods: {
    ...mapActions('instructor', ['updateGradingAction', 'getGradingAction', 'getGradingStudent', 'getInstructorAssessmentAction']),

    ...mapMutations(['alertMutation']),

    updateStatus(e){
      this.grading['status'] = e
    },

    save(){
      this.saveLoading = true
      this.gradingState.question_answers.forEach(item => {
        this.grading.question_answer_id.push(item.id)
        this.grading.scores.push(item.score ? item.score : 0)
        this.grading.comments.push(item.question_answer_feedback ? item.question_answer_feedback.comment : null)
      })
      this.updateGradingAction({
        user_assessment_id:  parseInt(this.$route.params.user_assessment_id),
        user_id: this.gradingState.score.user.id,
        assessment_id: this.gradingState.id,
        question_answer_id: this.grading.question_answer_id,
        score: this.grading.scores,
        checked: this.grading.status == null ? this.gradingState.score.checked ? true : false : this.grading.status,
        comment: this.grading.comments
      }).then(()=>{
        this.alertMutation({
          show: true,
          text: `Successfully Updated for ${this.gradingState.score.user.first_name} ${this.gradingState.score.user.last_name}`,
          type: "success"
        })
      }).catch(() => {
        this.alertMutation({
          show: true,
          text: `Something went wrong`,
          type: "error"
        })
      }).finally(() => {
        this.saveLoading = false
      })
    },

    getData(){
      this.loading = true
      this.getGradingAction({
        assessment_id: this.$route.params.id,
        user_id: this.$route.params.user_id,
        user_assessment_id: this.$route.params.user_assessment_id
      }).then(res=>{
        this.checked = res.score.checked
        this.sum_score = res.total_points
        this.loading = false
        // this.getGradingStudent(this.$route.params.id).finally(() => {
          
        // })
      }).catch(() => {
        this.loading = false
      })
    }
  },
  mounted(){
    this.getData()
  },
  computed: {

    ...mapState('instructor', {
      assessment_questions: (state) => state.assessment_questions,
      gradingState: (state) => state.grading,
      gradingUsers(state) {
        var user_list = []
        state.gradingUsers.forEach(item => {
          user_list.push({ value: item.user.id, text: `${item.user.first_name ? item.user.first_name : ''} ${item.user.last_name ? item.user.last_name : ''} ${item.user.suffix ? item.user.suffix : ''}`})
        })

        return user_list
      }
    })
  },

  data: () => ({
    grading: {
      status: null,
      answers: [],
      question_answer_id: [],
      scores: [],
      comments: []
    },
    saveLoading: false,
    sum_score: 0,
    loading: true,
    checked: 0,
    prevRoute: null,
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

<style></style>
