<template>
    <v-sheet id="form" class="roboto custom-border border pa-10 mb-5">
      <div v-for="(item, i) in data.assessment_questions" :key="item.id">
        <QuizHeader :i="i" :item="item" :start="true"/>
        <Choices 
          :answers="answers" 
          :i="i" 
          :item="item"
          :errorFile="errorFile"
          @getDropFiles="getDropFiles"
          @findFile="findFile"
        />
        <v-divider class="my-10" v-if="i+1 != data.assessment_questions_count"/>
      </div>
      <div class="d-flex align-center justify-end ma-7">
        <v-btn
          color="primary"
          class="text-capitalize poppins "
          @click="$emit('submit')"
          :disabled="buttonDisable(data.assessment_questions)"  
        >Submit</v-btn>
      </div>
    </v-sheet>
</template>

<script>
import QuizHeader from './QuizHeader.vue'
import Choices from './Choices.vue'

export default {
  props: ['data', 'answers', 'errorFile', 'start'],
  components: {
    QuizHeader,
    Choices
  },
  methods: {
    findFile(i) {
      this.$emit('findFile', i)
    },
    getDropFiles(i) {
      this.$emit('getDropFiles', i)
    },
    buttonDisable(questions) {
      return questions.some((item, i) => {
        if( item.type_of_question == "true_or_false" ||  item.type_of_question == 'multiple_choice' ||  item.type_of_question == 'identification') {
          if (item.require_file_attachment ) {
            return this.answers[i].answer == '' || this.answers[i].file == null
          } else return this.answers[i].answer == ''
        } else if ( item.type_of_question == "justified_multiple_choice" || item.type_of_question == 'modified_true_or_false') {
          return this.answers[i].answer == '' || this.answers[i].support_answer == ''
        } else if ( item.type_of_question == "essay" ) {
          // if (item.require_file_attachment ) {
          //   return this.answers[i].answer == '' || this.answers[i].file == null
          // } else return this.answers[i].answer == ''
        }
      })
    }
  }
}
</script>
