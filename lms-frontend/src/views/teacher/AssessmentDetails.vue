<template>
  <section id="assessment-details">
    <breadcrumbs :items="links"/>
    <v-tabs class="tabs mt-5" color="success" v-model="tab" background-color="transparent">
      <v-tab v-for="item in items" :key="item" active-class="active" class="item nunito text-capitalize">
        {{item}}
      </v-tab>
    </v-tabs>
    <v-divider/>
    <v-tabs-items v-model="tab">
      <v-tab-item v-for="item in items" :key="item" >
        <AssessmentBuilder v-if="item === 'Assessment Details'"/>
        <QuestionBuilder v-if="item === 'Question Builder'"/>
        <Questionnaire v-if="item === 'Questionnaire'"/>
        <QuestionBankTable v-if="item === 'Question Bank'"/>
      </v-tab-item>
    </v-tabs-items>
  </section>
</template>

<script>
import AssessmentBuilder from '../../components/teacher/assessment/AssessmentBuilder.vue'
import QuestionBuilder from '../../components/teacher/assessment/QuestionBuilder.vue'
import Questionnaire from '../../components/teacher/assessment/Questionnaire.vue'
import QuestionBankTable from '../../components/teacher/assessment/QuestionBankTable'

export default {
   components: {
    AssessmentBuilder,
    QuestionBuilder,
    Questionnaire,
    QuestionBankTable
  },
  data: () => ({
    tab: null,
    dialog: false,
    items: ['Assessment Details', 'Question Builder', 'Questionnaire', 'Question Bank'],
    links: []
  }),
  mounted() {
    this.links = [
       {
        text: 'BACK',
        to: { name: this.$store.state.route.from.name },
        disabled: false
      },
      {
        text: 'Assessment 1',
        to: '',
        disabled: true
      },
    ]
  },
}
</script>