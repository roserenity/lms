<template>
    <section>
        <section class="d-flex flex-row mb-1" v-if="!start && !viewSubmitted" >
            <v-btn 
                text 
                color="primary" 
                dense 
                small
                class="px-1" 
                @click="$router.push({ path: `/course/${$route.params.id}`, query: { timestamp: Date.now() } })">
                <v-icon small>
                mdi-chevron-left
                </v-icon>
                BACK
            </v-btn>
            <v-spacer/>
            <ButtonPrimary
                :label="'Generate Certificate'"
                :icon="'mdi-certificate-outline'"
                :loading="certificateLoading"
                :disabled="!(getProgress === 100 && course.is_certificate_open)"
                class="hidden-sm-and-down mb-2"
                @click="generateCertificate"
            />
        </section>

        <LinearProgress v-if="pageLoading" />
        
        <v-row dense v-if="!start && !viewSubmitted">
            <v-col cols="12" md="4" class="order-last order-md-first">
                <v-sheet class="custom-border border pa-4 fade">
                    <div class="secondary-font f12 fw600 secondary-1--text no-spacing my-2 ">
                        LEARNING PROGRESS ( {{ progressLoading ? 0 :getProgress }} % )
                        <section class="d-flex flex-row align-center secondary-1--text mt-2">
                            <v-progress-linear 
                                height="8"
                                :value=" progressLoading ? 0 :getProgress"
                                rounded
                                color="success"
                                background-color="grey lighten-3"
                                class="mr-2"
                                :indeterminate="progressLoading"
                            ></v-progress-linear>
                        </section>
                    </div>
                    <!-- <v-alert color="#B5221033" dense class="f10 error--text"> We are currently unable to generate certificates at this time. We apologize for any inconvenience this may cause. Thank you for your understanding. </v-alert> -->

                    <v-sheet class="overflow-y-auto scroller" max-height="310" min-height="42vh"  id="parent">
                        <MaterialTimelineFix
                            elevation='0'
                            :course="course" 
                            id="materialTimeline"
                            @click="(e) => goTo(e)"
                            :loading="savingProgressLoading"
                            :current="current_content ? current_content.uuid : ''"/>
                    </v-sheet>
                </v-sheet>
            </v-col>
            <v-col cols="12" md="8" class="py-1">
                <v-sheet class="custom-border border fade" :min-height="['course_evaluation', 'module_evaluation', 'assessment'].includes(current_content_type) ? `54vh` : ''">
                    <StartScreenEvaluation 
                        v-if="!pageLoading && ['course_evaluation', 'module_evaluation'].includes(current_content_type)"
                        @start="() => { start = true, startTimerOther()}" 
                        :evaluation="current_content"
                        :current_content_type="current_content_type"
                        @viewSubmission="viewSubmitted = true" />
                    
                    <StartScreen 
                        v-if="!pageLoading && ['assessment'].includes(current_content_type)"
                        @setAnswers="(e) => answers = e"
                        :data="current_content"
                        @viewAccepted="viewSubmitted = true"
                        @start="() => {
                            start = true
                            $store.commit('usr/startAssessmentMutation', {
                                action: 'start',
                                status: true
                            })
                            startTimerOther()
                        }" />

                    <section
                        v-if="!pageLoading && ['topic', 'subtopic'].includes(current_content_type) && current_content.learning_material">
                        <div class="d-flex flex-row align-center justify-end pa-3">
                                <v-btn :loading="downloadLoading" @click="download()" dense small class="mx-1 primary ml-auto" flat v-if="['ppt', 'pptx', 'docx', 'png', 'jpg', 'jpeg', 'pdf', 'mp4'].includes(current_content.learning_material.file_type) && current_content.learning_material.is_downloadable" >
                                    Download
                                </v-btn>
                        </div>
                        <vimeo v-if="['mp4', 'youtube'].includes(current_content.learning_material.file_type) && showVimeo"
                            :uri="current_content.learning_material.url"
                            :type="current_content.learning_material.file_type"
                            :data="current_content"
                            @start="startTimerOther()"
                            @end="saveTopicSubtopicProgress('DONE')"
                            class="fade"
                        />
                        <div id="container-frame" class="container-frame fade" v-else>
                            <div class="d-flex flex-row align-center justify-end ma-3">
                                <div>
                                    <v-btn dense small class="primary ml-auto" flat @click="autoDoneProgress()" v-if="['ppt', 'pptx', 'docx', 'png', 'jpg', 'jpeg', 'pdf', 'gdrive_link'].includes(current_content.learning_material.file_type) && !current_content.student_done_status">
                                        Mark as completed
                                    </v-btn>
                                </div>
                                <div v-if="['scorm_file'].includes(current_content.learning_material.file_type) && current_content.scorm"  class="float-right d-flex flex-row align-center" >
                                    <v-btn dense small color="primary" flat @click="checkScormCompletion" :loading="checkScormLoading">
                                        Check Scorm Completion
                                    </v-btn>
                                    <v-btn icon @click="toggleFullScreen">
                                        <v-icon>
                                            mdi-fullscreen
                                        </v-icon>
                                    </v-btn>
                                </div>
                            </div>
                            <iframe 
                                v-if="['ppt', 'pptx', 'docx'].includes(current_content.learning_material.file_type)"
                                id="material-frame"
                                :src="`https://view.officeapps.live.com/op/embed.aspx?src=${current_content.learning_material.url}`"
                                class="responsive-iframe"
                                allowfullscreen
                                webkitallowfullscreen="true"
                                mozallowfullscreen="true"
                                loading="lazy"
                            ></iframe>
                            <v-img 
                                v-else-if="['png', 'jpg', 'jpeg'].includes(current_content.learning_material.file_type)"
                                id="material-iframe"
                                class="fade"
                                :src="current_content.learning_material.url"
                            />
                            <div v-else-if="['scorm_file'].includes(current_content.learning_material.file_type)">
                                <iframe 
                                    v-if="current_content.scorm"
                                    id="material-iframe"
                                    :src="current_content.scorm.my_registration.launch_link"
                                    class="responsive-iframe"
                                    allowfullscreen
                                    webkitallowfullscreen="true"
                                    mozallowfullscreen="true"
                                    frame-border="none"
                                    loading="lazy"
                                ></iframe>
                                <scorm
                                    v-else
                                    :url="current_content.learning_material.url"
                                    @end="saveTopicSubtopicProgress('DONE')"
                                    :course_id="current_content.course_id"
                                    :type="current_content.hasOwnProperty('topic_has_assessments') ? 'topic' : 'subtopic'"
                                    :model_id="current_content.id"
                                    :progress="current_content.student_scorm_progress"
                                />
                                 
                            </div>
                            <iframe 
                                v-else
                                id="material-iframe"
                                :src="current_content.learning_material.url"
                                class="responsive-iframe fade"
                                allowfullscreen
                                webkitallowfullscreen="true"
                                mozallowfullscreen="true"
                            /> 
                        </div>
                    </section>
                    
                    <section
                        v-if="!pageLoading && ['group_activity'].includes(current_content_type)">
                        <iframe 
                            id="material-iframe"
                            v-if="current_content.group_activity_groups.length > 0"
                            :src="current_content.group_activity_groups[0].file_url"
                            class="responsive-iframe"
                            allowfullscreen
                            webkitallowfullscreen="true"
                            mozallowfullscreen="true"
                            frame-border="none"
                            loading="lazy"
                         />

                        <v-sheet height="50vh" class="d-flex align-center justify-center" v-else-if="current_content.group_activity_groups.length == 0 || (current_content.group_activity_groups.length > 0 && current_content.group_activity_groups[0].file_url)">
                            No file found. Contact your course administrator.
                        </v-sheet>   
                    </section>

                    <v-sheet height="50vh" class="d-flex align-center justify-center" v-else-if="!pageLoading && ['topic', 'subtopic'].includes(current_content_type) && !current_content.learning_material">
                        No learning material found
                    </v-sheet>   
                </v-sheet>

                <div v-if="current_content && current_content.scorm_activities && current_content.scorm_activities.length > 0" class="f12 my-5">
                    <FormLabel :label="'Submitted Scorm Activities: '"/>
                    <v-expansion-panels accordion class="custom-border border fade mt-3" flat>
                        <v-expansion-panel v-for="(activity, i) in current_content.scorm_activities" :key="i">
                            <v-expansion-panel-header dense><b>{{ activity.title }}</b></v-expansion-panel-header>
                            <v-expansion-panel-content dense>
                                <div v-for="(item, j) in activity.my_scorm_answers" :key="j">
                                    <v-row dense>
                                        <v-col cols="9" md="10">
                                            <div class="question-div roboto f15 secondary-1--text mb-4" v-html="item.scorm_question.question"/>
                                        </v-col>
                                        <v-col cols="3" md="2" class="ml-auto text-center"  v-if="item.scorm_question.type_of_question !== 'QA_SECTION'">
                                            <v-alert dense small :color="item.is_correct ? '#7BC14533' : '#F8835E33'" class="ma-auto f12" :class="item.is_correct ? 'success--text' : 'error--text'">
                                                {{ item.is_correct ? 'CORRECT' : 'WRONG' }}
                                            </v-alert>
                                        </v-col>
                                    </v-row>
                                    
                                    <RadioButtons class="f12 pa-0" :preview="true" v-if="item.scorm_question.type_of_question === 'MULTIPLE_CHOICE'" :choices="getChoices(item.scorm_question.choices)" :answer="item.answer ? getAnswer(item.answer) : ''" />
                                    <Checkboxes class="f12 pa-0" :preview="true" v-else-if="item.scorm_question.type_of_question === 'MULTIPLE_RESPONSE'" :choices="getChoices(item.scorm_question.choices)" :answer="item.answer ? getAnswerMultiple(item.answer) : []" />          
                                    <div class="f12 col-md-8 col-12 pa-0" v-else-if="['FILL_IN_THE_BLANK', 'QA_SECTION'].includes(item.scorm_question.type_of_question)">
                                        <v-textarea class="poppins" outlined dense hide-details auto-grow readonly :value="item.answer ? getAnswer(item.answer) : ''"></v-textarea>
                                    </div>
                                    <MatchingType class="f12 pa-0" v-else-if="item.scorm_question.type_of_question === 'MATCHING'" :matchingPairs="item.answer ? getAnswer(item.answer) : []"/>
                                    
                                    <section v-if="item.scorm_question.type_of_question !== 'QA_SECTION'" class="mt-3">
                                        <FormLabel :label="item.scorm_question.type_of_question !== 'FILL_IN_THE_BLANK' ? 'CORRECT ANSWER:' : 'ACCEPTED ANSWERS:'" />
                                        <v-sheet class="rounded-lg pa-2 mb-5 f14" color="dark-1">
                                        <span v-if="['MULTIPLE_CHOICE', 'MULTIPLE_RESPONSE'].includes(item.scorm_question.type_of_question)">
                                            {{ getCorrectAnswer(item.scorm_question.correct_answers) }}
                                        </span>
                                        <span v-else-if="item.scorm_question.type_of_question !== 'MATCHING'">
                                            {{item.scorm_question.correct_answers.join(', ') }}
                                        </span>
                                        <MatchingType class="f12" v-else :matchingPairs="item.scorm_question.correct_answers"/>
                                        </v-sheet>
                                    </section>

                                    <v-divider class="my-8" v-if="j !== activity.my_scorm_answers.length - 1"/>
                                </div>
                            </v-expansion-panel-content>
                        </v-expansion-panel>
                    </v-expansion-panels>
                </div>

            </v-col>
        </v-row>

        <Quiz 
            v-if="start && ['assessment'].includes(current_content_type)"
            :data="current_content" 
            :answers="answers" 
            @submit="submitDialog = true" 
            @back="() => {
                leaveDialog = {status: true, action: 'leave'}
                $store.commit('usr/startAssessmentMutation', {
                    action: 'left',
                    status: false
                })
            }" 
        />

        <LatestSubmission 
            v-if="viewSubmitted && ['assessment'].includes(current_content_type)"
            :data="current_content"
            @backSubmitted="viewSubmitted = false"/>


        <DialogSuccess
            :dialog="successDialog"
            :type="current_content_type === 'assessment' ? 'assessment' : 'evaluation'"
        />

        <DialogLeave
            :dialog="leaveDialog" 
            @submit="() => { leave(), saveTopicSubtopicProgress(null), stopTimer()  }"
            @cancel="leaveDialog.status=false"
        />

        <DialogSubmit 
            :dialog="submitDialog" 
            :loading="submitLoading"
            @submit="proceedSubmit"
            @cancel="submitDialog=false"
            @review="submitDialog=false"
        />

        <Evaluation 
            v-if="start && ['course_evaluation', 'module_evaluation'].includes(current_content_type)" 
            :evaluation="current_content"
            :current_content_type="current_content_type"
            @back="() => { leaveDialog = {status: true, action: 'leave'}, saveTopicSubtopicProgress(null), stopTimer() }" 
            @submit="(e) => confirmEvaluation(e)"/>
        
        <LatestEvaluation 
            v-if="viewSubmitted && ['course_evaluation', 'module_evaluation'].includes(current_content_type)"
            :evaluation="current_content"
            :current_content_type="current_content_type"
            @back = "viewSubmitted = false"/>
        
            
        <CertificateDialog 
            :dialog="certificateDialog"
            :loading="certificateLoading"
            :url="certificateURL"
            @close="certificateDialog = false"
            />

    </section>
