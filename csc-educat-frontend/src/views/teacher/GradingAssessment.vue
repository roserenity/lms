

<template>
  <section id="grading-assessment">
    <Breadcrumb
      :links="[
        {
          label: 'Grading',
          link: true,
          route: (prevRoute && prevRoute.name === 'Instructor Grading') ? { name: 'Instructor Grading', query: { ...prevRoute.query} } : { name: 'Instructor Grading', query: { search: '', page: 1, paginate: 10 } },
        },
        {
          label: `${grading_title}`,
          link: false,
          route: null,
        },
      ]"
    />
    <SearchBar 
      :placeholder="'Search'" 
      :value.sync="search" 
      @clear="clearSearch"
      @search="onSearch"
      class="mb-5"/>

    <v-data-table
      :headers="grading_evaluation_tbl"
      :items="grading"
      class="text--center custom-border poppins f14 mt-3"
      :loading="loading"
      :items-per-page.sync="itemsPerPage"
      hide-default-footer>
      <template v-slot:item.title="{ item }">
        <div class="d-flex flex-column my-2">
          {{  item.title }}
        </div>
      </template>
      <template v-slot:item.type_of_assessment="{ item }">
        <span class="text-capitalize">
          {{ item?.type_of_assessment ? item.type_of_assessment.replace('_', ' ') : '' }}
          {{ item?.student_course_evaluations ? 'Course Evaluation' : '' }}
          {{ item?.student_module_evaluations ? 'Module Evaluation' : '' }}
          {{ item?.scorm_cloud_course_id ? 'Scorm' : '' }}
          {{ item?.group_activity_tool ? 'Group Activity' : '' }}
        </span>
      </template>
      <template v-slot:item.no_of_submitters="{ item }">
        <span>
          {{ item?.assessment_scores_count ? item.assessment_scores_count : '' }}
          {{ item?.student_course_evaluations ? item.student_course_evaluations.length : '' }}
          {{ item?.student_module_evaluations ? item.student_module_evaluations.length : '' }}
          {{ item?.scorm_cloud_course_id ? item.learner_registrations_count : '' }}
          {{ item?.group_activity_tool ? item.no_of_members : '' }}
        </span>
      </template>
      <template v-slot:item.action="{ item }">
        <v-btn
          text
          v-if="item?.scorm_cloud_course_id"
          small
          :disabled="item.learner_registrations_count === 0"
          class="primary--text text-capitalize"
          :to="{ name: 'Instructor Scorm', params: { id: $route.params.id, scorm_id: item.id }, query: { search: '', page: 1, paginate: 10, search: ''} }"
        >
          <v-icon left>mdi-eye-outline</v-icon>
          View
        </v-btn>
        <v-btn
          text
          small
          v-if="item?.type_of_assessment"
          class="primary--text text-capitalize"
          :to="{ name: 'Instructor Grading Assessment', params: { course_id: $route.params.id, id: item.id, course_name: item.name }, query: { search: '', page: 1, paginate: 10, search: '' } }"
        >
          <v-icon left>mdi-eye-outline</v-icon>
          View
        </v-btn>
        <v-btn
          text
          small
          v-if="item?.student_course_evaluations"
          class="primary--text text-capitalize"
          :to="{ name: 'Instructor Survey', params: { id: $route.params.id, survey_id: item.id, type: 'course_evaluation' }, query: { search: '', page: 1, paginate: 10 } }"
        >
          <v-icon left>mdi-eye-outline</v-icon>
          View
        </v-btn>
        <v-btn
          text
          small
          v-if="item?.student_module_evaluations"
          class="primary--text text-capitalize"
          :to="{ name: 'Instructor Survey', params: { id: $route.params.id, survey_id: item.id, type: 'module_evaluation' }, query: { search: '', page: 1, paginate: 10 } }"
        >
          <v-icon left>mdi-eye-outline</v-icon>
          View
        </v-btn>
        <v-btn
          text
          small
          v-if="item?.group_activity_tool"
          class="primary--text text-capitalize"
          :to="{ name: 'Instructor Group Activity', params: { id: $route.params.id, group_activity_id: item.id, type: 'group_activity' }, query: { search: '', page: 1, paginate: 10 } }"
        >
          <v-icon left>mdi-eye-outline</v-icon>
          View
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
import { grading_assessment_tbl, grading_evaluation_tbl } from '@/constants/tblheaders/grading';
import Breadcrumb from "../../layouts/teacher/InstructorBreadcrumbs.vue";

export default {
  mixins: [searchAndPaginateMixin],
  components: { Breadcrumb },
  methods: {
    ...mapActions('instructor', ['getGradingAssessmentsEvaluations']),
    ...mapMutations('instructor', ['gradingMutation']),

    getData(){
      if(!this.loading) {
        this.loading = true
        this.getGradingAssessmentsEvaluations({ id: this.$route.params.id, paginate: Number(this.paginate), page: this.page, search: this.search, type: this.$route.params.type }).then(res => {
          console.log(res)
          this.grading_title = res.course_title
          this.pageCount = res.paginated_result.last_page
          this.paginate = res.paginated_result.per_page.toString()
          this.page = res.paginated_result.current_page
          this.loading = false
        }).catch(()=>{
          this.loading = false
        })
      }
    },

    // viewAssessment(e) {
    //   this.$router.push({ name: 'Instructor Grading Assessment', params: { id: e.id }, query: { page: 1 } })
    // },

    // viewEvaluation(e) {
    //   if(e.type === 'COURSE EVALUATION') {
    //     this.$router.push({ name: 'Instructor Survey', params: { id: this.$route.params.id, survey_id: e.id, type: 'course_evaluation' }, query: { search: '', page: 1, paginate: 10 } })
    //   } else {
    //     this.$router.push({ name: 'Instructor Survey', params: { id: this.$route.params.id, survey_id: e.id, type: 'module_evaluation' }, query: { search: '', page: 1, paginate: 10 } })
    //   }
    // },
  },
  mounted(){
    this.getData()
  },
  created(){
    this.gradingMutation([])
  },
  computed:{
    ...mapState('instructor', {
      grading: (state) => state.grading,
    }),

    itemsPerPage(){
      return Number(this.paginate)
    },

    paginationPaginate(){
        return String(this.paginate)
    }
  },
  data: () => ({
    grading_assessment_tbl,
    grading_evaluation_tbl,
    search: '',
    grading_title: '',
    loading: false,
    pageCount: 1,
    paginate: '10',
    totalCount: 0,
    page: 1,
    prevRoute: null,
  }),
  
  beforeRouteEnter(to, from, next) {
    next(vm => {
        vm.prevRoute = from          
    })
  },
};
</script>

<style>
.v-expansion-panel-content__wrap {
  padding: 0 !important;
}
</style>