<template>
  <section id="question">
    <v-form ref="form">
      <section id="choices">
        <div class="col-lg-6 row">
          <div class="col-12 col-sm-6">
            <FormLabel
              :label="'TYPE *'"
            />
            <v-select
              dense
              outlined
              hide-details
              :items="assessment_question_types"
              v-model="item.type_of_question"
              v-on:input="selectType($event)"
              class="general-custom-field secondary-1--text f14 fw600 roboto"
            />
          </div>
          <div class="col-12 col-sm-6">
            <FormLabel
              :label="'SCORE'"
            />
            <v-text-field
              dense
              outlined
              v-model="item.score"
              class="general-custom-field secondary-1--text f14 fw600 poppins"
              :rules="rules"
            >
              <template v-slot:append>
                <div class="pt-1 f12 secondary--text fw400">pts</div>
              </template>
            </v-text-field>
          </div>
        </div>
        <div class="col-lg-8" style="margin-top: -30px">
          <FormLabel
            :label="'ENTER YOUR QUESTION HERE'"
          />
          <RichTextEditor v-if="showMce"
            :rules="rules"
            :value="item.question"
            @setValue="(e) => { item.question = e }"
          />
          <circular v-else />
        </div>
        <div class="col-lg-6" v-if="item.type_of_question">
          <FormLabel
            :label="note()"
            class="text-uppercase"
          />
          <div class="d-flex align-end">
            <v-btn
              v-if="item.type_of_question == 'essay'"
              text
              small
              color="primary"
              class="text-capitalize roboto f14 fw500 no-space mb-1"
              @click="() => {
                rubricID = item.rubric_id
                importDialog()
              }"
            >
              <v-icon left>mdi-file-import-outline</v-icon>
              Import Rubric
            </v-btn>
            <v-btn
              text
              v-if="['multiple_choice', 'identification', 'justified_multiple_choice', 'ordering'].includes(item.type_of_question)"
              color="primary"
              class="text-capitalize roboto f14 fw600 no-space mb-0 pb-0"
              @click="addChoices"
            >
              <v-icon left>mdi-plus</v-icon>
              {{
                item.type_of_question == "identification"
                  ? "Add Possible Answers"
                  : "Add Choices"
              }}
            </v-btn>
            <div
              class="mx-3 d-flex align-end"
              v-if="
                item.type_of_question == 'justified_multiple_choice' ||
                item.type_of_question == 'modified_true_or_false' ||
                item.type_of_question == 'essay'
              "
            >
              <v-switch
                hide-details
                dense
                class="mb-1"
                inset
                color="success"
                v-model="item.require_file_attachment"
              />
              <div class="roboto f14 fw500 primary--text mb-2">Require upload file</div>
            </div>
          </div>
  
          <div v-if="item.type_of_question === 'sliders'">
            <FormLabel
              :label="'RANGE:'"
              class="text-uppercase"
            />
            <section class="d-flex flex-wrap mb-2 align-center">
              <input
                class="input-sm mr-1"
                type="number"
                value="1"
                disabled
              />
              -
              <input
                class="input-sm ml-1"
                type="number"
                v-model="sliderRange"
                v-on:change="sliderRangeChange"
              />
            </section>
          </div>
          <div v-if="['sliders', 'ordering'].includes(item.type_of_question)">
            <section class="d-flex flex-row align-center" 
              v-for="(choice, i) in item.answer.choices" :key="i">
                <FormLabel
                  :label="`${ i + 1 }`"/>
                <v-text-field
                  outlined
                  dense
                  solo
                  hide-details
                  flat
                  required
                  v-model="choice[`choice_${i}`]"
                  class="f12 general-custom-field secondary-1--text fw600 roboto mx-2 my-1"
                />
                <section v-if="item.answer.choices.length > 1">
                  <v-btn icon @click="removeOption(i)">
                    <v-icon>
                      mdi-close-circle
                    </v-icon>
                  </v-btn>
                </section>
            </section>
          </div>
          
          <v-radio-group
            dense
            v-if="!['essay', 'sliders', 'ordering'].includes(item.type_of_question)"
            v-model="item.answer.correct_answer"
            mandatory
            hide-details
            class="pt-0">
            <div
              class="d-flex align-center my-1"
              v-for="(choice, i) in item.answer.choices"
              :key="i"
            >
              <v-radio
                v-if="item.type_of_question != 'identification' && item.type_of_question != 'checkbox'"
                color="success"
                class="mt-2"
                :value="`choice_${i}`"
                hide-details
              />
              <v-checkbox
                v-else-if="item.type_of_question == 'checkbox'"
                color="success"
                class="mt-3"
                :value="`choice_${i}`"
                hide-details
              />
              <v-icon v-else color="success">mdi-check-circle</v-icon>
              <v-text-field
                :readonly="item.type_of_question == 'true_or_false'"
                outlined
                dense
                solo
                hide-details
                :placeholder="
                  item.type_of_question == 'identification'
                    ? 'Possible answer'
                    : `Type your answer ${i + 1}`
                "
                flat
                required
                v-model="choice[`choice_${i}`]"
                v-on:change="handleAnswer($event, `choice_${i}`, i)"
                class="general-custom-field secondary-1--text fw600 roboto mx-2 my-1 f12"
              />
              <v-text-field
                v-if="item.type_of_question != 'checkbox' && item.type_of_question != 'dropdown' && item.type_of_question != 'identification' && item.type_of_question != 'justified_multiple_choice'"
                outlined
                dense
                solo
                hide-details
                placeholder="Enter Feedback"
                flat
                v-model="choice.feedback"
                v-on:change="handleAnswer($event, 'feedback', i)"
                class="general-custom-field secondary-1--text f14 fw600 roboto mx-2 my-1"
              />
              <section v-if="item.answer.choices.length > 1">
                <v-btn icon @click="removeOption(i)">
                  <v-icon>
                    mdi-close-circle
                  </v-icon>
                </v-btn>
              </section>
            </div>
          </v-radio-group>
          
          <div v-if="item.type_of_question === 'linear_scale'">
            <section class="d-flex flex-wrap mb-2">
              <select
                class="input-sm mr-2"
              >
                <option
                  v-for="(value, i) in min"
                  :key="i"
                  :value="value"
                  class="roboto fw500 f12 secondary-3--text"
                >
                  {{ value }}
                </option>
              </select>
              -
              <select
                class="input-sm ml-2"
              >
                <option
                  v-for="(value, i) in max"
                  :key="i"
                  :value="value"
                  class="roboto fw500 f12 secondary-3--text"
                >
                  {{ value }}
                </option>
              </select>
            </section>
            <section class="d-flex flex-row align-center justify-space-between">
              <label class="poppins fw500 f12 secondary-2--text">0</label>
              <v-text-field
                outlined
                dense
                solo
                hide-details
                flat
                required
                class="f12 general-custom-field secondary-1--text fw600 roboto ml-4 mr-2 my-1"
              />
            </section>
            <section class="d-flex flex-row align-center justify-space-between">
              <label class="poppins fw500 f12 secondary-2--text">10</label>
              <v-text-field
                outlined
                dense
                solo
                hide-details
                flat
                required
                class="f12 general-custom-field secondary-1--text fw600 roboto mx-2 my-1"
              />
            </section>
          </div>
        </div>
        <v-skeleton-loader
          v-if="loadingRubric"
          type="image"
          class="mb-3 mx-3"
        ></v-skeleton-loader>
        <section v-if="rubricID">
          <div class="col-12 mr-2" v-if="item.type_of_question == 'essay' && item.rubric_id && previewData == null" style="margin-top: -20px">
            <PreviewRubric :rubric=null :mode="1" :rubric_id="item.rubric_id"/>
          </div>
          <div class="col-12 mr-2" v-else-if="item.type_of_question == 'essay' && previewData" style="margin-top: -20px">
            <PreviewRubric :rubric="previewData"  :rubric_id="item.rubric_id" :mode="2"/>
          </div>
        </section>
        <div class="col-12" style="margin-top: -20px">
          <v-btn
            text
            color="primary"
            class="roboto f14 fw600 no-space"
            @click="save"
            :loading="updateLoading"
            v-if="!successSaved"
            >Save Changes</v-btn
          >
          <v-btn text color="primary" class="roboto f14 fw600 no-space" v-else>
            <v-icon color="success" left>mdi-check-circle</v-icon>
            Saved</v-btn
          >
          <v-btn @click="cancel" text color="secondary" class="roboto f14 fw600 no-space"
            >Cancel</v-btn
          >
        </div>
        <ImportRubricDialog :dialog="dialog" @getRubricID="(id) => getRubricID(id)" @close="dialogClose()" :data="rubrics" :loading="loadingRubrics" :rubricID="rubricID"/>
      </section>
    </v-form>
  </section>
