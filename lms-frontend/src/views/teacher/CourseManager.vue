<template>
  <section class="mt-3">
    <v-row dense>
      <v-col class="flex-grow-1"  style="min-width: 0;">
        <v-tabs class="tabs border" color="primary" v-model="tab" background-color="transparent" id="course-manager" active-class="active" show-arrows>
          <v-tab v-for="item in ( course.type_of_learning === 'BLENDED' ? blended_tabs : e_learning_tabs )"
              :key="item" 
              active-class="active"
              class="primary-font f14 fw600 text-capitalize poppins">
              {{item}}
          </v-tab>
        </v-tabs>
      </v-col>
      <v-col cols="auto" class="flex-shrink-0 d-flex align-center justify-end">
        <v-btn dense color="primary" class="poppins f12 fw600 mr-1" :loading="duplicateLoading" @click="duplicateCourse">Duplicate</v-btn>
        <v-btn dense color="primary" class="poppins f12 fw600" :loading="exportLoading" @click="exportCourse">Export</v-btn>
      </v-col>
    </v-row>
    <LinearProgress v-if="loading"/>
    <v-tabs-items v-model="tab" id="main-container" style="background-color: transparent">
      <v-tab-item v-for="item in ( course.type_of_learning === 'BLENDED' ? blended_tabs : e_learning_tabs )" :key="item">
        <v-row v-if="item === 'Course Info'" dense>
          <v-col :cols="$vuetify.breakpoint.mobile ? 12 : activity_log.show ? 8 : 12">
            <AboutCourse 
              v-if="course"
              :courseLoading="loading"
              :course="course" 
              :activity_log="activity_log.show" 
              :classes="classes"
              :permission="permission"/>
          </v-col>
          <v-col :cols="$vuetify.breakpoint.mobile ? 12 : activity_log ? 4 : 12">
            <CourseActivityLog :activity_log="activity_log.show" @close="closeActivityLog" :popup="activity_log.popup"/>
          </v-col>
        </v-row>
        <Enrollees v-if="item === 'Certificate Issuance' && course" :course="course" />
        <ModuleEditor v-if="item === 'Module Editor' && course" 
          :course="course" 
          :topicLoading="topicLoading"
          :subtopicLoading="subtopicLoading"
          :assessmentLoading="assessmentLoading"
          :groupActivityLoading="groupActivityLoading"
          :permission="permission"
          @addAssessment="addAssessment"
          @addGroupActvity="addGroupActvity"
          @viewAssessment="viewAssessment"
          @viewGroupActivity="viewGroupActivity"
          @viewModuleEvaluation="viewModuleEvaluation"
          @viewCourseEvaluation="viewCourseEvaluation"/>
        <Synchronous v-if="item === 'Synchronous' && course" :course_id="course.id"
          :status="course.status"
          :permission="permission"/>
        <SynchronousAttendees v-if="item === 'Attendees'" :item="tab"/>
        <Assessment
          v-if="item === 'Assessment' && course"
          :item="selectedAssessment"
          :status="course.status"
          :assessment_questions="selectedAssessment ? assessments.filter(item => item.id === selectedAssessment.id)[0] : null"
          :permission="permission"
          @closeAssessment="closeAssessment"
        />
        <ModuleEvaluation
          v-if="item === 'Module Evaluation'&& course" 
          :evaluation="selectedModuleEvaluation"
          :status="course.status"
          :permission="permission"
          :loading="loadingViewEvaluation"
        />
        <CourseEvaluation 
          v-if="item === 'Course Evaluation' && course"
          :evaluation="selectedCourseEvaluation"
          :status="course.status"
          :permission="permission"
          :loading="loadingViewEvaluation"
        />
        <CourseGroups 
          v-if="item === 'Course Groups' && course"
        />
        <GroupActivity 
          v-if="item === 'Group Activity' && course"
          :status="course.status"
          :permission="permission"
          :groupActivity="selectedGroupActivity"
          @setGroupActivity="(e) => selectedGroupActivity = e"
          @viewGroupActivity="viewGroupActivity"
        />
      </v-tab-item>
    </v-tabs-items>

    <div class="my-10 secondary--text poppins f12 font-italic text-capitalize">
      Saved {{ getUpdatedAt }} 
    </div>

    <v-dialog max-width="350" v-model="dialog" :persistent="submitting">
      <v-sheet class="border" v-if="!saved">
        <div class="" v-if="!submitting">
          <div class="text-center pa-5">
            <v-icon color="success" size="46">mdi-check-circle-outline</v-icon>
            <h4 class="poppins primary--text fw600 my-3">
              Your changes will be saved to drafts.
            </h4>
          </div>
          <v-divider />
          <div class="text-center py-2">
            <v-btn
              text
              class="f12 poppins text-capitalize mx-1"
              width="100"
              @click="dialog = false"
              >Cancel</v-btn
            >
            <v-btn
              color="primary"
              class="f12 poppins text-capitalize mx-1"
              width="100"
              @click="save"
              >Confirm</v-btn
            >
          </div>
        </div>
        <div class="text-center pa-5" v-else>
          <v-progress-circular size="32" indeterminate color="green" />
          <h4 class="poppins primary--text fw600 my-3">Saving...</h4>
        </div>
      </v-sheet>
      <v-sheet class="border" v-else>
        <div class="">
          <div class="text-center pa-5">
            <v-icon color="success" size="46">mdi-check-circle</v-icon>
            <h4 class="poppins primary--text fw600 my-3">Saved to drafts.</h4>
          </div>
        </div>
      </v-sheet>
    </v-dialog>
  </section>
