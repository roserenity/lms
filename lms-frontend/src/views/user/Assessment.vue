<template>
  <section id="" v-if="!loading">
    <div class="d-flex col-lg-9 mx-auto f14 roboto fw500">
      <v-btn text @click="backAssessment" class=" f14 poppins primary--text">
        <v-icon>mdi-chevron-left</v-icon>
        Back
      </v-btn>
    </div>
    <v-row justify="center" align="center" v-if="!start">
      <v-col cols="12" lg="9">
        <StartScreen :data="assessment"  :link="false" @start="startQuiz"/>
      </v-col>
    </v-row>
    <Quiz 
      :data="assessment" 
      :answers="answers" 
      @submit="showSubmitDialog" 
      :reviewAnswers="reviewAnswers"
      v-else
    />
    <LeaveAlert 
      :dialog="dialog" 
      @submit="leaveQuiz"
      @cancel="dialog.status=false"
    />
    <DialogSubmit 
      :dialog="submitDialog" 
      :data="assessment" 
      @submit="submit"
      @cancel="submitDialog=false"
      @review="review"
    />
    <DialogSuccess :dialog="successDialog"/>

  </section>
  <circular v-else/>
</template>

<script>
import Quiz from './Quiz.vue'
import { mapActions, mapMutations, mapState } from 'vuex'
import LeaveAlert from '../../components/user/assessment/LeaveAlert.vue'
import StartScreen from '../../components/user/assessment/StartScreen.vue'
import DialogSubmit from '../../components/user/assessment/DialogSubmit.vue'
import DialogSuccess from '../../components/user/assessment/DialogSuccess.vue'

export default {
  components: {
    StartScreen,
    Quiz,
    LeaveAlert,
    DialogSubmit,
    DialogSuccess
  },
  computed: {
    ...mapState('usr', {
      assessment: (state) => state.assessments,
      startAssessment: (state) => state.startAssessment,
    })
  },  
  data: () => ({
    start: false,
    loading: false,
    answers: [],
    dialog: {
      status: false,
      action: ''
    },
    reload: false,
    submitDialog: false,
    successDialog: false,
    post_type: '',
    reviewAnswers: false,
  }),

  mounted() {
    this.loading = true
    this.fetchAssessment()

    window.onfocus = function() {
      // console.log('window focus');
    }

    window.onblur = function(e) {
      // console.log(e);
      // alert('blur')
    }
  },

  methods: {
    ...mapActions('usr', ['getAssessmentAction', 'storeAssessmentAnswersAction']),
    
    fetchAssessment() {
      
      this.answers = []
      this.getAssessmentAction(this.$route.params.uuid)
        .then(_ => {
        
        if(this.assessment.shuffle_questions) this.shuffleQuestions()
        
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
      })
    },

    startQuiz() {
      this.start=true

      this.$store.commit('usr/startAssessmentMutation', {
        action: 'start',
        status: true
      })
    },

    leaveQuiz(){
      this.loading = true
      this.post_type='left'
      this.$store.commit('usr/startAssessmentMutation', {
        action: 'left',
        status: false
      })
      this.start = false
      this.dialog = {
        status: false,
        action: ""
      }
      this.savedAnswers()
    },

    backAssessment(){
      if(this.start){
        this.dialog = {
          status: true,
          action: "leave"
        }
      } else this.$router.back()
    },

    showSubmitDialog() {
      if(!this.reviewAnswers) {
        this.submitDialog=true
      } else this.submit()

    },

    review(){
      this.submitDialog = false
      this.reviewAnswers = true
    },

    submit() {
      this.submitDialog=false
      this.post_type='submitted'
      this.$store.commit('usr/startAssessmentMutation', {
        action: 'submitted',
        status: false
      })

      this.savedAnswers()
      this.successDialog=true
      this.start = false
      this.reviewAnswers =  false
    },  

    savedAnswers() {
      var form = new FormData()
      form.append('assessment_id', this.assessment.id)
      form.append('post_type', this.post_type)
      form.append('course_id', this.assessment.course_id)
      this.answers.forEach((el, i) => {
        form.append('question_id[]', el.question_id)
        form.append('answer[]', el.answer)
        form.append('support_answer[]', el.support_answer)
        if(el.file) {
          form.append(`file[${i}]`, el.file) 
        } else {
          form.append(`file[${i}]`, '') 
        }
        form.append('is_require_support_answer[]', el.is_require_support_answer)
        form.append('is_require_file_attachment[]', el.is_require_file_attachment)
      })

      this.storeAssessmentAnswersAction(form).then(_ => {
        this.fetchAssessment()
      })

    },

    checkAnswers() {
      let status = true;
      this.answers.every(el => {
        el.errorMessages = ''
        if(!el.answer) {
          el.errorMessages = 'Answer is required'
          status = false
          return false
        }

        if(el.is_require_file_attachment && !el.file) {
          el.errorMessages = 'File is required'
          status = false
          return false
        }

        if(el.is_require_support_answer && !el.support_answer) {
          el.errorMessages = 'Support answer is required'
          status = false
          return false
        }
        status = true
        return true
      })

      return status
    },

    // beforeReload(e) {
    //   if(!this.start) return
    //   console.log(e);
      // e.preventDefault();
      // e.returnValue='Are you sure you want to exit?'
    // },

    shuffleQuestions() {
      return this.assessment.assessment_questions.sort(() => Math.random() - 0.5)
    }
  },

  watch: {
    //time over or ended
    startAssessment(val) {
      if(!val.status && val.action == 'ended') {
        this.post_type='ended'
        this.savedAnswers()
        this.start = false

      }
    },

    successDialog(val) {
      if(val) {
        setTimeout(() => {
          this.successDialog= false
        }, 3000);
      }
    }
  },

  // beforeRouteLeave(to, from, next) {
  //   if(this.start && confirm('Change you made may not be saved')){
  //     this.post_type='left'
  //     this.$store.commit('usr/startAssessmentMutation', {
  //       action: 'left',
  //       status: false
  //     })
  //     this.savedAnswers()
  //     this.start = false
  //     next()
      
  //   } return false
    
  //   next()  
  // },

  
  
}
</script>