</template>
  
<script>
import { mapActions, mapMutations, mapState } from "vuex";
import { assessment_question_types } from "@/shared/questionTypes.js";
import Editor from "@tinymce/tinymce-vue";
import ImportRubricDialog from "@/components/teacher/assessment/ImportRubricDialog";
import PreviewRubric from "@/components/teacher/rubrics/PreviewRubric.vue";

export default {
  props: ["item", "assessment_type", "updateLoading", "successSaved"],
  components: {
    editor: Editor,
    ImportRubricDialog,
    PreviewRubric,
  },
  data: () => ({
    showMce: true,
    loadingRubric: false,
    loadingRubrics: false,
    dialog: false,
    previewData: null,
    rubricID: null,
    rules: {
      required: value => !!value || 'Required.',
    },
    assessment_question_types,
    min: [ 0, 1 ],
    max: [ 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10 ],
  }),
  mounted() {
    // newly created, default multiple choice
    
    this.$nextTick(() => {
      setTimeout(() => {
        this.showMce = true;
      }, 2000);
    });

    if(!this.item.answer.choices) {
      this.selectType('multiple_choice')
    }

    if(this.item.rubric_id) {
      this.loadingRubric = true
      this.showRubricAction(this.item.rubric_id).then(() => {
        this.loading1 = false
        this.loadingRubric = false
        this.rubricID = this.item.rubric_id
        this.previewData = this.rubrics
      }).catch(() => {
        this.loading1 = false
        this.loadingRubric = false
      });
    }
  },
  computed: {
    ...mapState("instructor", {
      course: (state) => state.course,
      rubrics: (state) => state.rubrics
    }),

    sliderRange: {
      get() {
        return this.item.answer.choices.length
      },
      set() {
      },
    }
  },
  methods: {
    ...mapActions("instructor", [
      "updateQuestionAction",
      "getAssessmentsAction",
      "getRubricsAction",
      "showRubricAction"
    ]),

    ...mapMutations("instructor", ["addChoicesMutation", "inputAnswerMutation"]),

    selectType(e) {
      this.item.answer.choices = null;
      if(["multiple_choice", "justified_multiple_choice", "identification", "dropdown"].includes(this.item.type_of_question)) {
        return (this.item.answer.choices = [
          { choice_0: e == "identification" ? "" : "Type your answer 1", feedback: "" },
          { choice_1: e == "identification" ? "" : "Type your answer 2", feedback: "" },
          { choice_2: e == "identification" ? "" : "Type your answer 3", feedback: "" },
        ]);
      }

      if (["checkbox", "ordering"].includes(this.item.type_of_question) ) {
        return (this.item.answer.choices = [
          { choice_0: "Type your option 1", feedback: "" },
          { choice_1: "Type your option 2", feedback: "" },
          { choice_2: "Type your option 3", feedback: "" },
        ]);
      }

      if (e == "true_or_false" || e == "modified_true_or_false") {
        return (this.item.answer.choices = [
          { choice_0: "TRUE", feedback: "" },
          { choice_1: "FALSE", feedback: "" },
        ]);
      }

      if(e == 'sliders') {
        return (this.item.answer.choices = [
          { choice_0: "Strongly Agree", feedback: "" },
          { choice_1: "Agree", feedback: "" },
          { choice_2: "Neutral", feedback: "" },
          { choice_3: "Disagree", feedback: "" },
          { choice_4: "Strongly Disagree", feedback: "" }
        ])
      }
    },

    sliderRangeChange(event) {
      let _max = event.target.value;

      if(_max == 0) {
        this.selectType('sliders')
      } else if(this.item.answer.choices.length < _max) {
        for (let i = 0; i <= (_max - this.item.answer.choices.length); i++) {
          this.addChoices()
        }
      } else if(this.item.answer.choices.length > _max) {
        this.item.answer.choices = this.item.answer.choices.slice(0, _max)
      }
    },

    addChoices() {
      this.$emit('addChoices', {
        id: this.item.id,
      })
      // this.addChoicesMutation({
      //   assessment_id: this.item.assessment_id,
      //   question_id: this.item.id,
      // });
    },

    getRubricID(id){
      this.rubricID = id

      this.item.rubric_id = id
      this.previewData = this.rubrics
    },

    removeOption(i) {
      let choicesArr = []
      this.item.answer.choices.splice(i, 1);
      this.item.answer.choices.forEach((_choice, i) => {
        let _keys = Object.keys(_choice)
        let _new_choice = {
          [`choice_${i}`]: _choice[_keys[0]].includes('Type your answer') ? `Type your answer ${i+1}` : _choice[_keys[0]],
          feedback: _choice['feedback']
        }
        choicesArr.push(_new_choice)
      })
      this.item.answer.choices = choicesArr
    },

    cancel() {
      this.$emit('cancel')
      // this.getAssessmentsAction({ course_id: this.course.id });
    },
    
    save() {
      if(this.$refs.form.validate()) {
        this.item.rubric_id = this.rubricID

        let question = this.item;
        // let choices = [];
        let answer = question.answer;
        question["correct_answer"] = answer.correct_answer;
        if(question.type_of_question === 'ordering'){
          let _choices = answer.choices.map(item => Object.keys(item)[0]).join(',')
          question["correct_answer"] = _choices
          console.log(_choices)
        }
        question["choices"] = answer.choices;
        question["course_id"] = this.course.id

        this.$emit('updateQuestion', question)
      }
    },

    handleAnswer(text, type, index) {
      // return console.log(type);
      // this.inputAnswerMutation({
      //   assessment_id: this.item.assessment_id,
      //   question_id: this.item.id,
      //   type: type,
      //   text: text,
      //   index: index,
      // });

      this.$emit('inputAnswer', {
        assessment_id: this.item.assessment_id,
        question_id: this.item.id,
        type: type,
        text: text,
        index: index,
      })
    },

    note() {
      let _note = ""

      switch(this.item.type_of_question) {
        case 'multiple_choice' : 
          _note = "Enter multiple choices, then select the correct answer";
          break;
        case 'justified_multiple_choice' :
          _note = "Enter multiple choices, then select the correct answer. The Student will type their explanation on a separate text box.";
          break;
        case 'modified_true_or_false':
          _note = "Enter True or false. Student will type the correct answer on a separate text box.";
          break;
        case 'true_or_false': 
          _note = "Enter True or false, then select the correct answer";
          break;
        case 'essay' :
          _note = "ESSAY. Student will type their answer on a text box.";
          break;
        case 'dropdown':
        case 'checkbox': 
          _note = "Enter choices."
          break;
        case 'linear_scale': 
          _note = "Enter minimum and maximum values for scale, then their labels"
          break;
        case 'multiple_choice_grid':
        case 'checkbox_grid':
          _note = "Add questions in rows and answer choices in columns"
          break;
        case 'sliders':
          _note = "Enter range values for slider, then their labels"
          break;
        case 'ordering':
          _note = "Enter options to be rearranged. The arrangement below are the correct arrangement: "
          break;
        default: "";
      }

      return _note;
    },

    importDialog() {
      this.dialog = true
      this.loadingRubrics = true
      this.getRubricsAction().then(() => {
        this.loadingRubrics = false;
      }).catch(() => {
        this.loadingRubrics = false;
      });
    },

    dialogClose() {
      this.dialog = false;
      this.previewData = this.rubrics;
    },
  },
};
</script>
