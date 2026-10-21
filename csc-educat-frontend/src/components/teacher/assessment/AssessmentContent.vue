<template>
  <section>
    <v-tabs
      v-model="tab"
      background-color="transparent"
      slider-color="primary"
      active-class="white"
      show-arrows
    >
      <v-tab
        v-for="item in items"
        :key="item"
        class="roboto text-capitalize f14 fw600"
        active-class="primary--text"
      >
        {{ item }}
        <v-btn
          v-if="item === 'Questions' && !$isReadOnly.isReadOnly(status, permission)"
          icon
          color="primary"
          @click.stop="addQuestion('tab')"
          :loading="tabloading"
        >
          <v-icon>mdi-plus-box</v-icon>
        </v-btn>
      </v-tab>
    </v-tabs>
    <v-divider />
    <v-tabs-items v-model="tab" style="background-color: transparent" class="py-2">
      <v-tab-item v-for="item in items" :key="item" style="background-color: transparent">
        
        <Info v-if="item === 'Settings'" :item="assessment" :status="status" :permission="permission" />
        <QuestionBuilder v-if="item === 'Questions'" :id="assessment.id" :type_of_assessment="assessment.type_of_assessment" :assessment="assessment_questions" @addQuestion="addQuestion" :loading="contentloading" :status="status" :permission="permission" :course_id="assessment.course_id"/>
      </v-tab-item>
    </v-tabs-items>
  </section>
</template>

<script>
import { mapActions, mapMutations } from "vuex";
import Info from "./AssessmentInfo.vue";
import QuestionBuilder from "./QuestionBuilder.vue";
import tabs from '../../../constants/tabs/assessment/assessmentcontent'

export default {
  props: ["assessment", "assessment_questions", "status", "permission"],
  components: {
    Info,
    QuestionBuilder,
  },
  data: () => ({
    tab: null,
    items: tabs,
    tabloading: false,
    contentloading: false,
  }),
  methods: {
    ...mapActions("instructor", ["addQuestionAction"]),
    ...mapMutations(["alertMutation"]),
    
    addQuestion(type) {
      if (this.tab) {
        if(type === 'tab') {
          this.tabloading = true
        } else {
          this.contentloading =  true
        }
        this.addQuestionAction({
          assessment_id: this.assessment.id,
          course_id: this.assessment.course_id,
        }).then(() => {
          this.tabloading = false
          this.contentloading = false
        }).catch(() => {
          this.tabloading = false
          this.contentloading = false

          this.alertMutation({
              show: true,
              text: 'Something went wrong.',
              type: "error"
          })
        });
      }
    },
  },
};
</script>