</template>


<style>
    .container-frame {
        position: relative;
        overflow: hidden;
        width: 100%; /* 16:9 Aspect Ratio (divide 9 by 16 = 0.5625) */
    }

    .responsive-iframe {
        height: 74vh;
    }

    @media only screen and (max-width: 768px){
        .responsive-iframe {
            height: 74vh;
            width: 100%;
        }
    }

</style>

<script>
import { mapActions, mapMutations, mapState } from "vuex";
import Quiz from './Quiz.vue';
import StartScreen from '@/components/user/assessment/StartScreen.vue';
import Evaluation from './Evaluation.vue';
import LatestSubmission from './LatestSubmission.vue';
import MaterialTimelineFix from '@/components/user/course/MaterialTimelineFix.vue';
import DialogSubmit from '@/components/user/course/DialogSubmit.vue';
import DialogLeave from '@/components/user/course/DialogLeave.vue';
import DialogSuccess from '@/components/user/course/DialogSuccess.vue';
import StartScreenEvaluation from '@/components/user/evaluation/StartScreen.vue';
import LatestEvaluation from '@/components/user/evaluation/LatestEvaluation.vue';
import CertificateDialog from '@/components/main/CertificateDialog.vue';
import RadioButtons from '@/components/teacher/evaluation/RadioButtons.vue';
import Checkboxes from '@/components/teacher/evaluation/Checkboxes.vue';
import MatchingType from '@/components/teacher/evaluation/MatchingType.vue';

