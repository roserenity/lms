<template>
  <section id="grading-assessment py-2">
    <section class="d-flex flex-wrap align-center justify-space-between">
      <SearchBar 
        :placeholder="'Search student name'" 
        :value.sync="search" 
        @clear="clearSearch"
        @search="onSearch"
        class="mb-5"/>
      <section class="d-flex flex-row ml-auto">
        <v-btn
            outlined
            color="primary"
            class="poppins f12 fw500 text-capitalize mx-1 mb-5"
            @click="markAsCompleted"
            dense
            :loading="loading1"
        >
            Mark as Completed
        </v-btn>
        <v-btn
            outlined
            color="primary"
            class="poppins f12 fw500 text-capitalize mx-1 mb-5"
            @click="markAsPending"
            dense
            :loading="loading2"
        >
            Mark as Pending
        </v-btn>
    </section>
    </section>
    

    <v-data-table
      :headers="grading_assessment_student_tbl"
      :items="gradingAssessment"
      class="text--center poppins custom-border"
      hide-default-footer
      :items-per-page.sync="itemsPerPage"
      :loading="loading">
      <template v-slot:header.select="{ item }" >
        <v-checkbox  class="mt-0" v-model="selectAll" hide-details/>
      </template>
      <template v-slot:item.select="{ item }" >
        <v-checkbox  class="mt-0" v-model="selected" :value="item.id" hide-details/>
      </template>
      <template v-slot:item.checked="{ item }">
        <span :class="item.checked == 'Completed' ? 'green--text' : 'grey--text'">{{ item.checked }}</span>
      </template>
      <template v-slot:item.date="{ item }">
        {{$dateFormat.mmDDyy(item.created_at)}}
      </template>
      <template v-slot:item.time="{ item }">
        {{$dateFormat.hhMM(item.created_at)}}
      </template>
      <template v-slot:item.action="{ item }">
        <v-btn
          text
          exact
          class="primary--text text-capitalize roboto f14"
          :to="{
            name: 'Instructor Grading Assessment User',
            params: { id: item.assessment_id, user_assessment_id: item.id, user_id: item.user.id },
          }"
          icon
        >
          <v-icon small>mdi-pencil</v-icon>
        </v-btn>
      </template>
    </v-data-table>

    <FormPagination 
        :pageCount="pageCount" 
        :page="page"
        :paginate="paginationPaginate"
        @page="onPageChange" 
        @paginate="onPaginateChange"/>

  </section>
</template>
  
<script>
import { mapActions, mapState, mapMutations } from 'vuex'
import searchAndPaginateMixin from '@/mixins/searchAndPaginateMixin';
import { grading_assessment_student_tbl } from '@/constants/tblheaders/grading';

export default {
  mixins: [searchAndPaginateMixin],
  props: ['submitted_to', 'submitted_from'],
  data: () => ({
    grading_assessment_student_tbl,
    pageCount: 1,
    loading: false,
    loading1: false,
    loading2: false,
    selected: [],
    selectAll: false,
    course_title: '',
    assessment_title: ''
  }),
  methods: {
    ...mapActions('instructor', ['getGradingStudentList', 'updateGradingAssessmentStatusAction']),

    ...mapMutations(['alertMutation']),

    getData() {
      if(!this.loading) {
        this.selectAll = false
        this.selected = []

        this.loading = true
        this.$emit('setLoading', true)
        this.getGradingStudentList({id: this.$route.params.id, search: this.search, page: this.page, paginate: Number(this.paginate), submitted_from: this.submitted_from, submitted_to: this.submitted_to}).then(res => {
          this.loading = false
          this.$emit('setLoading', false)
          this.page = res.assessment_scores.current_page
          this.pageCount = res.assessment_scores.last_page
          this.paginate = String(res.assessment_scores.per_page)
          this.$emit('setTotal', res.assessment_scores.total)
          this.$emit('setCourseTitle', res.course)
          this.$emit('setAssessmentTitle', res.assessment.title)
        }).catch(()=>{
          this.loading = false
          this.$emit('setLoading', false)
        })
      }
    },

    markAsCompleted(){
      this.loading1 = true
      this.updateGradingAssessmentStatusAction({
        id: this.$route.params.id,
        assessment_scores_id: this.selected,
        status: true
      }).then(() => {
        this.alertMutation({
          show: true,
          text: `Successfully updated grading status`,
          type: "success"
        })
        this.getData()
        this.loading1 = false
      }).catch(() => {
        this.alertMutation({
          show: true,
          text: `Something went wrong.`,
          type: "error"
        })
        this.loading1 = false
      })
    },

    markAsPending(){
      this.loading2 = true
      this.updateGradingAssessmentStatusAction({
        id: this.$route.params.id,
        assessment_scores_id: this.selected,
        status: false
      }).then(() => {
        this.alertMutation({
          show: true,
          text: `Successfully updated grading status`,
          type: "success"
        })
        this.getData()
        this.loading2 = false
      }).catch(() => {
        this.alertMutation({
          show: true,
          text: `Something went wrong.`,
          type: "error"
        })
        this.loading2 = false
      })
    }
  },
  mounted(){
    this.getData()
  },
  watch: {
    selectAll(val) {
      if(val) {
        let _selected = []

        this.gradingAssessment.forEach(item => {
          if(!this.selected.includes(item)) {
            _selected.push(item.id)
          }
        })

        this.selected = [..._selected]

      } else if (!val && this.gradingAssessment.length === this.selected.length) {
        this.selected = [] 
      }
    },

    selected(val){
      if(val.length !== this.gradingAssessment.length) {
        this.selectAll = false
      } else if (val.length === this.gradingAssessment.length && this.gradingAssessment.length !== 0) {
        this.selectAll = true
      }
    }
  },
  computed:{
    ...mapState('instructor', {
      grading: (state) => state.grading,
      gradingAssessment(state){
        let items = []
        state.gradingUsers.forEach(item => {
          items.push(
            { 
              created_at: item.created_at,
              student: `${item.user?.first_name ? item.user?.first_name : ''} ${item.user?.last_name ? item.user?.last_name : ''} ${item.user?.suffix ? item.user?.suffix : ''}`, 
              score: state.grading.total_points ? `${item.score} / ${state.grading.total_points} pts` : '-',
              checked: item.checked ? "Completed" : "Pending",
              assessment_id: state.grading.id,
              user: item.user,
              id: item.id })
        })
        return items
      }
    }),

    itemsPerPage(){
      return Number(this.paginate)
    },

    paginationPaginate(){
        return String(this.paginate)
    }
  },
};
</script>
  