</template>

<script>
import { mapActions, mapState, mapMutations } from "vuex";
import { blended_tabs, e_learning_tabs } from '@/constants/tabs/course/coursemanager'
import AboutCourse from "@/components/teacher/course/AboutCourse.vue";
import ModuleEditor from "@/components/teacher/course/ModuleEditor.vue";
import Enrollees from "@/components/teacher/course/Enrollees.vue";
import Assessment from "@/components/teacher/assessment/Assessment.vue";
import Synchronous from "@/components/teacher/course/Synchronous.vue";
import SynchronousAttendees from "@/components/teacher/course/SynchronousAttendees.vue";
import CourseActivityLog from "@/components/teacher/course/CourseActivityLog.vue";
import CourseEvaluation from "@/components/teacher/course/CourseEvaluation.vue";
import ModuleEvaluation from "@/components/teacher/course/ModuleEvaluation.vue";
import CourseGroups from "@/components/teacher/course/CourseGroups.vue";
import GroupActivity from "./GroupActivity.vue";

export default {
  components: {
    ModuleEditor,
    AboutCourse,
    Assessment,
    Synchronous,
    SynchronousAttendees,
    CourseActivityLog,
    ModuleEvaluation,
    CourseEvaluation,
    Enrollees,
    CourseGroups,
    GroupActivity
  },
  data: () => ({
    blended_tabs,
    e_learning_tabs,
    loading: true,
    dialog: false,
    saved: true,
    next: null,
    submitting: false,
    selectedAssessment: null,
    selectedModuleEvaluation: null,
    selectedCourseEvaluation: null,
    selectedGroupActivity: null,
    topicLoading: false,
    subtopicLoading: false,
    assessmentLoading: false,
    groupActivityLoading: false,
    loadingSave: false,
    loadingViewEvaluation: false,
    exportLoading: false,
    duplicateLoading: false
  }),
  mounted(){
    if(!this.$route.query || !this.$route.query.tab || this.$route.query.tab === '3') {
        this.$router.replace({ query: { tab: 0, timestamp: Date.now() } });
    } else {
      this.$router.replace({ query: { tab: this.$route.query.tab, timestamp: Date.now()} });
    }
    
    this.e_learning_tabs = this.e_learning_tabs.filter((item) => item !== 'Assessment' || item !== 'Group Activity')
    this.blended_tabs = this.blended_tabs.filter((item) => item !== 'Assessment' || item !== 'Group Activity')

    this.showCourseAction(this.$route.params.id).then(() => {
      this.loading = false;
      this.getAssessmentsAction({
        course_id: this.course.id,
      });
    }).catch(() => {
      this.loading = false
    });

    this.getClassesAction();
  },
  computed: {
    ...mapState({
      tenant: (state) => state.tenant,
    }),

    ...mapState("instructor", {
      course: (state) => state.course,
      classes: (state) => state.classes,
      assessments: (state) => state.assessments,
      assessment_classes: (state) => state.assessment_classes,
      assessment_questions: (state) => state.assessment_questions,
      activity_log: (state) => state.activity_log
    }),

    getUpdatedAt(){
      return this.$dateFormat.calendar(this.course.updated_at)
    },

    tab: {
      get() {
        let tab = parseInt(this.$route.query.tab);
        return tab;
      },
      set(tab) {
        this.$router.replace({ query: { tab: tab, timestamp: Date.now() } });
      },
    },

    permission(){
      if(this.loading) return 'owner'
      return (Object.keys(this.course).length > 0 && this.course.my_permission.length > 0) ? this.course.my_permission[0].pivot.permission : 'owner'
    }
  },
  watch:{
    assessments(val){
      if(this.selectedAssessment) {
        return this.selectedAssessment = {  ...this.selectedAssessment, ...this.assessments.filter(i => i.id == this.selectedAssessment.id)[0] }
      }
    },
    course() {
      this.e_learning_tabs = this.e_learning_tabs.filter((item) => item !== 'Assessment' || item !== 'Group Activity')
      this.blended_tabs = this.blended_tabs.filter((item) => item !== 'Assessment' || item !== 'Group Activity')
    },
  },
  methods: {
    ...mapActions("instructor", [
      "showCourseAction",
      "getClassesAction",
      "getAssessmentsAction",
      "addAssessmentAction",
      "addGroupActivityAction", 
      "updateAssessmentAction",
      "showTaggedUsersAction",
      "getTagClassAssessmentAction",
      "courseExportAction",
      "viewGroupActivityAction",
      "duplicateCourseAction",
    ]),

    ...mapMutations(["alertMutation"]),

    ...mapMutations('instructor', ["activityLogMutation", "evaluationQuestionMutation"]),

    duplicateCourse(){
      this.duplicateLoading = true

      this.duplicateCourseAction(this.course.id).then(() => {
        this.duplicateLoading = false
        this.alertMutation({ 
          show: true, 
          text: "Course successfully duplicated",
          type: "success" });
      }).catch(() => {
        this.duplicateLoading = false
        this.alertMutation({ 
          show: true, 
          text: "Something went wrong",
          type: "error" });
      })
      
    },

    exportCourse(){
      this.exportLoading = true
      this.courseExportAction(this.course.id).then(response => {
      
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', this.course.title.toLowerCase().replaceAll(' ', '_') + ".docx");
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        this.exportLoading = false;
        this.alertMutation({ 
          show: true, 
          text: "Course successfully exported",
          type: "success" });
      }).catch(e => {
        console.log(e)
        this.exportLoading = false;
        this.alertMutation({ 
          show: true, 
          text: "Something went wrong",
          type: "error" });
      })
    },

    closeActivityLog() {
      this.activityLogMutation(false)
    },

    save() {
      this.submitting = true;
      setTimeout(() => {
        this.saved = true;
        setTimeout(() => {
          this.next();
        }, 2000);
      }, 2000);
    },

    addGroupActvity(data) {
      if(data.hasOwnProperty('is_topic')) {
        if(data.is_topic){
          this.topicLoading = true
        } else {
          this.subtopicLoading =  true
        }
      } else {
        this.groupActivityLoading = true
      }

      this.addGroupActivityAction({ 
        course_id: this.course.id,
        module_id: data.module_id,
        is_topic: data.is_topic,
        topic_or_sub_topic_id: data.topic_or_subtopic_id,
        title: data.title,
        group_activity_tool: 'GDOC',
        group_count: 0
      }).then(() => {
        this.topicLoading = false;
        this.subtopicLoading = false;
        this.groupActivityLoading = false;
        this.alertMutation({ 
          show: true, 
          text: "Group Activity Successfully Created!",
          type: "success" });
      }).catch(() => {
        this.topicLoading = false;
        this.subtopicLoading = false;
        this.groupActivityLoading = false;
        this.alertMutation({ 
          show: true, 
          text: "Something went wrong",
          type: "error" });
      })
    },

    addAssessment(data) {
      if(data.hasOwnProperty('is_topic')) {
        if(data.is_topic){
          this.topicLoading = true
        } else {
          this.subtopicLoading =  true
        }
      } else {
        this.assessmentLoading = true
      }

      this.addAssessmentAction({ 
        course_id: this.course.id,
        module_id: data.module_id,
        is_topic: data.is_topic,
        topic_or_sub_topic_id: data.topic_or_subtopic_id,
        title: data.title,
        type_of_assessment: 'graded_assessment',
        require_checking: false
       }).then(() => {
        this.topicLoading = false;
        this.subtopicLoading = false;
        this.assessmentLoading = false;
        this.alertMutation({ 
          show: true, 
          text: "Assessment Successfully Created!",
          type: "success" });
      }).catch(() => {
        this.topicLoading = false;
        this.subtopicLoading = false;
        this.assessmentLoading = false;
        this.alertMutation({ 
          show: true, 
          text: "Something went wrong",
          type: "error" });
      })
    },

    saveAssessment() {
      this.loadingSave =  true
      if(this.selectedAssessment.max_attempt == null) {
        this.selectedAssessment.max_attempt = 1;
      }
      this.updateAssessmentAction(this.selectedAssessment).then(()=>{
        this.loadingSave = false
        this.alertMutation({ 
          show: true, 
          text: "Assessment Successfully Saved!",
          type: "success" });
      })
    },

    closeAssessment() {
      let index = this.e_learning_tabs.findIndex((i) => i === 'Assessment')
      if(index !== -1) {
        this.e_learning_tabs = this.e_learning_tabs.filter((item) => item !== 'Assessment')
        this.blended_tabs = this.blended_tabs.filter((item) => item !== 'Assessment')
      }
      
      //this.e_learning_tabs.pop()
      this.selectedAssessment = null
      // this.tab = 1
      if(this.course.type_of_learning === 'BLENDED') { 
        this.tab = this.blended_tabs.findIndex((i) => i === 'Module Editor')
      } else {
        this.tab = this.e_learning_tabs.findIndex((i) => i === 'Module Editor')
      }

      this.alertMutation({ 
        show: true, 
        text: "Assessment Successfully Deleted!",
        type: "success" });
    },

    viewAssessment(data){
      if(this.course.type_of_learning === 'BLENDED') {
        if(!this.blended_tabs.includes('Assessment')) {
          this.blended_tabs.push('Assessment')
        }

        this.tab = this.blended_tabs.findIndex((item) => item === 'Assessment')
      } else {
        if(!this.e_learning_tabs.includes('Assessment')) {
          this.e_learning_tabs.push('Assessment')
        }

        this.tab = this.e_learning_tabs.findIndex((item) => item === 'Assessment')
      }
      // if(this.e_learning_tabs.length === 3) {
      //   this.e_learning_tabs.push('Assessment')
      // } else {
      //   this.e_learning_tabs.splice(3, 1)
      //   this.e_learning_tabs.push('Assessment')
      // }

      this.selectedAssessment = { ...this.assessments.filter(i => i.id == data.id)[0], ...data }
      if(this.selectedAssessment.type_of_assessment === null) {
        this.selectedAssessment.type_of_assessment = 'graded_assessment'
      }
      
      // if(this.course.status === 'PUBLISH') {
        
      //   this.tab = 3
      // } else this.tab = 3
      this.$vuetify.goTo('#course-manager', { offset: 0})
      // console.log(document.getElementById("main-container").scrollTop)
      // document.getElementById("main-container").scrollTop = 0

      this.showTaggedUsersAction({
        course_id: this.selectedAssessment.course_id,
        id: this.selectedAssessment.id,
        state_classes: this.assessment_classes
      }).then(()=>{
        this.getTagClassAssessmentAction({
          id: this.selectedAssessment.id,
        })
      })
    },

    viewGroupActivity(group_activity) {
      if(this.course.type_of_learning === 'BLENDED') {
        if(!this.blended_tabs.includes('Group Activity')) {
          this.blended_tabs.push('Group Activity')
        }

        this.tab = this.blended_tabs.findIndex((item) => item === 'Group Activity')
      } else {
        if(!this.e_learning_tabs.includes('Group Activity')) {
          this.e_learning_tabs.push('Group Activity')
        }

        this.tab = this.e_learning_tabs.findIndex((item) => item === 'Group Activity')
      }

      this.$vuetify.goTo('#course-manager', { offset: 0})
      if(group_activity) {
        this.viewGroupActivityAction( group_activity.id ).then(res => {
          this.selectedGroupActivity = res
          this.groupActivityLoading = false
        }).catch(() => {
          this.groupActivityLoading = false
        })
      }
    },

    viewModuleEvaluation(evaluation) {
      if(this.course.type_of_learning === 'BLENDED') {
        if(!this.blended_tabs.includes('Module Evaluation')) {
          this.blended_tabs.push('Module Evaluation')
        }

        this.tab = this.blended_tabs.findIndex((item) => item === 'Module Evaluation')
      } else {
        if(!this.e_learning_tabs.includes('Module Evaluation')) {
          this.e_learning_tabs.push('Module Evaluation')
        }

        this.tab = this.e_learning_tabs.findIndex((item) => item === 'Module Evaluation')
      }

      this.$vuetify.goTo('#course-manager', { offset: 0})

      this.loadingViewEvaluation = true

      this.$api.get(`instructor/module/evaluations/${evaluation.id}`).then( res => {
        this.selectedModuleEvaluation = res.data.data
        this.evaluationQuestionMutation(res.data.data.module_evaluation_questions)
        this.loadingViewEvaluation = false
      })
    },

    viewCourseEvaluation(evaluation) {
      if(this.course.type_of_learning === 'BLENDED') {
        if(!this.blended_tabs.includes('Course Evaluation')) {
          this.blended_tabs.push('Course Evaluation')
        }

        this.tab = this.blended_tabs.findIndex((item) => item === 'Course Evaluation')
      } else {
        if(!this.e_learning_tabs.includes('Course Evaluation')) {
          this.e_learning_tabs.push('Course Evaluation')
        }

        this.tab = this.e_learning_tabs.findIndex((item) => item === 'Course Evaluation')
      }

      this.$vuetify.goTo('#course-manager', { offset: 0})

      this.loadingViewEvaluation = true

      this.$api.get(`instructor/course/evaluations/${evaluation.id}`).then( res => {
        this.selectedCourseEvaluation = res.data.data
        this.evaluationQuestionMutation(res.data.data.course_evaluation_questions)
        this.loadingViewEvaluation = false
      })
    }
  },
  beforeRouteLeave(to, from, next) {
    if (this.saved) return next();
    this.dialog = true;
    this.next = next;
  },
};
</script>
