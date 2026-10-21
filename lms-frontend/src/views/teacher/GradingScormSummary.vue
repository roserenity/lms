<template>
  <section>
    <Breadcrumb
      :links="[
        {
          label: 'Back',
          link: true,
          route: (prevRoute && prevRoute.name === 'Instructor Grading Course') 
            ? { name: 'Instructor Grading Course' , params: { id: $route.params.id }, query: { ...prevRoute.query }} 
            : { name: 'Instructor Grading Course' , params: { id: $route.params.id }, query: { search: '', page: 1, paginate: 10 }},
        },
        {
          label: `${title}`,
          link: false,
          route: null,
        },
      ]"
    />

    <!-- <div class="mx-1">

      <h3 class="poppins secondary-1--text my-3">
        Course Reportage Summary
      </h3>

      <v-skeleton-loader
          v-if="loading1"
          height="700px"
          type="image"
          class="mb-3"
      ></v-skeleton-loader>

      <v-sheet class="custom-border border my-5 pa-3 fade " v-else >
        <v-btn text small :href="courseSummarySrc" target="_blank" class="float-right poppins"> 
            Open report in a new tab 
            <v-icon right small>
                mdi-open-in-new
            </v-icon>
        </v-btn>
        <iframe 
          :src="courseSummarySrc"
          frameborder='0' 
          id="myIframe"
          width="100%"
          height="700px"
          scrolling="auto" 
          class="fade"
        />
          
      </v-sheet> -->

      <div class="d-flex flex-wrap align-center justify-space-between">
        <SearchBar 
          :placeholder="'Search student'" 
          :value.sync="search1" 
          @clear="() => { search1 = '', getAllLearnerSummary() }"
          @search="() => { page2 = 1, getAllLearnerSummary() } "
          class="mb-5"/>
          
        <ButtonExport 
          @click="exportTable1"
          :loading="exportLoading1"
          :disabled="loading2 || enrollees.length === 0"
          class="poppins mb-5 ml-auto"/>
      </div>

      <v-data-table
        :loading="loading2"
        :headers="course_scorm_learners_tbl"
        :items="enrollees"
        hide-default-footer
        :items-per-page.sync="itemsPerPage1"
        class="text--center custom-border poppins f14">

        <template v-slot:item.action="{ item }" >
          <div class="d-flex flex-row justify-center">
            <v-btn dense icon color="primary" @click="$router.push({ name: 'Instructor Scorm Student', params: { id: $route.params.id, scorm_id: $route.params.scorm_id, scorm_registration_id: item.id, user_id: item.user_id }})">
              <v-icon small>
                mdi-eye-outline
              </v-icon>
            </v-btn>
            <v-btn dense icon color="primary" @click="() => { selected_id = item.id, dialog = true }" :loading="deleteLoading && selected_id === item.id">
              <v-icon small>
                mdi-delete-outline
              </v-icon>
            </v-btn>
          </div>
        </template>

        <template v-slot:item.scorm_progress.time_spent="{ item }">
          <span v-if="item.scorm_progress?.time_tracked">
            {{  item.scorm_progress.time_tracked }}
          </span>
        </template>

        <template v-slot:item.scorm_progress.completion_status="{ item }" >
          <span v-if="item.scorm_progress?.completion_status" :class="item.scorm_progress.completion_status === 'COMPLETED' ? 'success--text': ''">
            {{ item.scorm_progress.completion_status ?? ''}}
          </span>
        </template>

        <template v-slot:item.scorm_progress.success_status="{ item }" >
          <span v-if="item.scorm_progress?.success_status" :class="item.scorm_progress.success_status === 'PASSED' ? 'success--text': ''">
            {{ item.scorm_progress.success_status ?? ''}}
          </span>
        </template>

        <template v-slot:item.scorm_progress.completion_date="{ item }" >
          <span v-if="item.scorm_progress?.completion_date">
            {{ $dateFormat.mmDDyy(item.scorm_progress.completion_date) }}
          </span>
        </template>
        
      </v-data-table>

      <FormPagination 
          :pageCount="pageCount1" 
          :page="page1"
          :paginate="paginationPaginate1"
          @page="onPageChange1" 
          @paginate="onPaginateChange1"/>

      <DeleteDialog 
        :dialog="dialog"
        type="scorm progress"
        :loading="deleteLoading"
        @close="dialog=false"
        @delete="remove"
      />


      <!-- <div class="d-flex flex-wrap align-center justify-space-between">
        <SearchBar 
          :placeholder="'Search student'" 
          :value.sync="search2" 
          @clear="() => { search2 = '', getAllLearnerDetails() }"
          @search="() => { page2 = 1, getAllLearnerDetails() }"
          class="mb-5"/>
          
        <ButtonExport 
          @click="exportTable2"
          :loading="exportLoading2"
          :disabled="loading3 || course_scorm_learners_tbl.length === 0"
          class="poppins mb-5 ml-auto"/>
      </div>
      
      <v-data-table
        :loading="loading3"
        :headers="[]"
        :items="enrollees"
        hide-default-footer
        :items-per-page.sync="itemsPerPage2"
        class="text--center custom-border poppins f14">
      </v-data-table>

      <FormPagination 
          :pageCount="pageCount2" 
          :page="page2"
          :paginate="paginationPaginate2"
          @page="onPageChange2" 
          @paginate="onPaginateChange2"/> -->

  </section>
