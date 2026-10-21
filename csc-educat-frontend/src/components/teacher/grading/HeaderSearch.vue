<template>
  <v-sheet class="custom-border border mb-5">
    <!-- <v-row class="pa-10"> -->
      <!-- <v-col cols="12" lg="3">
        <label class="f13 poppins fw500 secondary-1--text">STUDENT</label>
        <v-select
          hide-details
          outlined
          dense
          class="general-custom-field f14 roboto fw500"
          :items="user_list"
          item-text="text"
          item-value="value"
          :value="user_id"
          @change="displayStudent"
          eager
        />
      </v-col> -->
      <!-- <v-col cols="12" lg="3" class="ml-auto">
        <label class="f13 poppins fw500 secondary-1--text">STATUS</label>
        <v-select
          hide-details
          outlined
          dense
          class="general-custom-field f14 roboto fw500"
          :value="status"
          :items="items"
          item-text="status"
          item-value="value"
          @change="$emit('updateStatus', $event)"
        />
      </v-col> -->
      <!-- <v-col cols="12" lg="3" class="d-flex align-center justify-end">
        <div class="roboto f14 secondary-1--text mr-5 fw500">
          {{ getNextStudent() }}
        </div>
        <v-tooltip bottom>
          <template v-slot:activator="{ on, attrs }">
            <v-btn v-bind="attrs"
              v-on="on"
              color="primary-1" 
              fab 
              elevation="0" 
              @click="nextStudent">
              <v-icon color="primary" large>mdi-chevron-right</v-icon>
            </v-btn>
          </template>
          <span>Next user</span>
        </v-tooltip>
      </v-col> -->
    <!-- </v-row>
    <v-divider /> -->
    <div class="d-flex flex-wrap align-center justify-space-between pa-10">
      <div class="d-flex flex-wrap">
        <div class="mr-16 d-flex align-center">
          <h1 class="roboto fw600">
            {{`${data.score.user.first_name} ${data.score.user.last_name}`}}
          </h1>
        </div>
        <div class="d-flex flex-wrap align-center">
          <div class="my-2" :class="($vuetify.breakpoint.mdAndDown || $vuetify.breakpoint.mobile) ? 'mr-10' : ''">
            <div class="poppins f13 secondary-2--text fw500">DUE DATE</div>
            <div class="roboto f15 fw500" v-if="data.due_date">{{JSON.parse(data.due_date).end ? $dateFormat.mmDDyy(JSON.parse(data.due_date).end) : '-'}}</div>
            <div class="roboto f15 fw500" v-else>-</div>
          </div>
          <v-divider vertical class="mx-5" v-if="!$vuetify.breakpoint.mdAndDown || !$vuetify.breakpoint.mobile" />
          <div class="my-2" :class="($vuetify.breakpoint.mdAndDown || $vuetify.breakpoint.mobile) ? 'mr-10' : ''">
            <div class="poppins f13 secondary-2--text fw500">DATE SUBMITTED</div>
            <div class="roboto f15 fw500">{{$dateFormat.mmDDyy(data.score.created_at)}}</div>
          </div>
          <v-divider vertical class="mx-5"  v-if="!$vuetify.breakpoint.mdAndDown || !$vuetify.breakpoint.mobile" />
          <div class="my-2" :class="($vuetify.breakpoint.mdAndDown || $vuetify.breakpoint.mobile) ? 'mr-10' : ''">
            <div class="poppins f13 secondary-2--text fw500">TOTAL SCORE</div>
            <!-- <div class="roboto f15 fw500" v-if="data.question_answers.every(question_answer => question_answer.assessment_question.score !== null)">
              {{ data.score.score % data.score.score === 0 ? parseInt(data.score.score) : data.score.score }} / {{ data.question_answers.reduce((prev, current) => prev + parseFloat(current.assessment_question.score), 0) }}<span class="secondary--text ml-1 fw400">pts</span>
            </div>
            <div v-else>
              -
            </div> -->

            <div class="roboto f15 fw500" v-if="data.question_answers.every(question_answer => question_answer.assessment_question.score !== null)">
              {{ data.score.score % data.score.score === 0 ? parseInt(data.score.score) : data.score.score }} / {{ this.sum_score }}<span class="secondary--text ml-1 fw400">pts</span>
            </div>
            <div v-else>
              -
            </div>
            <!-- {{this.sum_score}} -->
            <!-- {{ this.user_list.users.findIndex(f=>f.user_id == this.data.score.user_id)}} -->
          </div>
          <v-divider vertical class="mx-5"  v-if="!$vuetify.breakpoint.mdAndDown || !$vuetify.breakpoint.mobile" />
          <div class="my-2" :class="($vuetify.breakpoint.mdAndDown || $vuetify.breakpoint.mobile) ? 'mr-10' : ''">
            <div class="poppins f13 secondary-2--text fw500">PERCENTAGE</div>
            <div class="roboto f15 fw500">
              {{ data.score.assessment_score_percentage }} %
            </div>
          </div>
        </div>
      </div>
      <div class="ml-auto">
        <div class="poppins f13 secondary-2--text fw500">STATUS</div>
        <v-select
          hide-details
          outlined
          dense
          class="general-custom-field f13 roboto fw500"
          :value="status"
          :items="items"
          item-text="status"
          item-value="value"
          @change="$emit('updateStatus', $event)"
        />
      </div>
      <!-- <div class="">
        <v-btn icon large>
          <v-icon>mdi-history</v-icon>
        </v-btn>
      </div> -->
    </div>
  </v-sheet>
</template>

<script>
export default {
  props: ['data', 'user_list', 'sum_score', 'status' ],
  data: () => ({
    items: [
      { status: 'Pending', value: 0 },
      { status: 'Completed', value: 1 },
    ],
    selectedUser: '',
    currentIndex: null,
    user_id: null
  }),
  created(){
    this.user_id = this.$route.params.user_id
  },
  mounted(){
    this.user_id = this.$route.params.user_id
  },
  methods: {
    displayStudent(e){
      this.$router.push({ name: 'Instructor Grading Assessment User', params: { id: this.$route.params.id, user_id: e } })

      this.$emit('displayStudentAssessment')
    },
    getNextStudent(){
      let assessment = this.user_list.find(e=> e.id == this.data.score.assessment_id)
      this.currentIndex = assessment.users.findIndex(f=>f.user_id == this.data.score.user_id)
      let user_length = assessment.users.length
      if(user_length - this.currentIndex === 1) {
        return assessment.users[0].user
      } else {
        return assessment.users[this.currentIndex + 1].user
      }
    },
    nextStudent(){
      let assessment = this.user_list.find(e=> e.id == this.data.score.assessment_id)
      let user_length = assessment.users.length
      if(user_length - this.currentIndex === 1) {
        this.$router.push({ name: 'Instructor Grading Assessment User', params: { id: this.data.score.assessment_id, user_id: assessment.users[0].user_id } })
      } else {
        this.$router.push({ name: 'Instructor Grading Assessment User', params: { id: this.data.score.assessment_id, user_id: assessment.users[this.currentIndex+1].user_id } })
      }
    },
    select(e){
      console.log(e.id)
    }
  },
}
</script>

<style></style>