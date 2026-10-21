<template>
    <section>
      <section class="">

        <Breadcrumb
          :links="[
            {
              label: 'Back',
              link: true,
              route: (prevRoute && prevRoute.name === 'Instructor Grading Course') ? { name: 'Instructor Grading Course' , params: { id: $route.params.id }, query: { ...prevRoute.query}} : { name: 'Instructor Grading Course' , params: { id: $route.params.id }, query: { search: '', page: 1, paginate: 10 }},
            },
            {
              label: `${title}`,
              link: false,
              route: null,
            },
          ]"
        />
      </section>
      <v-row no-gutters dense>
        <v-col cols="12" md="9">
          <v-tabs
            v-model="tab"
            background-color="transparent"
            show-arrows
            color="primary"
            active-class="active"
            dense
          >
          <v-tab
              v-for="item in survey"
              :key="item"
              class="poppins text-capitalize f14 fw600"
              active-class="black--text"
            >
              {{ item }}
            </v-tab>
          </v-tabs>
        </v-col>
        <v-col cols="12" md="3" class="d-flex flex-row align-center">
          <FilterMenu
                :initialFilter="filter"
                @resetFilters="resetFilters()"
                @applyFilters="() => {page = 1, getData()}"
            >
            <section>
              <div>
                <FormLabel class="mb-1" :label="'SUBMISSION DATE (YYYY-MM-DD)'" />
              </div>
              <label class="caption">FROM:</label>
              <FormDatePicker 
                :val="filter.submitted_from"
                :max="maxDate"
                class_="col-12 mb-3"
                @apply="(e) => {
                    filter.submitted_from = e
                    minDate = e
                }"/>

              <label class="caption">TO:</label>
              <FormDatePicker 
                :val="filter.submitted_to"
                :min="minDate"
                class_="col-12 mb-3"
                @apply="(e) => {
                    filter.submitted_to = e
                    maxDate = e
                }"/>
            </section>
          </FilterMenu>
          
          <ButtonExport
              :loading="exportLoading"
              class="mr-3"
              @click="() =>{
                  tab === 0 ? exportList() : exportSummary()
              }"
              :disabled="tab === 0 ? (loading || learners.length === 0) : loading"
          />
        </v-col>
      </v-row>

      <LinearProgress v-if="loadingQuestions && !loading" />

      <v-tabs-items v-model="tab" style="background-color: transparent" class="py-2">
        <v-tab-item style="background-color: transparent">
          <SurveyIndividual 
              class="mx-2" 
              :loading="loading"
              :page="page"
              :paginate="paginate"
              :pageCount="pageCount"
              :learners="learners"
              :type="$route.params.type"
              @getData="getData"
              @search="(e) => {search = e, onSearch()}"
              @page="onPageChange"
              @paginate="onPaginateChange"
              @setLoading="(e) => loading = e"/>
        </v-tab-item>
        <v-tab-item style="background-color: transparent">
            <SurveySummary class="mx-3" :loading="loading" :submitted_from="filter.submitted_from" :submitted_to="filter.submitted_to"/>
        </v-tab-item>
        <v-tab-item style="background-color: transparent">
          <SurveyQuestion v-if="tab === 2" :loading="loading" :submitted_from="filter.submitted_from" :submitted_to="filter.submitted_to"/>
        </v-tab-item>
      </v-tabs-items>
    </section>
  </template>

<script>
import { mapActions, mapMutations, mapState } from 'vuex'
import searchAndPaginateMixin from '@/mixins/searchAndPaginateMixin';
import survey from '@/constants/tabs/survey/survey';
import SurveySummary from '@/components/teacher/survey/SurveySummary.vue';
import SurveyQuestion from '@/components/teacher/survey/SurveyQuestion.vue';
import SurveyIndividual from '@/components/teacher/survey/SurveyIndividual.vue';
import Breadcrumb from "@/layouts/teacher/InstructorBreadcrumbs.vue";

