<!-- <template>
    <section>
      <section>
        <Breadcrumb
          :links="[
            {
              label: 'Back',
              link: true,
              route: (prevRoute && prevRoute.name === 'Instructor Scorm') 
                ? { name: 'Instructor Scorm' , params: { id: $route.params.id, scorm_id: $route.params.scorm_id }} 
                : { name: 'Instructor Scorm' , params: { id: $route.params.id, scorm_id: $route.params.scorm_id }},
            },
            {
              label: `${full_name}`,
              link: false,
              route: null,
            },
          ]"
        />
      </section>

      <div class="mx-3">
        <h3 class="poppins secondary-1--text my-3">
          Learner Scorm Progress
        </h3>

        <v-skeleton-loader
            v-if="loading1"
            height="700px"
            type="image"
            class="mb-1"
        ></v-skeleton-loader>

        <v-sheet class="custom-border border my-5 pa-3 fade " v-else >
          <v-btn text small :href="learnerRegistrationSummarySrc" target="_blank" class="float-right poppins"> 
              Open report in a new tab 
              <v-icon right small>
                  mdi-open-in-new
              </v-icon>
          </v-btn>
          <iframe 
            :src="learnerRegistrationSummarySrc"
            frameborder='0' 
            id="myIframe"
            width="100%"
            height="450px"
            scrolling="auto" 
            class="fade"
          />
        </v-sheet>
      </div>
      
      <div v-for="answer in answers" :key="answer.key" class="ma-3">
        <h4 class="poppins">
          {{ answer.title}}
        </h4>
        <div class="d-flex flex-column mb-2">
          <FormLabel :label="`ATTEMPTS: ${answer.attempts}`"/>
          <FormLabel :label="`COMPLETION STATUS: ${answer.activity_completion.toUpperCase()}`"/>
          <FormLabel :label="`SUCCESS STATUS: ${answer.activity_success.toUpperCase()}`"/>
        </div>

        <v-data-table
          :loading="loading2"
          :headers="course_scorm_learner_progress_tbl"
          :items="answer.answers"
          hide-default-footer
          :items-per-page.sync="itemsPerPage"
          class="text--center custom-border poppins f14">

          <template v-slot:item.learnerResponse="{ item }">
            {{  item.learnerResponse.replaceAll('urn:scormdriver:', '').replaceAll('-', ' ').replaceAll('[,]', ', ').replaceAll('[.]', ' && ') }}
          </template>

          <template v-slot:item.attempt="{ item }">
              {{  Number(item.id.split('_').pop()) + 1 }}
          </template>

          <template v-slot:item.correctResponses="{ item }">
            <span v-if="item.type === 'FillIn'">
              {{  item.correctResponses[0].replaceAll('urn:scormdriver:', '').split(',').join(', ').replaceAll('-', ' ')}}
            </span> 
            <span v-else>
              {{  item.correctResponses[0].replaceAll('urn:scormdriver:', '').split('[,]').join(', ').replaceAll('-', ' ').replaceAll('[.]', ' && ')}}
            </span>
          </template>

        </v-data-table>
      </div>
    </section>
  </template>
  
  <script>
  import { mapActions, mapMutations, mapState } from 'vuex'
  import Breadcrumb from "@/layouts/teacher/InstructorBreadcrumbs.vue";
  import { course_scorm_learner_progress_tbl } from '@/constants/tblheaders/scorm';
  import searchAndPaginateMixin from '@/mixins/searchAndPaginateMixin';
  
  export default {
    mixins: [searchAndPaginateMixin],
  
    components: {
      Breadcrumb
    },
  
    data: () => ({
      courseAllLearnerDetailsSrc: '',
      exportLoading: false,
      deleteLoading: false,
      full_name: '',
      prevRoute: null,
      learnerRegistrationSummarySrc: '',
      loading1: false,
      loading2: false,
      pageCount: 1,
      dialog: false,
      course_scorm_learner_progress_tbl,
      selected_id: null,
      answers: []
    }),
  
    computed: {
      itemsPerPage(){
          return Number(this.paginate)
      },
  
      paginationPaginate(){
          return String(this.paginate)
      }
  },
  
    mounted(){
      this.loading1 = true
      this.getScormLearnerRegistrationSummaryAction({ id: this.$route.params.scorm_id, user_id: this.$route.params.user_id }).then((res) => {
        this.learnerRegistrationSummarySrc = res
      }).catch(() => {
        this.alertMutation({
            show: true,
            text: 'Something went wrong in fetching learner registration summary',
            type: "error"
        })
      }).finally(() => {
        this.loading1 = false
      })
    },
  
    methods: {
      ...mapActions('instructor', ['getScormLearnerRegistrationSummaryAction', 'getScormCourseAllLearnerProgressAction', 'getScormCourseAllLearnersExportAction']),
  
      ...mapMutations(['alertMutation']),
  
      getData() {
        if(!this.loading2) {
          this.loading2 = true
  
          this.getScormCourseAllLearnerProgressAction({ scorm_id: this.$route.params.scorm_id, user_id: this.$route.params.user_id }).then(res => {
            this.full_name = res.registration.user.full_name
            this.answers = res.scorm_progress.children
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
  
      exportTable() {
        this.exportLoading = true
  
        this.getScormCourseAllLearnersExportAction({ id: this.$route.params.scorm_id, search: this.search }).then(res => {
            this.$exportToCSV(`Scorm Learners - ${this.title}`, res)
            this.exportLoading = false
        }).catch(() => {
            this.exportLoading = false
            this.alertMutation({
                show: true,
                text: 'Something went wrong',
                type: "error"
            })
        })
      },
    },
  
    beforeRouteEnter(to, from, next) {
      next(vm => {
        vm.prevRoute = from;
      });
    }
  }
  </script> -->



<template>
  <section>
    <section>
      <Breadcrumb
        :links="[
          {
            label: `< ${scorm?.title ?? '' }`,
            link: true,
            route: (prevRoute && prevRoute.name === 'Instructor Scorm') 
              ? { name: 'Instructor Scorm' , params: { id: $route.params.id, scorm_id: $route.params.scorm_id }} 
              : { name: 'Instructor Scorm' , params: { id: $route.params.id, scorm_id: $route.params.scorm_id }},
          },
          {
            label: `${user?.full_name ?? '' }`,
            link: false,
            route: null,
          },
        ]"
      />
    </section>


    <v-skeleton-loader
      v-if="loading2"
          height="100px"
          type="image"
          class="mb-5"
      ></v-skeleton-loader>
      
    <v-card 
      v-else
        class="pa-5 mb-5 custom-border border fade">
      <h3 class="poppins">
        {{ user.full_name}}
      </h3>
      <FormLabel :label="user.email" class=" mb-5"/>
      <div class="d-flex flex-wrap align-start justify-space-around">
        <div class="ma-2 text-center" v-if="progress.completion_status">
          <FormLabel :label="`COMPLETION STATUS`"/>
          <v-alert v-if="!progress.completion_status" dense small color="#BDBDBD33" class="ma-auto">
            -
          </v-alert>
          <v-alert v-else dense small :color="progress.completion_status === 'COMPLETED' ? '#7BC14533' : '#E49E3233'" class="ma-auto" :class="progress.completion_status === 'COMPLETED' ? 'success--text' : 'error--text'">
              {{ progress.completion_status }}
          </v-alert>
        </div>

        <!-- <div class="ma-2 text-center" v-if="progress.success_status">
          <FormLabel :label="`SUCCESS STATUS`"/>
          <v-alert v-if="!progress.success_status" dense small color="#BDBDBD33" class="ma-auto">
            -
          </v-alert>
          <v-alert v-else dense small :color="progress.success_status === 'PASSED' ? '#7BC14533' : '#E49E3233'" class="ma-auto" :class="progress.success_status === 'PASSED' ? 'success--text' : 'error--text'">
              {{ progress.success_status }}
          </v-alert>
        </div> -->

        <div class="ma-2 text-center" v-if="progress.time_tracked">
          <FormLabel :label="`TOTAL TIME`"/>
          <v-alert dense small class="ma-auto" :class="'secondary-2--text'" color="#BDBDBD33">
              {{ progress.time_tracked }}
          </v-alert>
        </div>

        <div class="ma-2 text-center" v-if="progress.completion_date">
          <FormLabel :label="`COMPLETION DATE`"/>
          <v-alert dense small class="ma-auto" :class="'secondary-2--text'" color="#BDBDBD33">
              {{ $dateFormat.dayDateTime(progress.completion_date) }}
          </v-alert>
        </div>

      </div>
    </v-card>

    <!-- <div class="mx-3">
      <h3 class="poppins secondary-1--text my-3">
        Learner Scorm Progress
      </h3>

      <v-skeleton-loader
          v-if="loading1"
          height="700px"
          type="image"
          class="mb-1"
      ></v-skeleton-loader>

      <v-sheet class="custom-border border my-5 pa-3 fade " v-else >
        <v-btn text small :href="learnerRegistrationSummarySrc" target="_blank" class="float-right poppins"> 
            Open report in a new tab 
            <v-icon right small>
                mdi-open-in-new
            </v-icon>
        </v-btn>
        <iframe 
          :src="learnerRegistrationSummarySrc"
          frameborder='0' 
          id="myIframe"
          width="100%"
          height="450px"
          scrolling="auto" 
          class="fade"
        />
      </v-sheet>
    </div> -->


    <v-data-table
      :loading="loading3"
      :headers="course_scorm_learner_interactions_tbl"
      :items="interactions"
      hide-default-footer
      :items-per-page.sync="itemsPerPage"
      class="text--center custom-border poppins f14 fade">

      <template v-slot:item.interaction_id="{ item }">
        <span class="py-2">
          {{ item.interaction_id.split('_').slice(0, -1).join(' ') }}
        </span>
      </template>

      <template v-slot:item.attempt_no="{ item }">
        <span class="py-2">
          {{ Number(item.interaction_id.split('_').pop()) + 1 }}
        </span>
      </template>

      <template v-slot:item.learner_response="{ item }">
        <span v-if="item.type === 'STORYLINE'">
          {{ ['CHECKBOX'].includes(item.interaction_type) ? JSON.parse(item.learner_response).join(', ') : '' }}
          {{ ['RADIO BUTTON'].includes(item.interaction_type) ? JSON.parse(item.learner_response)[0] : ''  }}
          {{ ['LONGFILLIN'].includes(item.interaction_type) ? item.learner_response : ''  }}
          <span v-if="['DRAGNDROP'].includes(item.interaction_type)">
            <div v-for="response in JSON.parse(item.learner_response)">
              {{ response.target }} - {{ response.object }}
            </div>
          </span>
        </span>
        <span v-else>
          <span v-if="['DRAGNDROP'].includes(item.interaction_type)">
            <div v-for="response in JSON.parse(item.learner_response)">
              <span v-if="!(['answer', 'target']).includes(response.target)">{{ response.target }} -</span> {{ response.object }}
            </div>
          </span>
          {{ ['FILLIN', 'ESSAY'].includes(item.interaction_type) ? item.learner_response : ''  }}
          {{ ['LONGFILLIN'].includes(item.interaction_type) ? JSON.parse(item.learner_response).join(', ') : ''  }}
          {{ ['CHECKBOX'].includes(item.interaction_type) ? JSON.parse(item.learner_response).join(', ') : '' }}
        </span>
      </template>
      <template v-slot:item.correct_responses="{ item }">
        <span v-if="item.type === 'RISE'">
          {{ ['FILLIN'].includes(item.interaction_type) ? JSON.parse(item.correct_responses).join(', ') : ''  }}
        </span>
      </template>

      <template v-slot:item.result="{ item }" >
        <span v-if="item.result" :class="item.result === 'correct' ? 'success--text': ''">
          {{ item.result.toUpperCase() ?? ''}}
        </span>
        <span v-else> N/A </span>
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
import { mapActions, mapMutations, mapState } from 'vuex'
import Breadcrumb from "@/layouts/teacher/InstructorBreadcrumbs.vue";
import { course_scorm_learner_interactions_tbl } from '@/constants/tblheaders/scorm';
import searchAndPaginateMixin from '@/mixins/searchAndPaginateMixin';

export default {
  mixins: [searchAndPaginateMixin],

  components: {
    Breadcrumb
  },

  data: () => ({
    courseAllLearnerDetailsSrc: '',
    exportLoading: false,
    deleteLoading: false,
    full_name: '',
    prevRoute: null,
    learnerRegistrationSummarySrc: '',
    loading1: false,
    loading2: false,
    loading3: false,
    pageCount: 1,
    dialog: false,
    course_scorm_learner_interactions_tbl,
    selected_id: null,
    interactions: [],
    user: null,
    progress: null,
    scorm: null
  }),

  computed: {
    itemsPerPage(){
        return Number(this.paginate)
    },

    paginationPaginate(){
        return String(this.paginate)
    }
},

  mounted(){
    // this.loading1 = true
    // this.getScormLearnerRegistrationSummaryAction({ id: this.$route.params.scorm_id, user_id: this.$route.params.user_id }).then((res) => {
    //   this.learnerRegistrationSummarySrc = res
    // }).catch(() => {
    //   this.alertMutation({
    //       show: true,
    //       text: 'Something went wrong in fetching learner registration summary',
    //       type: "error"
    //   })
    // }).finally(() => {
    //   this.loading1 = false
    // })
    this.loading2 = true

    this.getScormCourseAllLearnerProgressAction({ scorm_id: this.$route.params.scorm_id, user_id: this.$route.params.user_id }).then(res => {
      this.user = res.user
      this.scorm = res.scorm
      this.progress = res.scorm_progress
      this.loading2 = false
    }).catch(() => {
        this.loading2 = false
        this.alertMutation({
            show: true,
            text: 'Something went wrong in fetching students progress',
            type: "error"
        })
    })
  },

  methods: {
    ...mapActions('instructor', ['getScormLearnerRegistrationSummaryAction', 'getScormCourseAllLearnerProgressAction', 'getScormCourseAllLearnerInteractionAction', 'getScormCourseAllLearnersExportAction']),

    ...mapMutations(['alertMutation']),

    getData() {
      if(!this.loading3) {
        this.loading3 = true

        this.getScormCourseAllLearnerInteractionAction({ scorm_registration_id: this.$route.params.scorm_registration_id, user_id: this.$route.params.user_id, paginate: Number(this.paginate), page: this.page }).then(res => {
          this.interactions = res.data
          this.loading3 = false
          this.pageCount = res.last_page
          this.paginate = String(res.per_page)
          this.page = res.current_page
        }).catch(() => {
            this.loading3 = false
            this.alertMutation({
                show: true,
                text: 'Something went wrong in fetching students interactions',
                type: "error"
            })
        })
      }
    },

    exportTable() {
      this.exportLoading = true

      this.getScormCourseAllLearnersExportAction({ id: this.$route.params.scorm_id, search: this.search }).then(res => {
          this.$exportToCSV(`Scorm Learners - ${this.title}`, res)
          this.exportLoading = false
      }).catch(() => {
          this.exportLoading = false
          this.alertMutation({
              show: true,
              text: 'Something went wrong',
              type: "error"
          })
      })
    },
  },

  beforeRouteEnter(to, from, next) {
    next(vm => {
      vm.prevRoute = from;
    });
  }
}
</script>

  