</template>

<script>
import { mapActions, mapMutations, mapState } from 'vuex'
import Breadcrumb from "@/layouts/teacher/InstructorBreadcrumbs.vue";
import { course_scorm_learners_tbl } from '@/constants/tblheaders/scorm';

export default {

  components: {
    Breadcrumb
  },

  data: () => ({
    courseAllLearnerDetailsSrc: '',
    exportLoading1: false,
    exportLoading2: false,
    deleteLoading: false,
    title: '',
    prevRoute: null,
    courseSummarySrc: '',
    loading1: false,
    loading2: false,
    loading3: false,
    dialog: false,
    course_scorm_learners_tbl,
    selected_id: null,
    search1: '',
    search2: '',
    pageCount1: 1,
    pageCount2: 1,
    page1: 1,
    page2: 1,
    paginate1: '10',
    paginate2: '10',
    progress: []
  }),

  computed: {
    ...mapState('instructor', {
        enrollees: (state) => state.enrollees
    }),

    itemsPerPage1(){
        return Number(this.paginate1)
    },

    paginationPaginate1(){
        return String(this.paginate1)
    },

    itemsPerPage2(){
        return Number(this.paginate2)
    },

    paginationPaginate2(){
        return String(this.paginate2)
    }
},

  mounted(){
    // this.loading1 = true
    // this.getScormCourseSummaryAction({ id: this.$route.params.scorm_id }).then((res) => {
    //   this.courseSummarySrc = res
    // }).catch(() => {
    //   this.alertMutation({
    //       show: true,
    //       text: 'Something went wrong in fetching course summary',
    //       type: "error"
    //   })
    // }).finally(() => {
    //   this.loading1 = false
    // })

    this.getAllLearnerSummary()

    //this.getAllLearnerDetails()

    // this.getScormCourseAllLearnerDetailsAction({ id: this.$route.params.scorm_id }).then((res) => {
    //   this.courseSummarySrc = res
    // })
  },

  methods: {
    ...mapActions('instructor', ['getScormCourseSummaryAction', 'getScormCourseAllLearnerDetailsAction', 'getScormCourseAllLearnersAction', 'getScormCourseAllLearnersExportAction', 'deleteScormCourseLearnersProgressAction', 'getScormCourseAllLearnersSummaryAction']),

    ...mapMutations(['alertMutation']),

    onPageChange1(newPage) {
      this.page1 = newPage;
      this.updateQuery();
    },

    onPageChange2(newPage) {
      this.page2 = newPage;
      this.updateQuery();
    },
    
    onPaginateChange1(newPaginate) {
      if (this.paginate1 !== newPaginate) {
        this.paginate1 = newPaginate;
        this.page1 = 1;
        this.getAllLearnerSummary();
      }
    },
    
    onPaginateChange2(newPaginate) {
      if (this.paginate2 !== newPaginate) {
        this.paginate2 = newPaginate;
        this.page2 = 1;
        this.getAllLearnerDetails();
      }
    },

    getAllLearnerSummary() {
      if(!this.loading2) {
        this.loading2 = true

        this.getScormCourseAllLearnersAction({ id: this.$route.params.scorm_id, paginate: Number(this.paginate1), page: this.page1, search: this.search1 }).then(res => {
          this.title = res.title
          this.page1 = res.learners.current_page
          this.pageCount1 = res.learners.last_page
          this.paginate1 = String(res.learners.per_page)
          this.loading2 = false
        }).catch(() => {
          this.loading2 = false
          this.alertMutation({
              show: true,
              text: 'Something went wrong in fetching students progress',
              type: "error"
          })
        })
      }
    },

    getAllLearnerDetails() {
      if(!this.loading3) {
        this.loading3 = true

        this.getScormCourseAllLearnersSummaryAction({ id: this.$route.params.scorm_id, paginate: Number(this.paginate2), page: this.page2, search: this.search2 }).then(res => {
          this.progress = res.data 
          this.page2 = res.current_page
          this.pageCount2 = res.last_page
          this.paginate2 = String(res.per_page)
          this.loading3 = false
        }).catch(() => {
          this.loading3 = false
          this.alertMutation({
              show: true,
              text: 'Something went wrong in fetching students progress',
              type: "error"
          })
        })
      }
    },

    exportTable1() {
      this.exportLoading1 = true

      this.getScormCourseAllLearnersExportAction({ id: this.$route.params.scorm_id, search: this.search1 }).then(res => {
          this.$exportToCSV(`Scorm Learners - ${this.title}`, res)
          this.exportLoading1 = false
      }).catch(() => {
          this.exportLoading1 = false
          this.alertMutation({
              show: true,
              text: 'Something went wrong',
              type: "error"
          })
      })
    },

    remove() {
      this.deleteLoading = true
      this.deleteScormCourseLearnersProgressAction(this.selected_id).then(() => {
        this.alertMutation({
          show: true,
          text: 'Successfully removed learner progress',
          type: "success"
        })

        this.getAllLearnerSummary()
      }).catch(() => {
        this.alertMutation({
          show: true,
          text: 'Something went wrong in removing scorm progress',
          type: "error"
        })
      }).finally(() => {
        this.deleteLoading = false
        this.dialog = false
      })
    }
  },

  beforeRouteEnter(to, from, next) {
    next(vm => {
      vm.prevRoute = from;
    });
  }
}
</script>