export default {
  mixins: [searchAndPaginateMixin],
  components: {
    SurveyIndividual,
    SurveyQuestion,
    SurveySummary,
    Breadcrumb
  },
  data:() => ({
      survey,
      loading: false,
      loadingQuestions: false,
      users: [],
      no_choices: ['file_upload', 'date', 'time', 'short_answer', 'paragraph'],
      pageCount: 1,
      learners: [],
      tab: 0,
      title: '',
      exportLoading: false,
      modelFrom: false,
      modelTo: false,
      filter: {
        submitted_from: '',
        submitted_to: ''
      },
      prevRoute: null,
      minDate: '',
      maxDate: ''
  }),
  methods: {
    ...mapActions('instructor', ['showCourseEvaluationAction', 'showModuleEvaluationAction',
      'getCourseEvaluationQuestionsAction', 'getModuleEvaluationQuestionsAction', 
      'getCourseEvaluationRespondentsAction', 'getModuleEvaluationRespondentsAction', 
      'getCourseEvaluationQuestionAnswers', 'getModuleEvaluationQuestionAnswers',
      'getCourseEvaluationAnswers', 'getModuleEvaluationAnswers',
      'exportCourseEvaluationSummary', 'exportModuleEvaluationSummary',
      'exportCourseEvaluationList', 'exportModuleEvaluationList']),

    ...mapMutations('instructor', ['courseEvaluationsMutation', 'evaluationAnswersMutation', 'moduleEvaluationsMutation']),
    ...mapMutations(['alertMutation']),

    resetFilters(){
      this.filter = {
        submitted_from: '',
        submitted_to: ''
      }

      this.minDate = ''
      this.maxDate = ''
    },

    exportList(){
      this.exportLoading = true
      if(this.$route.params.type === 'course_evaluation') {
        this.exportCourseEvaluationList({ course_evaluation_id: this.$route.params.survey_id, ...this.filter}).then(res => {
          this.exportLoading = false
          this.alertMutation({
            show: true,
            text: 'Export is being processed in the background.',
            type: "success"
          })
        }).catch(() => {
          this.exportLoading = false
          this.alertMutation({
            show: true,
            text: 'Something went wrong in exporting',
            type: "error"
          })
        })
      }
      if(this.$route.params.type === 'module_evaluation') {
        this.exportModuleEvaluationList({ module_evaluation_id: this.$route.params.survey_id, ...this.filter}).then(res => {
          this.exportLoading = false
          this.alertMutation({
            show: true,
            text: 'Export is being processed in the background.',
            type: "success"
          })
        }).catch(() => {
          this.exportLoading = false
          this.alertMutation({
            show: true,
            text: 'Something went wrong in exporting',
            type: "error"
          })
        })
      }
    },

    exportSummary(){
      this.exportLoading = true
      if(this.$route.params.type === 'course_evaluation') {
        this.exportCourseEvaluationSummary({ course_evaluation_id: this.$route.params.survey_id, search: this.search, ...this.filter}).then(res => {
          this.exportLoading = false
          this.alertMutation({
            show: true,
            text: 'Export is being processed in the background.',
            type: "success"
          })
        }).catch(() => {
          this.exportLoading = false
          this.alertMutation({
            show: true,
            text: 'Something went wrong in exporting',
            type: "error"
          })
        })
      }
      if(this.$route.params.type === 'module_evaluation') {
        this.exportModuleEvaluationSummary({ module_evaluation_id: this.$route.params.survey_id, search: this.search, ...this.filter}).then(res => {
          this.exportLoading = false
          this.alertMutation({
            show: true,
            text: 'Export is being processed in the background.',
            type: "success"
          })
        }).catch(() => {
          this.exportLoading = false
          this.alertMutation({
            show: true,
            text: 'Something went wrong in exporting',
            type: "error"
          })
        })
      }
    },

    getQuestions(){
      this.loadingQuestions = true
      if(this.$route.params.type === 'course_evaluation') {
        this.getCourseEvaluationQuestionsAction(this.$route.params.survey_id).then(res => {
          this.title = res.title
          if(this.tab === 2) {
            this.loadingQuestions = true
            this.getCourseEvaluationAnswers({id: this.$route.params.survey_id, ...this.filter}).finally(() => {
              this.loadingQuestions = false
            })
          } else {
            this.loadingQuestions = false
          }
        }).catch(() => {
          this.loadingQuestions = false
        })
      }

      if(this.$route.params.type === 'module_evaluation') {
        this.getModuleEvaluationQuestionsAction(this.$route.params.survey_id).then(res => {
          this.title = res.title
          if(this.tab === 2) {
            this.loadingQuestions = true
            this.getModuleEvaluationAnswers({id: this.$route.params.survey_id, ...this.filter}).finally(() => {
                this.loadingQuestions = false
            })
          } else {
            this.loadingQuestions = false
          }
        }).catch(() => {
          this.loadingQuestions = false
        })
      }
    },

    getData(){
      if(!this.loading) {
        this.loading = true
        if(this.$route.params.type === 'course_evaluation') {
          this.getCourseEvaluationRespondentsAction({ id:this.$route.params.survey_id, page: this.page, paginate: Number(this.paginate), search: this.search, ...this.filter}).then(res => {
            this.learners = res.data
            this.pageCount = res.last_page
            this.paginate = String(res.per_page)
            this.page = res.current_page
            this.search = this.search
          }).then(() => {
            this.loading = false
          }).catch(() => {
            this.loading = false
          })
        }
        if(this.$route.params.type === 'module_evaluation') {
          this.getModuleEvaluationRespondentsAction({ id:this.$route.params.survey_id, page: this.page, paginate: Number(this.paginate), search: this.search, ...this.filter}).then(res => {
            this.learners = res.data
            this.pageCount = res.last_page
            this.paginate = String(res.per_page)
            this.page = res.current_page
            this.search = this.search
          }).then(() => {
            this.loading = false
          }).catch(() => {
            this.loading = false
          })
        }
      }
    }

  },
  created(){
    
  },
  mounted() {
    this.getQuestions()
  },
  computed: {
    ...mapState('instructor', {
        questions: (state) => state.questions,
        evaluation_answers: (state) => state.evaluation_answers,
    }),
  },
  watch:{
    tab(val) {
      if(val === 0) {
        this.getQuestions()
        this.getData()
      }
      if(val === 2){
        this.getQuestions()
      }
    }
  },

  beforeRouteEnter(to, from, next) {
    next(vm => {
        vm.prevRoute = from          
    })
  },
}

</script>
