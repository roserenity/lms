<template>
  <section class="mr-2">
    <v-sheet class="custom-border border py-1" transparent >
      <section class="d-flex flex-wrap align-center justify-space-between mx-4 py-2">
        <SearchBar 
          :placeholder="'Search assessment'" 
          :outlined="true" 
          :value.sync="search" 
          @clear="clearSearch"
          @search="onSearch"
          class="py-2"/>
        <section class="py-2 ml-auto">
          <FilterMenu
            :initialFilter="filter"
            @resetFilters="resetFilters"
            @applyFilters="() => { page = 1, getData() }"
            
            >
            <v-sheet max-height="350" class="overflow-y-auto scroller">
                <div>
                  <FormLabel class="mb-1" :label="tab === 0 ? 'CREATION DATE (YYYY-MM-DD)' : ( tab === 1 ? 'RECEIVING DATE (YYYY-MM-DD)' : 'SHARING DATE (YYYY-MM-DD)')" />
                </div>
                <label class="caption">FROM:</label>
                <FormDatePicker 
                    :val="filter.date_from"
                    :max="maxDate"
                    class_="col-11 mb-3"
                    @apply="(e) => {
                        filter.date_from = e
                        minDate = e
                    }"/>

                <label class="caption">TO:</label>
                <FormDatePicker 
                    :val="filter.date_to"
                    :min="minDate"
                    class_="col-11 mb-3"
                    @apply="(e) => {
                        filter.date_to = e
                        maxDate = e
                    }"/>
            </v-sheet>
          </FilterMenu>
        </section>
      </section>
      <Tabs :value.sync="tab" :tabs="items" :disabled="assessmentLoading"/>
      <v-divider />
      <v-tabs-items v-model="tab" style="background-color: transparent">
        <v-tab-item>
          <AssessmentTable
            :assessments="assessments"
            :loading = "assessmentLoading"
            :page="page"
            :paginate="Number(paginate)"
            :pageCount="pageCount"
            :totalCount="totalCount"
            @preview="preview"
            @duplicate="duplicate"
            @remove="remove"
            @move="move"
            @share="share"
            @getMyAssessments="getMyAssessments()"
          />
        </v-tab-item>
        <v-tab-item>
          <AssessmentShareWithMeTable
            :assessments="assessments"
            :new_assessment_uuid="new_assessment_uuid"
            :loading = "assessmentLoading"
            :page="page"
            :paginate="paginate"
            :pageCount="pageCount"
            :totalCount="totalCount"
          />
        </v-tab-item>
        <v-tab-item>
          <AssessmentShareToOtherTable
            :assessments="assessments"
            :loading = "assessmentLoading"
            :page="page"
            :paginate="paginate"
            :pageCount="pageCount"
            :totalCount="totalCount"
          />
        </v-tab-item>
      </v-tabs-items>
                        
      <FormPagination 
          :pageCount="pageCount" 
          :page="page"
          :paginate="paginationPaginate"
          @page="onPageChange" 
          @paginate="onPaginateChange"/>
    </v-sheet>
    
    <AssessmentPreviewDialog
      :data="toShare"
      :dialog="previewDialog"
      @close="previewDialog = false"
    />

    <AssessmentShareDialog
      :data="toShare"
      :dialog="dialog" 
      @close="dialog = false"
    />
    
    <DeleteDialog 
      :dialog="deleteDialog"
      type="Assessment"
      :loading="deleteLoading"
      @close="deleteDialog=false"
      @delete="deleteAssessment"
    />

    <AssessmentMoveDialog
      action="mine"
      :data="toShare"
      :courses="courses"
      :dialog="moveDialog"
      @close="moveDialog = false"
      :courseLoading="courseLoading"
      @getMyAssessments="getMyAssessments()"
    />
  </section>
</template>

<script>
import { mapActions, mapState, mapMutations } from "vuex";
import items from '@/constants/tabs/assessment/assessmentbuilder'
import AssessmentTable from "@/components/teacher/assessment/AssessmentTable.vue";
import AssessmentBuilder from "@/components/teacher/assessment/AssessmentBuilder.vue";
import AssessmentShareWithMeTable from "@/components/teacher/assessment/AssessmentShareWithMeTable.vue";
import AssessmentShareToOtherTable from "@/components/teacher/assessment/AssessmentShareToOtherTable.vue";
import AssessmentPreviewDialog from "@/components/teacher/assessment/AssessmentPreviewDialog.vue";
import AssessmentShareDialog from "@/components/teacher/assessment/AssessmentShareDialog.vue";
import AssessmentMoveDialog from "@/components/teacher/assessment/AssessmentMoveDialog.vue";
import searchAndPaginateMixin from '@/mixins/searchAndPaginateMixin';

