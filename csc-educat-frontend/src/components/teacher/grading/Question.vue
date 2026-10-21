<template>
  <section id="question">
    <v-sheet class="d-flex justify-space-between border pa-10">
      <v-row>
        <v-col cols="10">
          <div class="d-flex justify-space-between align-center mb-5">
            <div class="poppins fw600 f14 secondary-2--text">Question {{ i+1 }} ({{data.assessment_question.score}} pts):</div>
          </div>
          <div class="question-div roboto f15 secondary-1--text" v-html="data.assessment_question.question ? $dompurifier(data.assessment_question.question) : ''"/>
          
          <section v-if="data.assessment_question.type_of_question == 'essay'">
            <FormLabel :label="'ANSWER:'" />
            <v-sheet 
              v-if="data.answer"
              v-html="data.answer ? $dompurifier(data.answer.replace(/(\r\n|\n|\r)/g, '<br>')) : ''" 
              color="dark-1"
              class="pa-2 poppins f14 fw400 rounded"/>
            <v-sheet 
              v-else
              color="dark-1"
              style="height: 40px"/>
            <div class="question-div" style="height: 100px" v-else></div>
            <section v-if="data.file" >
              <Document :file="data.file" />
              <v-btn
                :href="data.file" color="primary poppins" class="btn btn-success text-capitalize" download>Download</v-btn>
            </section>
            <!-- <div v-else class="poppins fw600 f13 secondary-4--text font-italic"> Nothing attached. </div> -->
          </section>
          <v-sheet 
            v-if="data.assessment_question.type_of_question == 'identification'"
            v-html="data.answer ? $dompurifier(data.answer) : ''" 
            color="dark-1"
            class="pa-2 poppins f14 fw400 rounded"/>
            
          <section v-if="data.assessment_question.assessment_answer.choices">
            <div class="d-flex my-2 mt-n5" v-if="['multiple_choice', 'true_or_false', 'justified_multiple_choice', 'modified_true_or_false'].includes(data.assessment_question.type_of_question)">
              <div class="d-flex flex-column flex-grow-1">
                <v-radio-group
                  dense
                  v-model="data.answer"
                >
                  <Choices
                    v-for="(item, n) in data.assessment_question.assessment_answer.choices"
                    :key="n"
                    :index="n"
                    :item="item"
                  />
                </v-radio-group>
              </div>
            </div>

            <section v-if="data.assessment_question.type_of_question !== 'identification' && (['justified_multiple_choice', 'modified_true_or_false'].includes(data.assessment_question.type_of_question))" class="mb-1">

              <FormTextAreaOptional
                :label="data.assessment_question.type_of_question === 'justified_multiple_choice' ? `Justification:` : 'Modified Statement:'"
                :value.sync="data.support_answer"
                :readonly="true"/>

            </section>

            <section v-if="data.assessment_question.type_of_question === 'sliders'">
              <Sliders 
                :readonly="true"
                :answer="data.answer"
                :labels="getSliderLabels"
                :max="data.assessment_question.assessment_answer.choices.length-1"
              />
            </section>

            <section v-else-if="data.assessment_question.type_of_question == 'ordering'" class="pb-3 mx-3">
              <FormLabel :label="'Answer:'" />
              <v-card outlined v-for="(item, i) in data.answer.split(',')" :key="i"
                class="d-flex flex-row my-2 pa-2 custom-border">
                <FormLabel :label="`${parseInt(item.replace('choice_', ''))+1}`" class="mr-2" />
                <div class="roboto f14 fw500 secondary-1--text">
                  {{ getOption(item) }}
                </div>
              </v-card>
            </section>

            <v-sheet class=" pa-2 rounded" color="dark-1" v-if="['multiple_choice', 'true_or_false', 'justified_multiple_choice', 'modified_true_or_false', 'ordering'].includes(data.assessment_question.type_of_question)">
              <FormLabel :label="'CORRECT ANSWER'"/>
              <div class="my-1" v-if="data.assessment_question.type_of_question === 'ordering'">
                <div class="roboto f14 fw500 secondary-1--text" v-for="(item) in data.assessment_question.assessment_answer.correct_answer.split(',')" :key="item">
                  <v-card outlined class="roboto f12 fw500 secondary-1--text pa-2 my-1">
                    {{ data.assessment_question.assessment_answer.choices[item.replace('choice_','')][item] }}
                  </v-card>
                </div>
              </div>
              <div class="d-flex align-center my-1" v-else>
                <v-radio color="success" class="" />
                <div class="roboto f14 fw500 secondary-1--text">{{
                  data.assessment_question.assessment_answer.choices[data.assessment_question.assessment_answer.correct_answer.replace('choice_','')][data.assessment_question.assessment_answer.correct_answer]
                }}</div>
              </div>
            </v-sheet>

          </section>
          <FormLabel v-else-if="data.assessment_question.type_of_question !== 'essay'" :label="'There were no choices for this question'"/>
          <!-- Instructor Feedback -->
          <section v-if="['identification', 'essay', 'justified_multiple_choice', 'modified_true_or_false', 'sliders', 'ordering'].includes(data.assessment_question.type_of_question)" class="mt-5">
            <FormTextAreaOptional
              :label="'ENTER YOUR FEEDBACK HERE:'"
              :value.sync="comment"/>
          </section>
        </v-col>
        <v-col>
          <div class="justify-start d-flex flex-row align-center">
            <div class="poppins fw600 f14 secondary-2--text mr-1">Score: </div>
            <div class="d-flex flex-row">
              <input 
                type="number"
                min="0"
                :max="data.assessment_question.score"
                class="input-sm mx-auto text-center" 
                style="width: 80px" 
                :id="data.id"
                required
                v-on:input="data.score > parseFloat(data.assessment_question.score) ? data.score = parseFloat(data.assessment_question.score) : data.score"
                v-model="data.score"
              />
            </div>
          </div>
        </v-col>
      </v-row>
    </v-sheet>
    
    <v-btn
      absolute
      fab
      fixed
      bottom
      right
      class="mb-15 mr-5"
      color="primary" 
      elevation="2"
      @click="$emit('save')"
      :loading="loading"
    >
      <v-icon>mdi-content-save </v-icon>
    </v-btn>
    <v-divider class="my-1" />
  </section>
</template>

<script>
import Choices from "./Choices.vue";
import Document from "../../../constants/material/Document.vue";
import Sliders from "@/components/teacher/questiontypes/Sliders.vue";
import FormTextAreaOptional from "@/components/reusable/forms/FormTextAreaOptional.vue";

export default {
  components: {
    Choices,
    Document,
    Sliders
},
  props: ["i", "data", "loading"],
  data: () => ({
    choices: ["Answer 1", "Answer 2", "Answer 3", "Answer 4"],
    justifyText: "Justification:"
  }),
  methods: {
    getOption(_key) {
      let option = this.data.assessment_question.assessment_answer.choices.find(choice => {
        if(choice[_key]) return choice[_key];
      })

      return option[_key]
    }
  },
  computed: {
    comment: {
      get() {
        return  this.data.question_answer_feedback ? this.data.question_answer_feedback.comment : this.data.question_answer_feedback = null
      },
      set(newValue) {
        this.data.question_answer_feedback ? this.data.question_answer_feedback.comment = newValue : this.data.question_answer_feedback = { comment: newValue}
      }
    },

    getSliderLabels() {
      const labels = this.data.assessment_question.assessment_answer.choices.map(choice => {
        const [label, feedback ] = Object.values(choice);
        return label;
      });

      return labels
    },
  }
};
</script>

<style>
  .question-div {
    white-space: normal !important;
    word-wrap: break-word;
  }
</style>