<template>
  <div class="mb-7">
    <RadioButtons
      v-if="!['identification', 'essay', 'sliders', 'ordering'].includes(item.type_of_question)"
      class="mx-3"
      :answer="answer.answer"
      :choices="item.assessment_answer.choices"
      :readonly="true"
    />
    <FormTextFieldOptional
      v-else-if="item.type_of_question == 'identification'"
      :value.sync="answer.answer"
      :readonly="true"
      class="mx-3"
    />
    
    <section v-else-if="item.type_of_question == 'sliders'" class="pb-3 mx-3">
      <Sliders 
        :max="item.assessment_answer.choices.length-1"
        :labels="getSliderLabels"
        :answer="answer.answer"
        :readonly="true"
      />
    </section>

    <section v-else-if="item.type_of_question == 'ordering' && answer.answer" class="pb-3 mx-3">
      <FormLabel :label="'Answer:'" />
      <v-card outlined v-for="(item, i) in answer.answer.split(',')" :key="i"
        class="d-flex flex-row my-2 pa-2 custom-border">
        <div class="roboto f14 fw500 secondary-1--text">
          {{ getOption(item) }}
        </div>
      </v-card>
    </section>

    <v-container>
      <v-container readonly v-if="item.type_of_question == 'essay' && answer.answer" v-html="answer.answer ? $dompurifier(answer.answer.replace(/(\r\n|\n|\r)/g, '<br>')) : ''" class="essay-answer rounded-lg" outlined></v-container>
    </v-container>
    <section v-if="item.require_support_answer" class="mx-3">
      <FormLabel :label="item.type_of_question === 'justified_multiple_choice' ? `Justification:` : 'Modified Statement:'" />
      <FormTextAreaOptional
        :value.sync="answer.support_answer"
        placeholder="Support your answer"
        :readonly="readonly"
      />
    </section>
    <v-container v-if="item.require_file_attachment">
        <Document :file="answer.file" />
        <!-- <v-btn
            :href="answer.file" color="primary poppins" class="btn btn-success text-capitalize" download>Download</v-btn> -->
    </v-container>
    <section>
      <v-sheet class="mx-3 mt-3 pa-2 rounded" color="dark-1" v-if="['multiple_choice', 'true_or_false', 'ordering'].includes(item.type_of_question) && assessment_question.assessment_answer.choices">
        <FormLabel :label="'Correct Answer:'" />
        <div class="my-1" v-if="item.type_of_question === 'ordering'">
          <div class="roboto f14 fw500 secondary-1--text" v-for="(item) in assessment_question.assessment_answer.correct_answer.split(',')" :key="item">
            <v-card outlined class="roboto f12 fw500 secondary-1--text pa-2 my-1">
              {{ getCorrectAnswer(assessment_question.assessment_answer.choices, item) }}
            </v-card>
          </div>
        </div>
        <div v-else>
          <div class="d-flex align-center my-1">
            <v-radio color="success" class="" />
            <div class="roboto f12 fw500 secondary-1--text">
              {{ getCorrectAnswer(assessment_question.assessment_answer.choices, assessment_question.assessment_answer.correct_answer) }}
            </div>
          </div>
          <div class="roboto f14 fw500 secondary-1--text font-italic mt-2">{{
            assessment_question.assessment_answer.choices[assessment_question.assessment_answer.correct_answer.replace('choice_','')].feedback
          }}</div>
        </div>
      </v-sheet>
      <v-sheet class="mx-3 mt-3 pa-2 rounded" color="dark-1" v-if="item.type_of_question == 'identification'">
        <FormLabel :label="'Possible Answers:'" />
        <div class="roboto f12 fw500 secondary-1--text">
          <span v-for="(item, i) in assessment_question.assessment_answer.choices" :key="i">
            {{ item[`choice_${i}`] }}{{i !== assessment_question.assessment_answer.choices.length - 1 ? ',':''}} 
          </span>
        </div>
      </v-sheet>
      <v-container v-if="checked && (['justified_multiple_choice', 'modified_true_or_false', 'essay', 'sliders', 'ordering'].includes(item.type_of_question) || item.require_file_attachment)" class="mt-5">
        <FormLabel :label="`INSTRUCTOR'S FEEDBACK: `" />
        <v-container readonly v-html="answer.question_answer_feedback ? $dompurifier(answer.question_answer_feedback.comment.replace(/(\r\n|\n|\r)/g, '<br>')) : ''" class="essay-answer rounded-lg" outlined></v-container>
      </v-container>
    </section>
  </div>
</template>

<style>
.essay-answer {
  background-color: rgba(130, 130, 130, 20%) !important;
}
</style>

<script>
import Document from "../../../constants/material/Document.vue";
import RadioButtons from "@/components/reusable/assessment/RadioButtons.vue";
import Sliders from "@/components/reusable/assessment/Sliders.vue";

export default {
  props: ['answer' , 'i', 'item', 'errorFile', 'assessment_question', 'checked'],
  components: {
    Document,
    RadioButtons,
    Sliders
  },
  data: () => ({
  }),
  methods: {
    getCorrectAnswer(choices, correct_answer) {
      if(!choices) return ''
      const correctAnswerObj = choices.find(item => item[correct_answer] !== undefined);
      const correctAnswerValue = correctAnswerObj ? correctAnswerObj[correct_answer] : null;
      return correctAnswerValue;
    },

    getOption(_key) {
      let option = this.item.assessment_answer.choices.find(choice => {
        if(choice[_key]) return choice[_key];
      })

      return option[_key]
    }
    
  },
  computed: {
    getSliderLabels(){
      const labels = this.item.assessment_answer.choices.map(choice => {
        const [label, feedback ] = Object.values(choice);
        return label;
      });

      return labels
    }
  }
}
</script>