export default {
  mixins: [searchAndPaginateMixin],
  components: {
    AssessmentBuilder,
    AssessmentTable,
    AssessmentShareToOtherTable,
    AssessmentShareWithMeTable,
    AssessmentPreviewDialog,
    AssessmentShareDialog,
    AssessmentMoveDialog
  },
  data: () => ({
    loading: false,
    assessmentLoading: false,
    courseLoading: false,
    deleteLoading: false,
    items: items,
    new_assessment_uuid: '',
    tab: 0,
    totalCount: 0,
    pageCount: 1,
    previewDialog: false,
    toShare: {
      course: {
        title: 'Untitled'
      }
    },
    dialog: false,
    moveDialog: false,
    deleteDialog: false,
    menu: false,
    minDate: '',
    maxDate: '',
  }),
  computed: {
    ...mapState("instructor", {
      assessments: (state) => state.assessments,
      courses: (state) => state.courses,
    }),

    paginationPaginate(){
      return String(this.paginate)
    }
  },
  created() {
    if(this.$route.query.assessment_uuid) {
      this.new_assessment_uuid = this.$route.query.assessment_uuid
      this.tab = Number(this.filter.tab)
      this.getSharedWithMeAssessments()
    }
  },
  mounted() {
    this.tab = Number(this.filter.tab)
    
    this.minDate = this.filter.date_from
    this.maxDate = this.filter.date_to

    if(this.$route.query.assessment_uuid) {
      this.new_assessment_uuid = this.$route.query.assessment_uuid
      this.getSharedWithMeAssessments()
    }

    this.getInstructorsToShareAction()

    this.courseLoading = true
    this.getCoursesAction().then(() => {
      this.courseLoading = false 
    }).catch(() => {
      this.courseLoading = false 
    })
  },
  methods: {
    ...mapActions("instructor", [
      "getDashboardCoursesAction",
      "getInstructorAssessmentsAction",
      "getInstructorsToShareAction",
      "showSharedToOtherAction",
      "showSharedWithMeAction",
      'duplicateAssessmentAction',
      'getCoursesAction',
      'deleteAssessmentAction'
    ]),

    ...mapMutations('instructor', ['assessmentsMutation']),
    ...mapMutations(["alertMutation"]),
    
    resetFilters() {
      this.filter = {
        date_from: ``,
        date_to: ``,
        tab: this.filter.tab
      }
      
      this.minDate = this.filter.date_from
      this.maxDate = this.filter.date_to

      this.updateQuery()
    },

    share(data) {
      this.dialog = true;
      this.toShare = data;
    },

    remove(data) {
      this.deleteDialog = true;
      this.itemToRemove = data;
    },

    duplicate(item) {
      this.duplicateAssessmentAction({
        course_id: item.course_id,
        assessment_id: item.id,
      }).then(() => {
        this.getMyAssessments()
        this.alertMutation({ show: true, text: "Assessment Successfully Duplicated!", type: "success" });
      });
    },

    move(data) {
      this.moveDialog = true;
      this.toShare = data;
    },

    preview(data) {
      this.previewDialog = true;
      this.toShare = data;
    },
    
    getData(){
      this.tab == 2
        ? this.getSharedToOthersAssessments()
        : this.tab ==1
        ? this.getSharedWithMeAssessments()
        : this.tab == 0
        ? this.getMyAssessments()
        : "";
    },

    getMyAssessments() {
      if(this.$route.query.tab === '0'){
        this.assessmentLoading = true
        this.getInstructorAssessmentsAction({ paginate: Number(this.paginate), page: this.page, search: this.search, ...this.filter }).then(res => {
          this.paginate = String(res.per_page)
          this.page = res.current_page
          this.pageCount = res.last_page
          this.totalCount = res.total
          this.assessmentLoading = false;
        }).catch(e=> {
          console.log(e)
          this.assessmentLoading = false;
          this.alertMutation({
            show: true,
            text: `Something went wrong in fetching owned assessments`,
            type: "error"
          });
        })
      }
    },

    getSharedWithMeAssessments(){
      if(!this.assessmentLoading && this.$route.query.tab === '1') {
        this.assessmentLoading = true
        this.showSharedWithMeAction({ paginate: Number(this.paginate), page: this.page, search: this.search, ...this.filter }).then(res=>{
          this.paginate = String(res.per_page)
          this.page = res.current_page
          this.pageCount = res.last_page
          this.totalCount = res.total
          this.assessmentLoading = false;
        }).catch(() => {
          this.assessmentLoading = false;
          this.alertMutation({
            show: true,
            text: `Something went wrong in fetching received assessments`,
            type: "error"
          });
        })
      }
    },

    getSharedToOthersAssessments(){
      if(!this.assessmentLoading && this.$route.query.tab === '2') {
        this.assessmentLoading = true
        this.showSharedToOtherAction({ paginate: Number(this.paginate), page: this.page, search: this.search, ...this.filter }).then(res=>{
          this.paginate = String(res.per_page)
          this.page = res.current_page
          this.pageCount = res.last_page
          this.totalCount = res.total
          this.assessmentLoading = false;
        }).catch(() => {
          this.assessmentLoading = false;
          this.alertMutation({
            show: true,
            text: `Something went wrong in fetching shared assessments`,
            type: "error"
          });
        })
      }
    },

    deleteAssessment(){
      if(this.itemToRemove){
        this.deleteLoading = true
        this.deleteAssessmentAction(this.itemToRemove).then(() => {
          this.deleteLoading = false
          this.itemToRemove = null
          this.deleteDialog = false
          this.alertMutation({
            show: true,
            text: `Assessment is Successfully Deleted`,
            type: "success"
          });
          this.getMyAssessments()
        }).catch(() => {
          this.deleteLoading = false
          this.deleteDialog = false
          this.alertMutation({
            show: true,
            text: `Something went wrong`,
            type: "error"
          });
        })
      }
    }

  },
  watch: {
    tab(val) {
      this.assessmentsMutation([])
      this.page = 1
      this.filter.tab = val
      this.updateQuery()
      this.getData()
    },
  },
};
</script>
