<template>
  <v-sheet id="form" class="roboto custom-border pa-10 mb-5">
    <div class="" v-for="(item, i) in data.assessment_questions" :key="i">
      <v-sheet v-if="question_number === i">
        <QuizHeader :i="i" :item="item" :start="true" />
        <Choices 
          :answers="answers"
          :i="i"
          :item="item" 
          :errorFile="errorFile"
          @getDropFiles="getDropFiles"
          @findFile="findFile"
        />
      </v-sheet> 
    </div>
    <div class="d-flex align-center mx-5">
      <v-btn 
        :disabled="question_number==0 || data.lock_question_after_answering == 1" 
        @click="prev" 
        outlined 
        color="primary" 
        class="text-capitalize fw600">
        Prev
      </v-btn>
      <v-btn 
        @click="next" color="primary" 
        class="text-capitalize fw500 ml-auto" 
        :disabled="buttonDisable(this.data.assessment_questions[question_number].type_of_question)">
        {{
          question_number === data.assessment_questions.length-1 ? 'Submit' : 'Next'
        }}  
      </v-btn>
    </div> 
  </v-sheet>
</template>

<script>
import QuizHeader from './QuizHeader.vue'
import Choices from './Choices.vue'

export default {
  components: {
    QuizHeader,
    Choices
  },  
  props: ['answers', 'data', 'errorFile'],
  data: () => ({
    question_number: 0,
  }),

  methods: {
    findFile(i) {
      this.$emit('findFile', i)
    },

    getDropFiles(i) {
      this.$emit('getDropFiles', i)
    },
      
    next() {
      if(this.question_number === this.data.assessment_questions.length-1) {
        this.$emit('submit')
        return
      }
      this.question_number=this.question_number+1;
    },
    prev() {
      if(this.question_number === 0) return
      this.question_number=this.question_number-1;
    },
    buttonDisable(type) {
      if( type == "true_or_false" || type == 'multiple_choice' || type == 'identification') {
        if ( this.answers[this.question_number].is_require_file_attachment ) {
          return this.answers[this.question_number].answer == '' || this.answers[this.question_number].file == null
        } else return this.answers[this.question_number].answer == ''
      } else if ( type == "justified_multiple_choice") {
        return this.answers[this.question_number].answer == '' || this.answers[this.question_number].support_answer == ''
      } else if ( type == "essay" ) {
        // if ( this.answers[this.question_number].is_require_file_attachment ) {
        //   return this.answers[this.question_number].answer == '' || this.answers[this.question_number].file == null
        // } else return this.answers[this.question_number].answer == ''
      }
    }
  }
}
</script>