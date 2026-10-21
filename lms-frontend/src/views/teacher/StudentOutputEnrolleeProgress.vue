<template>
    <section class="mx-auto">
        <Breadcrumb
            :links="[
                {
                    label: `<  Student Outputs`,
                    link: true,
                    route: { name: 'Instructor Student Outputs', query: { search: '', page: 1, paginate: 10, classes: '' } },
                },
                {
                    label: `${progress.title}`,
                    link: true,
                    route: (prevRoute && prevRoute.name=== 'Instructor Student Outputs Enrollees') ? { name: 'Instructor Student Outputs Enrollees',  params: { course_uuid: this.$route.params.course_uuid}, query: { ...prevRoute.query} } : { name: 'Instructor Student Outputs Enrollees',  params: { course_uuid: this.$route.params.course_uuid}, query: { search: '', page: 1, paginate: 10 } },

                },
                {
                    label: `${ user.first_name ? user.first_name : '' }`,
                    link: false,
                    route: null,
                },
            ]"
        />

        <v-sheet class="custom-border border pa-5 mb-3">
            <LinearProgress v-if="loading1" />
            <section class="fade" v-else-if="user && !loading1">
                <section class="d-flex flex-row my-2">
                    <v-avatar color="secondary-5" size="60" id="avatar-profile" style="border: 3px solid" class="fade mr-2">
                        <img
                            alt="Avatar"
                            :src="user.image ? user.image.url : require('@/assets/default-photo.png')"
                            v-on:error="require('@/assets/default-photo.png')"
                        >
                    </v-avatar>
                    <div class="fw500 mt-3">
                        {{ user.first_name ? user.first_name : '' }} {{ user.middle_name ? $userFormat.middle_initial(user.middle_name) : '' }} {{ user.last_name ? user.last_name : '' }} {{ user.suffix ? user.suffix : '' }}
                        <div class="secondary-2--text f12">
                            <v-icon small>
                                mdi-email-outline
                            </v-icon>
                            {{ user.email }}
                        </div>
                    </div>
                </section>
                <v-divider />
                <v-row class="my-2" dense>
                    <v-col cols="12" md="5" lg="5">
                        <FormLabel :label="'COURSE'" />
                        <div class="poppins f15 fw500">
                            {{ progress.title }}
                        </div>
                    </v-col>
                    <v-col cols="6" md="2" lg="2">
                        <FormLabel :label="'FINAL GRADE'" />
                        <div class="poppins f15 fw500" v-if="progress.assessments_count === 0">
                            N/A
                        </div>
                        <div class="poppins f15 fw500" v-else>
                            {{  getUserGrade(score, assessment_questions_sum) }}% ({{ score  }}/{{assessment_questions_sum}})
                        </div>
                    </v-col>
                    <v-col cols="6" md="2" lg="2">
                        <FormLabel :label="'TIME SPENT'" />
                        <div class="poppins f15 fw500" v-if="progress.total_time_spent === 0">
                            -
                        </div>
                        <div class="poppins f15 fw500" v-else>
                            {{  $dateFormat.duration(progress.total_time_spent) }} 
                        </div>
                    </v-col>
                    <v-col cols="12" md="4" lg="4">
                        <FormLabel :label="'PROGRESS'" />
                        <section class="d-flex flex-row align-center">
                            <v-progress-linear 
                                height="8"
                                :value="loading1 ? 0 :getProgress"
                                rounded
                                color="primary"
                                background-color="grey lighten-3"
                                class="mr-2"
                                style="width: 85%"
                                :indeterminate="loading5"
                            >
                            </v-progress-linear>
                            <div class="d-flex flex-row caption">
                                {{ loading5 ? 0 :getProgress }}%
                            </div>
                        </section>
                    </v-col>
                </v-row>
            </section>
        </v-sheet>

        <div class="d-flex flex-row">
            <v-spacer />
            <ButtonPrimary
                :outlined="true"
                :label="'Generate Certificate'"
                :icon="'mdi-certificate-outline'"
                :loading="certificateLoading"
                @click="generateCertificate"
                class="mb-3 ml-auto"
            />
        </div>

        <v-sheet class="custom-border border pa-5 mb-3" v-if="user && !loading1">
            <v-expansion-panels class="fade" accordion flat v-model="expand" multiple>
                <LinearProgress v-if="loading2 &&  progress.type_of_learning === 'BLENDED'" />
                <v-expansion-panel v-if="!loading2 &&  progress.type_of_learning === 'BLENDED'">
                    <ZoomMeetingsList :completed="progress.total_attended_zoom_meetings === 0 ? false :progress.zoom_meetings_count === progress.total_attended_zoom_meetings"/>
                </v-expansion-panel>
                <LinearProgress v-if="loading3" />
                <v-expansion-panel v-if="!loading3"  v-for="_module in modules" :key="_module.id">
                    <ModuleContentList :module="_module" @fetchModules="getEnrolleeProgress()"/>
                </v-expansion-panel>
                <v-expansion-panel v-if="course_evaluations.length > 0">
                    <CourseEvaluationsList :completed="progress.total_done_course_evaluations === 0 ? false :progress.course_evaluations_count === progress.total_done_course_evaluations"/>
                </v-expansion-panel>
            </v-expansion-panels>

        </v-sheet>

        <FormLabel :label="'Enrolled Courses'" class="mb-2" />

        <section class="d-flex flex-wrap justify-space-between">
            
            <SearchBar 
                :placeholder="'Search course'" 
                :value.sync="search"
                @search="() => { page = 1, getOtherEnrolledCoursesData() }"
                @clear="() => { search = '', page = 1, getOtherEnrolledCoursesData() }"
                class="mb-3"/>

            <!-- <ButtonExport 
                @click="exportEnrollments"
                :loading="exportLoading"
                :disabled="courses.length === 0 || loading4"
                class="ma-1 ml-auto mb-3"/> -->
        </section>

        <v-data-table
            :loading="loading4"
            :headers="enrolled_courses_tbl"
            :items="courses"
            :items-per-page.sync="itemsPerPage"
            class="elevation-1 poppins custom-border border"
            hide-default-footer
        >
            <template v-slot:item.final_grades="{ item }">
                <section>
                    {{  item.assessments_count === 0 ? 'N/A' : `${getUserGrade(item.score, item.assessment_questions_sum)}%` }}
                </section>
            </template>

            <template v-slot:item.progress="{ item }">
                <section class="d-flex flex-row align-center">
                    <v-progress-linear 
                        height="8"
                        :value="loading4 ? 0 : getCourseProgress(item)"
                        rounded
                        color="primary"
                        background-color="grey lighten-3"
                        class="mr-2"
                        style="width: 80%"
                    >
                    </v-progress-linear>
                    <div class="d-flex flex-row caption">
                        {{ loading4 ? 0 : getCourseProgress(item) }}%
                    </div>
                </section>
            </template>
        </v-data-table>

        <FormPagination 
            :pageCount="pageCount" 
            :page="page"
            :paginate="paginate"
            @page="(e) => { page = e, getOtherEnrolledCoursesData() }" 
            @paginate="(e) => { paginate = e, page = 1, getOtherEnrolledCoursesData() }"/>

        <CertificateDialog 
          :dialog="certificateDialog"
          :loading="certificateLoading"
          :url="certificateURL"
          @close="certificateDialog = false"
        />

    </section>