export default {

    components: {
        StartScreen,
        Quiz,
        LatestSubmission,
        MaterialTimelineFix,
        StartScreenEvaluation,
        LatestEvaluation,
        Evaluation,
        DialogSubmit,
        DialogLeave,
        DialogSuccess,
        CertificateDialog,
        RadioButtons,
        Checkboxes,
        MatchingType
    },

    data: () => ({
        showVimeo: false,
        review: false,
        start: false,
        answers: null,
        viewSubmitted: false,
        successDialog: false,
        submitDialog: false,
        leaveDialog: {
            status: false,
            action: ''
        },
        postType: false,
        pageLoading: false,
        submitLoading: false,
        certificateLoading: false,
        savingProgressLoading: false,
        progressLoading: false,
        current_content: null,
        current_content_type: '',
        downloadLoading: false,
        certificateLoading: false,
        certificateDialog: false,
        certificateURL: null,
        progress: {
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
            zoom_meetings_count: 0
        },
        prevRoute: null,
        checkScormLoading: false,
        timeSpent: 0,
        interval: null,
        saveInterval: null,
    }),

    created(){
        document.addEventListener('fullscreenchange', this.handleFullscreenChange);
        document.addEventListener("visibilitychange", this.handleVisibilityChange);
        window.addEventListener("beforeunload", this.stopTimer);

        this.getCourse()
        this.getCourseProgress()
    },

    beforeUnmount() {
        document.removeEventListener('fullscreenchange', this.handleFullscreenChange);
        document.removeEventListener("visibilitychange", this.handleVisibilityChange);
        window.removeEventListener("beforeunload", this.stopTimer);
        this.stopTimer();
    },

    computed: {
        ...mapState('usr', {
            course: (state) => state.object,
            modules: (state) => state.modules,
            elevations: (state) => state.elevations,
            content: (state) => state.content,
            startAssessment: (state) => state.startAssessment,
        }),

        getProgress(){
            let _progress = 0
            if(this.course.type_of_learning === 'BLENDED'){
                _progress = this.$userProgressBlended(this.progress)
            } else {
                _progress = this.$userProgressElearning(this.progress)
            }

            if(this.course && !this.course.is_completed && _progress === 100 && !this.progressLoading && !this.pageLoading) {
                this.pageLoading = true
                this.markCourseAsCompletedAction({ course_id: this.course.id }).then(() => {
                    this.course.is_completed = true
                    this.pageLoading = false
                    this.alertMutation({
                        show: true,
                        text: 'Course Completed!',
                        type: "success"
                    })
                }).catch(() => {
                    this.pageLoading = false
                    this.alertMutation({
                        show: true,
                        text: 'Something went wrong in saving your progress!',
                        type: "error"
                    })
                })
            }

            return Number.isNaN(_progress) ? 0 :_progress
        },
    },

    methods: {
        ...mapMutations(['alertMutation']),
        ...mapMutations('usr', ['modulesMutation', 'contentMutation']),

        ...mapActions('usr', [
            'getScormRegistrationProgressAction',
            'generateCertificateAction',
            'showCourseAction',
            'getModuleContentAction',
            'getCourseProgressAction',
            'saveDoneProgressAction',
            'submitCourseEvaluationAnswersAction',
            'submitModuleEvaluationAnswersAction',
            'storeAssessmentAnswersAction',
            'getAssessmentAction',
            'markCourseAsCompletedAction',
            'getCourseEvaluationsAction',
            'getScormProgressAction',
            'saveTimeSpentAction',
            'downloadCourseLearningMaterialAction'
        ]),

        startTimerOther(){
            this.interval = setInterval(() => {
                    this.timeSpent++;
                    if(this.current_content.hasOwnProperty('student_topic_progress')) {
                        this.current_content.student_topic_progress.time_spent++;
                    }
                    if(this.current_content.hasOwnProperty('student_sub_topic_progress')) {
                        this.current_content.student_sub_topic_progress.time_spent++;
                    }
                    if(this.current_content.hasOwnProperty('student_assessment_progress')) {
                        this.current_content.student_assessment_progress.time_spent++;
                    }
                    if(this.current_content.hasOwnProperty('student_course_evaluation_progress')) {
                        this.current_content.student_course_evaluation_progress.time_spent++;
                    }
                    if(this.current_content.hasOwnProperty('student_module_evaluation_progress')) {
                        this.current_content.student_module_evaluation_progress.time_spent++;
                    }
                }, 1000);
        },

        startTimer() {
            if(this.current_content && this.current_content.learning_material && !['mp4', 'youtube'].includes(this.current_content.learning_material.file_type) && !this.showVimeo && !['course_evaluation', 'module_evaluation', 'assessment'].includes(this.current_content_type)) {
                    this.interval = setInterval(() => {
                    this.timeSpent++;

                    if(this.current_content.hasOwnProperty('student_topic_progress')) {
                        this.current_content.student_topic_progress.time_spent++;
                    }
                    if(this.current_content.hasOwnProperty('student_sub_topic_progress')) {
                        this.current_content.student_sub_topic_progress.time_spent++;
                    }
                    if(this.current_content.hasOwnProperty('student_assessment_progress')) {
                        this.current_content.student_assessment_progress.time_spent++;
                    }
                    if(this.current_content.hasOwnProperty('student_course_evaluation_progress')) {
                        this.current_content.student_course_evaluation_progress.time_spent++;
                    }
                    if(this.current_content.hasOwnProperty('student_module_evaluation_progress')) {
                        this.current_content.student_module_evaluation_progress.time_spent++;
                    }
                }, 1000);
            }
        },

        stopTimer() {
            this.saveTimeSpent();
            if (this.interval) {
                clearInterval(this.interval);
                this.interval = null;
                this.saveInterval = null
            }
        },

        
        handleVisibilityChange() {
            if (document.hidden) {
                this.stopTimer();

            } else {
                if(this.current_content && this.current_content.learning_material && !['mp4', 'youtube'].includes(this.current_content.learning_material.file_type) && !this.showVimeo && !['course_evaluation', 'module_evaluation', 'assessment'].includes(this.current_content_type) && !this.start) {
                    this.startTimer();
                }
                else if (this.current_content && !this.current_content.hasOwnProperty('learning_material') && ['course_evaluation', 'module_evaluation', 'assessment'].includes(this.current_content_type) && this.start) {
                    this.startTimerOther()
                }
            }
        },

        saveTimeSpent() {
            if(this.current_content) {

                let data = null

                if(this.current_content.hasOwnProperty('student_topic_progress')) {
                    data = this.current_content.student_topic_progress
                }
                if(this.current_content.hasOwnProperty('student_sub_topic_progress')) {
                    data = this.current_content.student_sub_topic_progress
                }
                if(this.current_content.hasOwnProperty('student_assessment_progress')) {
                    data = this.current_content.student_assessment_progress
                }
                if(this.current_content.hasOwnProperty('student_course_evaluation_progress')) {
                    data = this.current_content.student_course_evaluation_progress
                }
                if(this.current_content.hasOwnProperty('student_module_evaluation_progress')) {
                    data = this.current_content.student_module_evaluation_progress
                }

                if(data && !this.current_content.student_done_status) {
                    this.saveTimeSpentAction(data).then(() => {
                        this.timeSpent = 0
                    })
                }

            }
        },

        setupAutoSave() {
            this.saveInterval = setInterval(this.saveTimeSpent, 10000); // Save every 10 seconds
        },

        autoDoneProgress(){
            if(this.current_content_type === 'GroupActivity') {
                return
            }
            if(['png', 'jpg', 'jpeg', 'pdf', 'gdrive_link', 'docx', 'ppt', 'pptx',].includes(this.current_content.learning_material.file_type)) {
                if(!this.current_content.student_done_status) this.saveTopicSubtopicProgress('DONE')
            }
        },

        setCurrentContentType() {
            if(this.current_content.hasOwnProperty('topic_has_assessments')) {
                setTimeout(() => {
                    if(this.pageLoading) {
                        this.pageLoading = false
                    }
                    if(['mp4', 'youtube'].includes(this.current_content.learning_material.file_type)) {
                        this.showVimeo = true
                    }
                }, 1000)
                this.current_content_type = 'topic'
                // this.autoDoneProgress()
            }
            if(this.current_content.hasOwnProperty('sub_topic_has_assessments')) {
                setTimeout(() => {
                    if(this.pageLoading) {
                        this.pageLoading = false
                    }
                    if(['mp4', 'youtube'].includes(this.current_content.learning_material.file_type)) {
                        this.showVimeo = true
                    }
                }, 1000)
                this.current_content_type = 'subtopic'

                // this.autoDoneProgress()
            }
            if(this.current_content.hasOwnProperty('course_evaluation_is_locked')) this.current_content_type = 'course_evaluation'
            if(this.current_content.hasOwnProperty('module_evaluation_is_locked')) this.current_content_type = 'module_evaluation'
            if(this.current_content.hasOwnProperty('type_of_assessment')) this.current_content_type = 'assessment'
            if(this.current_content.hasOwnProperty('group_activity_is_locked')) this.current_content_type = 'group_activity'
        },

        goTo(e) {
            this.showVimeo = false
            this.current_content = this.content.find(item => item.uuid === e.uuid)
            console.log(this.current_content)

            if(this.current_content.hasOwnProperty('topic_has_assessments') || this.current_content.hasOwnProperty('sub_topic_has_assessments')) {
                this.pageLoading = true
            }

            this.setCurrentContentType()

            if(this.current_content.hasOwnProperty('file_type')) {
                if(['scorm_file'].includes(this.current_content.learning_material.file_type)) {
                    this.getScormProgressAction({
                        course_id: this.current_content.course_id,
                        model_id: this.current_content.id,
                        model_type: this.current_content_type
                    }).then(res => {
                        this.current_content.student_scorm_progress = res
                    })
                }
            }

            this.timeSpent = 0
            this.saveTopicSubtopicProgress(null)
            this.$router.replace({ params: { id: this.$route.params.id, content_uuid: e.uuid}, timestamp: Date.now()})
        },

        getCourse(){
            this.pageLoading=true
            this.showCourseAction(this.$route.params.id).then(() => {
                this.pageLoading = false

                this.current_content = this.content[this.$route.params.index]

                this.setCurrentContentType()
                // this.autoDoneProgress()

                if(this.current_content_type !== 'group_activity') {
                    this.saveTopicSubtopicProgress(null)
                }

                if(!this.course.is_completed) {
                    this.startTimer();
                }
                
                this.setupAutoSave()
            }).catch(() => {
                this.pageLoading = false
                this.alertMutation({
                    show: true,
                    text: 'Something went wrong',
                    type: "error"
                })
            })
        },

        getCourseProgress(){
            this.progressLoading = true
            this.getCourseProgressAction(this.$route.params.id).then(res => {
                this.progressLoading = false
                this.progress = res
            }).catch(() => {
                this.progressLoading = false
                this.progress = {
                    assessments_count: 0,
                    course_evaluations_count: 0,
                    module_evaluations_count: 0,
                    sub_topics_count: 0,
                    topics_count: 0,
                    total_done_assessments: 0,
                    total_done_course_evaluations: 0,
                    total_done_module_evaluations: 0,
                    total_done_sub_topics: 0,
                    total_done_topics: 0,
                }
                this.alertMutation({
                    show: true,
                    text: 'Something went wrong in fetching your progress',
                    type: "error"
                })
            })
        },

        generateCertificate(){
            this.certificateLoading = true
            this.generateCertificateAction({course_id: this.course.id}).then(res => {
                this.certificateDialog = true
                this.certificateLoading = false
                this.certificateURL = res
            }).catch(err => {
                this.certificateLoading = false
                console.log(err)
                if(err.response?.status === 409) { 
                    this.alertMutation({
                        show: true,
                        text: err.response?.data.message,
                        type: "error"
                    })
                } else {
                    this.alertMutation({
                        show: true,
                        text: 'Certificate generation is currently unavailable.',
                        type: "error"
                    })
                }
            })
        },

        updateStateModules(updated_module){
            let index = this.modules.findIndex(item => item.id === updated_module.id);

            if (index !== -1) {
                this.modules.splice(index, 1, updated_module);
                this.modulesMutation(this.modules)
            }
        },

        showErrorProgressSave(){
            this.alertMutation({
                show: true,
                text: 'Something went wrong in saving your progress, please reload the page',
                type: "error"
            })
        },

        updateNextContent(e) {
            let _index = this.content.findIndex(item => item.uuid === e.uuid)
            let next_content = this.content[_index + 1]

            if(next_content && next_content.hasOwnProperty('module_id')) {
                if(next_content && next_content.module_id !== e.module_id) {
                    if(e.hasOwnProperty('module_id')) {
                        this.getModuleContentAction(e.module_id).then(res => {
                            this.updateStateModules(res)
                            if(next_content.hasOwnProperty('module_id')) {
                                this.getModuleContentAction(next_content.module_id).then(res => {
                                    this.updateStateModules(res)
                                    this.savingProgressLoading = false
                                }).catch(e => {
                                    console.log(e)
                                    this.showErrorProgressSave()
                                    this.savingProgressLoading = false
                                })
                            }
                        }).catch(e => {
                            console.log(e)
                            this.showErrorProgressSave()
                            this.savingProgressLoading = false
                        })
                    }
                } else {
                    if(next_content.hasOwnProperty('module_id')) {
                        this.getModuleContentAction(e.module_id).then(res => {
                            this.updateStateModules(res)
                            this.savingProgressLoading = false
                        }).catch(e => {
                            console.log(e)
                            this.showErrorProgressSave()
                            this.savingProgressLoading = false
                        })
                    }
                }
            }
            
        },

        saveTopicSubtopicProgress(e){
            if(!this.current_content.student_done_status) {
                this.savingProgressLoading = true

                const typeMap = {
                    subtopic: 'SubTopic',
                    topic: 'Topic',
                    course_evaluation: 'CourseEvaluation',
                    module_evaluation: 'ModuleEvaluation',
                    assessment: 'Assessment',
                    group_activity: 'GroupActivity'
                }

                let model_type = typeMap[this.current_content_type] || ""

                if(e) {

                    this.saveDoneProgressAction({
                        model_uuid: this.current_content.uuid, 
                        model_type: model_type,
                        course_id: this.course.id,
                        status: e,
                    }).then(res=>{
                        
                        if(this.current_content.hasOwnProperty('student_topic_progress')) {
                            this.current_content.student_topic_progress = res
                        }
                        if(this.current_content.hasOwnProperty('student_sub_topic_progress')) {
                            this.current_content.student_sub_topic_progress = res
                        }
                        if(this.current_content.hasOwnProperty('student_assessment_progress')) {
                            this.current_content.student_assessment_progress = res
                        }
                        if(this.current_content.hasOwnProperty('student_course_evaluation_progress')) {
                            this.current_content.student_course_evaluation_progress = res
                        }
                        if(this.current_content.hasOwnProperty('student_module_evaluation_progress')) {
                            this.current_content.student_module_evaluation_progress = res
                        }

                        this.current_content.student_done_status = true
                        this.updateNextContent(this.current_content)
                        this.getCourseProgress()
                        this.savingProgressLoading = false
                    }).catch(e => {
                        console.log(e)
                        this.savingProgressLoading = false
                        this.showErrorProgressSave()
                    })
                } else {
                    this.saveDoneProgressAction({
                        model_uuid: this.current_content.uuid, 
                        model_type: model_type,
                        course_id: this.course.id,
                    }).then(res=>{
                        
                        if(this.current_content.hasOwnProperty('student_topic_progress')) {
                            this.current_content.student_topic_progress = res
                        }
                        if(this.current_content.hasOwnProperty('student_sub_topic_progress')) {
                            this.current_content.student_sub_topic_progress = res
                        }
                        if(this.current_content.hasOwnProperty('student_assessment_progress')) {
                            this.current_content.student_assessment_progress = res
                        }
                        if(this.current_content.hasOwnProperty('student_course_evaluation_progress')) {
                            this.current_content.student_course_evaluation_progress = res
                        }
                        if(this.current_content.hasOwnProperty('student_module_evaluation_progress')) {
                            this.current_content.student_module_evaluation_progress = res
                        }
                        this.updateNextContent(this.current_content)
                        this.getCourseProgress()
                        this.savingProgressLoading = false
                    }).catch(e => {
                        console.log(e)
                        this.savingProgressLoading = false
                        this.showErrorProgressSave()
                    })
                }

            }
        },

        proceedSubmit() {
            if(this.current_content_type === 'assessment') {
                this.postType = 'submitted'
                
                this.$store.commit('usr/startAssessmentMutation', {
                    action: 'submitted',
                    status: false
                })
                this.submitAssessment()
            } else this.submitEvaluation()
        },

        submitAssessment() {
            let form = new FormData()

            this.submitLoading = true
            this.savingProgressLoading = true

            form.append('assessment_id', this.current_content.id)
            form.append('post_type', this.startAssessment.action)
            form.append('course_id', this.current_content.course_id)
            form.append('time_spent', this.current_content.student_assessment_progress.time_spent)
            this.answers.forEach((el, i) => {
                form.append('question_id[]', el.question_id)
                form.append('answer[]', el.answer)
                form.append('support_answer[]', el.support_answer)
                if(el.file) {
                    form.append(`file[${i}]`, el.file) 
                } else {
                    form.append(`file[${i}]`, '') 
                }
                form.append('is_require_support_answer[]', el.is_require_support_answer)
                form.append('is_require_file_attachment[]', el.is_require_file_attachment)
            })

            this.storeAssessmentAnswersAction(form).then(() => {
                this.getAssessmentAction(this.current_content.uuid).then(res => {
                    this.current_content = res
                    this.start = false
                    this.review = false
                }).catch(() => {
                    this.start = false
                    this.review = false
                })
                this.getCourseProgress()
                this.submitLoading = false
                this.submitDialog = false
                this.getModuleContentAction(this.current_content.module_id).then(res => {
                    this.updateStateModules(res)
                    this.savingProgressLoading = false
                }).catch(() => {
                    this.savingProgressLoading = false
                    this.showErrorProgressSave()
                })
                this.stopTimer()
            }).catch(() => {
                this.submitLoading = false
                this.submitDialog = false
                this.savingProgressLoading = false
                this.alertMutation({
                    show: true,
                    text: 'Something went wrong',
                    type: "error"
                })
            })
        },

        leave(){
            this.leaveDialog.status=false
            this.start = false
            this.answers = []
        },

        confirmEvaluation(data) {
            this.answers = data
            this.submitDialog = true
        },

        submitEvaluation() {

            let _form = new FormData()

            this.submitLoading = true
            this.savingProgressLoading = true
            if(this.current_content_type === 'course_evaluation') {
                _form.append('time_spent', this.current_content.student_course_evaluation_progress.time_spent)
                this.answers.forEach((item, i) => {
                    _form.append(`course_id[${i}]`, item.id)
                    _form.append(`course_evaluation_id[${i}]`, item.evaluation_id)
                    _form.append(`course_evaluation_question_id[${i}]`, item.evaluation_question_id)
                    _form.append(`answer[${i}]`, JSON.stringify(item.answer))
                    _form.append(`file_attachment[${i}]`, item.file_attachment)
                })

                this.submitCourseEvaluationAnswersAction(_form).then(res => {
                    this.savingProgressLoading = false
                    this.submitLoading = false
                    this.submitDialog = false
                    this.evalSuccessDialog = true
                    this.start = false
                    this.review = false
                    this.getCourseProgress()
                    this.getCourseEvaluationsAction(this.$route.params.id).then(res => {
                        this.course.is_completed = res.is_completed
                        this.savingProgressLoading = false
                    }).catch(() => {
                        this.savingProgressLoading = false
                        this.showErrorProgressSave()
                    })
                }).catch(() => {
                    this.alertMutation({
                        show: true,
                        text: 'Something went wrong in submitting your evaluation',
                        type: "error"
                    })
                    this.savingProgressLoading = false
                    this.submitLoading = false
                    this.submitDialog = false
                })
            } else {  
                _form.append('time_spent', this.current_content.student_module_evaluation_progress.time_spent)
                this.answers.forEach((item, i) => {
                    _form.append(`course_id[${i}]`, this.course.id )
                    _form.append(`module_id[${i}]`, item.id)
                    _form.append(`module_evaluation_id[${i}]`, item.evaluation_id)
                    _form.append(`module_evaluation_question_id[${i}]`, item.evaluation_question_id)
                    _form.append(`answer[${i}]`, JSON.stringify(item.answer))
                    _form.append(`file_attachment[${i}]`, item.file_attachment)
                })

                this.submitModuleEvaluationAnswersAction(_form).then(res => {
                    this.savingProgressLoading = false
                    this.submitLoading = false
                    this.submitDialog = false
                    this.evalSuccessDialog = true
                    this.start = false
                    this.review = false
                    this.getCourseProgress()
                    this.getModuleContentAction(this.current_content.module_id).then(res => {
                        this.updateStateModules(res)
                        this.savingProgressLoading = false
                    }).catch(() => {
                        this.savingProgressLoading = false
                        this.showErrorProgressSave()
                    })
                }).catch(() => {
                    this.alertMutation({
                        show: true,
                        text: 'Something went wrong in submitting your evaluation',
                        type: "error"
                    })
                    this.submitLoading = false
                    this.submitDialog = false
                    this.savingProgressLoading = false
                })
            }
        },

        getChoices(data){
            let choices = {}

            data.forEach(choice => {
                choices[choice.id] = choice.title
            })

            return choices;
        },

        getAnswer(data){
            if(data){
                if( typeof(data) === 'string' || Array.isArray(data) ) {
                    if(data.length > 0 && data[0].id) {
                        return data[0].id
                    }
                    return data
                }
                return ''
            }
            return ''
        },

        getAnswerMultiple(data){
            let answers = []
            if(data.length > 0){
                data.forEach(ans => {
                    answers.push(ans.id)
                })
            }
            return answers
        },

        getCorrectAnswer(data){
            if(!data) return ''
            return data.map(answer => answer.title)        // Extract the titles
                .join(', '); 
        },

        toggleFullScreen() {
            var iframe = document.getElementById('material-iframe');
            if (iframe.requestFullscreen) {
                iframe.requestFullscreen();
            } else if (iframe.mozRequestFullScreen) { // Firefox
                iframe.mozRequestFullScreen();
            } else if (iframe.webkitRequestFullscreen) { // Chrome, Safari and Opera
                iframe.webkitRequestFullscreen();
            } else if (iframe.msRequestFullscreen) { // IE/Edge
                iframe.msRequestFullscreen();
            }
        },

        checkScormCompletion() {
            this.checkScormLoading = true
            this.getScormRegistrationProgressAction({registration_id: this.current_content.scorm.my_registration.id}).then(res => {
                if(res.completion_status === 'COMPLETED' && !this.current_content.student_done_status) {
                    this.getCourseProgress()
                    this.saveTopicSubtopicProgress('DONE')
                    this.current_content.student_done_status = true

                    let modules = []
                    let content = []

                    this.modules.forEach((item, i) => {
                        modules.push({...item, 
                            module_subtopic_is_done: res.modules[i].module_subtopic_is_done,
                            module_topic_is_done: res.modules[i].module_topic_is_done
                        })
                    })

                    this.modulesMutation(modules)

                    this.content.forEach((item, i) => {
                        content.push({...item})
                    })

                    this.contentMutation(content)
                } else {
                    
                }
            }).catch(() => {
                
                this.alertMutation({
                    show: true,
                    text: 'Something went wrong in fetching your scorm progress',
                    type: "error"
                })

            }).finally(() => {
                this.checkScormLoading = false
            })
        },

        download(){
            this.downloadLoading = true
            this.downloadCourseLearningMaterialAction({url: this.current_content.learning_material.url, file_type: this.current_content.learning_material.file_type}).then(() => {
                this.downloadLoading = false
            }).catch(() => {
                this.downloadLoading = false
                this.alertMutation({
                    show: true,
                    text: 'Something went wrong downloading this material',
                    type: "error"
                })
            })
        }
    },

    watch: {
        successDialog(val) {
            if(val) {
                setTimeout(() => {
                    this.successDialog= false
                }, 3000);
            }
        },

        startAssessment(val) {
            if(!val.status && val.action == 'ended') {
                this.postType='ended'
                this.submitAssessment()
            }
        },
    },

    beforeRouteEnter(to, from, next) {

        if(!from.name) {
            location.href = `/course/${to.params.id}`
        }
        
        next(vm => {
            vm.prevRoute = from          
        })
    },

    beforeRouteLeave(to, from, next) {
        this.stopTimer();
        next();
    }
}

</script>