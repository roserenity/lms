<template>
  <div class="mb-7 mx-1">
    <RadioButtons
      v-if="!['identification', 'essay', 'sliders', 'ordering'].includes(item.type_of_question)"
      :answer="answers[i].answer"
      :choices="item.assessment_answer.choices"
      @setAnswer="(e) => { answers[i].answer = e}"
    />
    <FormTextFieldOptional
      v-else-if="item.type_of_question == 'identification'"
      :value.sync="answers[i].answer"
      placeholder="Type your answer here"
    />
    <section v-else-if="item.type_of_question == 'sliders'" class="pb-3">
      <Sliders 
        :max="item.assessment_answer.choices.length-1"
        :labels="getSliderLabels"
        :answer="answers[i].answer"
        @setAnswer="(e) => { answers[i].answer = e }"
      />
    </section>
    <section v-else-if="item.type_of_question == 'ordering'" class="pb-3">
      <draggable
          v-model="item.assessment_answer.choices"
          ghost-class="ghost"
          class="sortable"
          @end="sortOptions()"
        >
        <transition-group type="transition" name="flip-list" class="ma-0 pa-0">
          <v-card outlined v-for="(choice, i) in item.assessment_answer.choices" :key="i"
            class="d-flex flex-row my-2 pa-2 custom-border">
            <div class="roboto f14 fw500 secondary-1--text">{{choice[`${Object.keys(choice)[0]}`]}}</div>
          </v-card>
        </transition-group>
      </draggable>
    </section>
    <section v-if="item.type_of_question == 'essay'" class="mx-3">
      <FormTextAreaOptional
        :value.sync="answers[i].answer"
        placeholder="Type your answer here"
      />
    </section>
    <FormLabel v-if="item.type_of_question==='justified_multiple_choice'" :label="'Justify your answer:'" />
    <FormLabel v-if="item.type_of_question==='modified_true_or_false'" :label="'If false, modify the statement to make it true:'" />
    <FormTextAreaOptional
      v-if="answers[i].is_require_support_answer"
      :value.sync="answers[i].support_answer"
      placeholder="Support your answer"
    />
    <div
      v-if="item.require_file_attachment"
      class="bulk-border ma-3"
      @dragover="dragover"
      @dragleave="dragleave"
      @drop="drop($event, i)"
    >
      <div class="d-flex align-center justify-center">
        <div class="d-flex flex-column  align-center my-3">
          <v-icon color="rgba(63, 93, 208, 0.12)" size="32"
          >mdi-cloud-upload-outline</v-icon
          >
          <div class="secondary--text fw400">
          <a
              href="#"
              @click="$emit('findFile', i)"
              class="primary--text text-decoration-none"
              >Browse</a
          >
          or drag file here 
          </div>
          <div class="secondary--text f12">( .jpg, .jpeg, .png, .doc, .docx, .pdf, .pptx, .ppt )</div>
        </div>
      </div>
    </div>
    <div class="ma-7" v-if="item.require_file_attachment">

      <div class="d-flex align-center my-5" v-if="answers[i].file">
        <div class="secondary-3--text f13 mx-2 d-flex flex-column"> 
          {{`${answers[i].file.name}`}}
          <v-progress-linear
            color="primary"
            rounded
            value="100"
            :indeterminate="answers[i].loading" 
            v-if="answers[i].loading"
          ></v-progress-linear>
          <span class="f12 float-right">{{ `${answers[i].file.size} kb` }}</span>
        </div>
        <v-icon v-if="!answers[i].loading && answers[i].errorMessages == ''" color="success">mdi-check-circle-outline</v-icon>
      </div>
    </div>
    
    <div class="ma-7 d-flex align-center" v-if="answers[i].errorMessages">
      <v-icon color="danger-1">mdi-alert-circle-outline</v-icon>
      <div class="danger-1--text f14 roboto fw500 mx-2">
        {{answers[i].errorMessages}}
      </div>
    </div>
  </div>
</template>

<script>
import draggable from "vuedraggable";
import RadioButtons from "@/components/reusable/assessment/RadioButtons.vue";
import Sliders from "@/components/reusable/assessment/Sliders.vue";

export default {
  props: ['answers' , 'i', 'item', 'errorFile'],
  components: {
    draggable,
    RadioButtons,
    Sliders
  },
  data: () => ({
    mce_key: `${process.env.VUE_APP_TINYMCE_KEY}`
  }),
  created(){
    if(this.item.type_of_question === 'ordering' && !this.answers[this.i].answer) {
      this.sortOptions()
    }
  },
  methods: {
    dragover(e) {
      e.preventDefault();
      e.currentTarget.classList.add("bulk-border-drag");
    },
    dragleave(e) {
      e.preventDefault();
      e.currentTarget.classList.remove("bulk-border-drag");
    },

    drop(e, i) { 
      e.preventDefault();
      this.$emit('getDropFiles', { index: i, file: e.dataTransfer.files });
      e.currentTarget.classList.remove("bulk-border-drag");
    },

    sortOptions() {
      const sorted = this.item.assessment_answer.choices.map(choice => {
        const [key, value ] = Object.keys(choice);
        return key;
      });

      this.answers[this.i].answer = sorted.join(',')
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