</template>

<script>
import { mapActions, mapMutations, mapState } from "vuex";
import Breadcrumb from "@/layouts/teacher/InstructorBreadcrumbs.vue";
import ZoomMeetingsList from "@/components/teacher/course/ZoomMeetingsList"
import ModuleContentList from "@/components/teacher/course/ModuleContentList"
import CourseEvaluationsList from "@/components/teacher/course/CourseEvaluationsList"
import { enrolled_courses_tbl } from "@/constants/tblheaders/studentprogress"
import CertificateDialog from "@/components/main/CertificateDialog.vue";

export default {
    components: {
        Breadcrumb,
        ZoomMeetingsList,
        ModuleContentList,
        CourseEvaluationsList,
        CertificateDialog
    },
    data: () => ({
        title: '',
        loading1: false,
        loading2: false,
        loading3: false,
        loading4: false,
        loading5: false,
        exportLoading: false, 
        pageCount: 1,
        page: 1,
        paginate: '10',
        search: '',
        expand: [],
        user: {
            first_name: '-'
        },
        progress: {
            title: '-',
            total_done_topics: 0,
            total_done_sub_topics: 0,
            total_done_assessments: 0,
            assessments_count: 0,
            topics_count: 0,
            sub_topics_count: 0,
            total_done_course_evaluations: 0,
            total_done_module_evaluations: 0,
            course_evaluations_count: 0,
            module_evaluations_count: 0,
            total_attended_zoom_meetings: 0,
            zoom_meetings_count: 0,
            group_activities_count: 0,
            total_done_group_activities: 0
        },
        enrolled_courses_tbl,
        assessment_questions_sum: 0,
        score: 0,
        prevRoute: null,
        certificateLoading: false,
        certificateDialog: false,
        certificateURL: null,
    }),

    created() {
        this.getUserData()
    },

    methods: {
        ...mapActions('instructor', [
            'getEnrolleeProgressAction',
            'getEnrolleeZoomMeetingProgressAction',
            'getEnrolleeCourseProgressAction',
            'getEnrolleeOtherCoursesProgressAction',
            'generateCertificateAction'
        ]),
        ...mapMutations(['alertMutation']),

        getEnrolleeProgress(){
            this.loading5 = true
            this.getEnrolleeProgressAction({ course_uuid: this.$route.params.course_uuid, user_id: this.$route.params.user_id}).then(res => {
                this.progress = { ...this.progress, ...res.progress}
                this.score = res.score
                if(this.progress.type_of_learning === 'BLENDED') {
                    this.getZoomMeetingProgressData()
                } else {
                    this.getCourseData()
                }
            }).catch(e => {
                this.alertMutation({
                    show: true,
                    text: 'Something went wrong.',
                    type: "error"
                })
            }).finally(() => {
                this.loading5 = false
            })
        },

        getUserData(){
            this.loading1 = true
            this.getEnrolleeProgressAction({ course_uuid: this.$route.params.course_uuid, user_id: this.$route.params.user_id}).then(res => {
                this.user = res.user[0]
                this.progress = { ...this.progress, ...res.progress}
                this.assessment_questions_sum = res.assessment_questions_sum
                this.score = res.score
                if(this.progress.type_of_learning === 'BLENDED') {
                    this.getZoomMeetingProgressData()
                } else {
                    this.getCourseData()
                }
            }).catch(e => {
                this.user = null
                this.alertMutation({
                    show: true,
                    text: 'Something went wrong.',
                    type: "error"
                })
            }).finally(() => {
                this.loading1 = false
            })
        },

        getZoomMeetingProgressData(){
            this.loading2 = true

            this.getEnrolleeZoomMeetingProgressAction({ course_uuid: this.$route.params.course_uuid, user_id: this.$route.params.user_id}).then(() => {
                this.getCourseData()
            }).catch(e => {
                this.alertMutation({
                    show: true,
                    text: 'Something went wrong.',
                    type: "error"
                })
            }).finally(() => {
                this.loading2 = false
            })
        },

        getCourseData(){
            this.loading3 = true
            this.getEnrolleeCourseProgressAction({ course_uuid: this.$route.params.course_uuid, user_id: this.$route.params.user_id}).then(() => {
                this.getOtherEnrolledCoursesData()
            }).catch(e => {
                this.alertMutation({
                    show: true,
                    text: 'Something went wrong.',
                    type: "error"
                })
            }).finally(() => {
                this.loading3 = false
            })
        },

        getOtherEnrolledCoursesData(){
            this.loading4 = true
            this.getEnrolleeOtherCoursesProgressAction({ user_id: this.$route.params.user_id, paginate: Number(this.paginate), page: this.page, search: this.search }).then(res => {
                this.page = res.current_page
                this.pageCount = res.last_page
                this.paginate = String(res.per_page)
            }).catch(e => {
                this.alertMutation({
                    show: true,
                    text: 'Something went wrong.',
                    type: "error"
                })
            }).finally(() => {
                this.loading4 = false
            })
        },

        getCourseProgress(item){
            let _progress = 0

            if(item.type_of_learning === 'BLENDED'){
                _progress = this.$userProgressBlended(item)
            } else {
                _progress = this.$userProgressElearning(item)
            }

            return Number.isNaN(_progress) ? 0 :_progress
        },

        getUserGrade(score, total){
            let _grade = 0

            _grade = (score / total) * 100

            return Number.isNaN(_grade) ? 0 :_grade.toFixed(2)
        },

        generateCertificate(){
            this.certificateLoading = true
            this.generateCertificateAction({ course_uuid: this.$route.params.course_uuid, user_id: this.$route.params.user_id}).then(res => {
                this.certificateDialog = true
                this.certificateLoading = false
                this.certificateURL = res
            }).catch(res => {
                this.certificateLoading = false
                this.alertMutation({
                    show: true,
                    text: 'Certificate generation is currently unavailable.',
                    type: "error"
                })
            })
        }

    },

    watch:{
        $route (to, from){
            this.getUserData()
        }
    },

    computed: {
        ...mapState('instructor', {
            meetings: (state) => state.meetings,
            modules: (state) => state.modules,
            course_evaluations: (state) => state.course_evaluations,
            courses: (state) => state.courses,
        }),

        itemsPerPage(){
            return Number(this.paginate)
        },

        getProgress(){
            let _progress = 0

            if(this.progress.type_of_learning === 'BLENDED'){
                _progress = this.$userProgressBlended(this.progress)
            } else {
                _progress = this.$userProgressElearning(this.progress)
            }

            return Number.isNaN(_progress) ? 0 :_progress
            
        },
    },
  
    beforeRouteEnter(to, from, next) {
        next(vm => {
            vm.prevRoute = from          
        })
    },
}
</script>