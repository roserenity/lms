<template>
    <section>
        <LinearProgress v-if="pageLoading" />
        <section>
            <v-sheet
                color="transparent"
                class="d-flex flex-column align-center justify-center pb-10 rounded-lg fade"
                height="400"
                v-if="courseNotAvailable">
                <div class="">
                <v-img 
                    max-width="300" 
                    :src="require('@/assets/default/empty_published.png')" />
                </div>
                <h3 class="poppins secondary-1--text fw600 my-5">Oops!</h3>
                <div class="text-center poppins secondary-1--text f12">
                    It seems like this course is no longer available for access. 
                    Please reach out to your instructor for more information or explore other available courses.
                </div>
            </v-sheet>
            <v-sheet v-else min-height="80vh" color="transparent">
                <v-row dense>
                    <v-col cols="12" lg="4" class="order-last order-md-first">
                        <v-sheet id="right" class="fade custom-border border pa-4 fade mb-2" v-if="course.type_of_learning === 'BLENDED' && !pageLoading">
                            <MeetingsList id="meetinglist" class="fade" :course="course"/>
                        </v-sheet>
                        <v-sheet id="right" class="fade custom-border border pa-4 fade">
                            <div class="no-spacing">
                                <FormLabel :label="'LEARNING PROGRESS'" />
                                <section class="d-flex flex-row align-center secondary-1--text">
                                    <v-progress-linear 
                                        height="8"
                                        :value=" progressLoading ? 0 :getProgress"
                                        rounded
                                        color="success"
                                        background-color="grey lighten-3"
                                        class="mr-2"
                                        style="width: 88%"
                                        :indeterminate="progressLoading"
                                    ></v-progress-linear>
                                    <div class="d-flex flex-row align-center f12">{{ progressLoading ? 0 :getProgress }} %</div>
                                </section>
                            </div>
                            <MaterialTimelineFix
                                elevation='0'
                                :course="course" 
                                class="mt-1"
                                id="materialTimeline"
                                @click="goTo"/>
                        </v-sheet>
                    </v-col>
                    <v-col cols="12" lg="8">
                        <v-sheet id="parent" class="custom-border border pa-4 fade">
                            <v-sheet v-if="!course.image" color="success" width="100%" min-height="300" class="border custom-border mb-1"/>
                            <img 
                                id="course-photo"
                                alt="course thumbnail" 
                                v-else
                                height="300"
                                width="100%"
                                :src="course.image.url"
                                v-on:error="require('@/assets/images/default-course-bg.png')"/>
                            <v-sheet v-if="!pageLoading" class="py-1 mt-5 fade" id="parent-nav" >
                                <!-- <v-alert color="#B5221033" class="f10 error--text" dense> We are currently unable to generate certificates at this time. We apologize for any inconvenience this may cause. Thank you for your understanding. </v-alert> -->

                                <v-container flat elevation="0" :class="$vuetify.breakpoint.mobile ? 'd-flex flex-column' : 'd-flex flex-row'">
                                    <div >
                                        
                                        <v-alert dense v-if="course.learner_time_spent > 0" small :color="'#F8835E33'" class="ma-auto mb-3 f12" :class="'error--text'">
                                            Time Spent: {{  $dateFormat.duration(course.learner_time_spent) }}
                                        </v-alert>
                                        <div class="poppins f18 fw600 success--text mb-3">
                                            {{course.title}}
                                        </div>
                                        <div class="d-flex align-center mb-3 roboto fw500 f12 secondary-2--text text-capitalize">
                                            <v-icon class="mr-2" small color="secondary-2">mdi-account-outline</v-icon>
                                            <div v-if="course.facilitator === null || course.facilitator.length === 0">
                                                {{ course.uploader.full_name }}
                                            </div>
                                            <div v-else>
                                                {{ course.facilitator }}
                                            </div>
                                        </div>
                                        <div class="d-flex align-center mb-3 roboto fw500 f12 secondary-2--text text-capitalize">
                                            <v-icon class="mr-2" small color="secondary-2">mdi-clock-outline</v-icon>
                                            <div>
                                                {{`${course.training_hours} ${course.training_hours > 1 ? 'hours' : 'hour'}`}}
                                            </div>
                                        </div>
                                    </div>
                                    <!-- <v-btn color="success" width="100" class="text-capitalize poppins fw600" link :to="latest_module">View</v-btn> -->
                                    <v-spacer />
                                    <section class="d-flex flex-column">
                                        <!-- <v-btn v-if="current_index != null" dense color="primary" class="f12 text-capitalize poppins fw600 mb-3" link :to="{name: 'User Course Content', params: { course_uuid: course.uuid }, query: { uuid: course_content[current_index].uuid, timestamp: Date.now()} }">Continue</v-btn> -->
                                        
                                        <ButtonPrimary
                                            :outlined="true"
                                            :label="'Generate Certificate'"
                                            :icon="'mdi-certificate-outline'"
                                            :loading="certificateLoading"
                                            :disabled="!(getProgress === 100 && course.is_certificate_open)"
                                            @click="generateCertificate"
                                            class="mb-3"
                                        />
                                        <ButtonPrimary
                                            :outlined="true"
                                            :label="'Export'"
                                            :icon="'mdi-export-variant'"
                                            :loading="exportLoading"
                                            @click="exportCourse"
                                        />
                                    </section>
                                </v-container>

                                <v-tabs
                                    v-model="tab"
                                    background-color="transparent"
                                    slider-color="primary"
                                    active-class="white"
                                    show-arrows
                                    >
                                    <v-tab
                                        v-for="item in items"
                                        :key="item"
                                        class="roboto text-capitalize f12 fw600"
                                        active-class="primary--text"
                                    >
                                        {{ item }}
                                    </v-tab>
                                </v-tabs>

                                <v-tabs-items v-model="tab">
                                    <v-tab-item>
                                        <v-sheet max-height="350" class="overflow-y-auto scroller pa-3">
                                            <div v-if="course.description" class="fade roboto f14 fw500 secondary-1--text my-1" v-html="course.description ? $dompurifier(course.description) : ''"></div>
                                            <FormLabel v-else :label="'No course description available.'" />
                                        </v-sheet>
                                    </v-tab-item>
                                    <v-tab-item>
                                        <v-container>
                                            <LinearProgress v-if="announcementLoading" />
                                            <FormLabel v-else-if="announcement.length === 0" :label="'No announcements available.'" />
                                            <div v-else >
                                                <div v-if="announcement_id">
                                                    <div v-if="announcement.length > 0" >
                                                        <v-divider class="mt-3" v-if="!showComments"/>
                                                        <div class="d-flex align-center">
                                                        <v-btn icon v-show="showComments" @click="backFromNotif" class="mr-2">
                                                            <v-icon>mdi-chevron-left</v-icon>
                                                        </v-btn>
                                                        <div class="roboto f16 fw600 secondary-1--text my-2">Announcements</div>
                                                        </div>
                                                        <v-btn text v-if="showSingleComment" class="f12 secondary-2--text">
                                                        View all comments ... 
                                                        </v-btn>
                                                    </div>
                                                    <div v-else >
                                                        <v-divider class="mt-3" v-if="!showComments"/>
                                                        <div class="d-flex align-center ">
                                                        <v-btn icon v-show="showComments" @click="backFromNotif" class="mr-2">
                                                            <v-icon>mdi-chevron-left</v-icon>
                                                        </v-btn>
                                                        <div class="roboto f16 fw600 secondary-1--text my-2">Announcements</div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div v-else-if="announcement.length > 0" >
                                                    <div class="d-flex align-center ">
                                                        <v-btn 
                                                        color="primary" 
                                                        icon 
                                                        v-show="showComments && announcement.length" 
                                                        @click="{
                                                            showComments=false
                                                            selected_announcement = null
                                                            selected_announcement_comment = null
                                                            $router.replace({ query: {} })
                                                            getCourseAnnouncement()
                                                        }" class="mr-2">
                                                        <v-icon>mdi-chevron-left</v-icon>
                                                        </v-btn>
                                                        <div class="roboto f15 fw600 secondary-1--text my-2" v-if="showComments">Comments</div>
                                                    </div>
                                                </div>

                                                <div class="announcements fade" v-if="!showComments">
                                                    <ProfileCard v-for="item in announcement" :key="item.id" 
                                                        :item="item"
                                                        action="comment" 
                                                        icon="mdi-message-outline"
                                                        :comments="item.comments.length"
                                                        :allow_comments="item.allow_comments"
                                                        @comment="comments(item)"
                                                        :selected_announcement_comment="selected_announcement_comment"
                                                    />
                                                </div>
                                                <div class="comments fade" v-else>
                                                    <ProfileCard v-if="announcement[0]" :item="announcement[0]" :selected_announcement_comment="selected_announcement_comment"/>
                                                    <v-form v-if="announcement.length > 0 && announcement[0].allow_comments" @submit.prevent="submit_comment">
                                                        <v-text-field class="login-custom-field f14 roboto fw500 secondary-1--text mb-5" 
                                                        dense 
                                                        filled 
                                                        flat 
                                                        placeholder="Comment"
                                                        hide-details
                                                        v-model="comment"
                                                        :loading="comment_load"
                                                        />
                                                    </v-form>
                                                    <v-divider class="my-7" v-if="announcement.length > 0 && !announcement[0].allow_comments"></v-divider>

                                                    <div class="text-center font-italic secondary--text" v-if="announcement.length > 0 && !announcement[0].allow_comments"> Disabled comments</div>

                                                    <section v-if="announcement.length > 0">
                                                        <ProfileCard :id="`comment-${item.id}`" v-for="item in announcement[0].comments"  :key="item.id" 
                                                        action="reply" 
                                                        :item="item"
                                                        icon="mdi-reply-outline"
                                                        :announcement_id="announcement[0].uuid"
                                                        :allow_comments="announcement[0].allow_comments"
                                                        :replybox = replybox
                                                        :selected_announcement_comment="selected_announcement_comment"
                                                        :level = "1"
                                                        />
                                                    </section>
                                                </div>
                                            </div>
                                        </v-container>
                                    </v-tab-item>
                                </v-tabs-items>

                            </v-sheet>
                        </v-sheet>
                    </v-col>
                </v-row>
            </v-sheet>
        </section>
        <CertificateDialog 
          :dialog="certificateDialog"
          :loading="certificateLoading"
          :url="certificateURL"
          @close="certificateDialog = false"
        />
    </section>
</template>


<style scoped>
    #parent::-webkit-scrollbar {
        display: none;
        /* height: 5px; */
    }
    #right::-webkit-scrollbar {
        display: none;
        /* height: 5px; */
    }
    .fixed {
        position: sticky;
        top: -20px;
        z-index: 1;
    }

    p {
        margin-bottom: 5px !important;
    }

</style>

<script>
import MeetingsList from '@/components/user/course/MeetingsList.vue'
import MaterialTimelineFix from '@/components/user/course/MaterialTimelineFix.vue';
import CertificateDialog from '@/components/main/CertificateDialog.vue';
import ProfileCard from '@/components/user/announcement/ProfileCard.vue'
import { mapActions, mapState, mapMutations } from 'vuex';

export default {
    components: {
        MeetingsList,
        MaterialTimelineFix,
        CertificateDialog,
        ProfileCard
    },

    data: () => ({
        items: ['ABOUT THIS COURSE', 'ANNOUNCEMENTS'],
        tab: 0,
        selected_announcement: 0,
        pageLoading: true,
        announcementLoading: false,
        certificateLoading: false,
        exportLoading: false,
        progressLoading: true,
        courseNotAvailable: false,
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
        showComments: false,
        showSingleComment: false,
        loading: false,
        comment_load: false,
        selected_announcement: 0,
        comment: null,
        announcement_id: null,
        selected_announcement_comment: null,
        certificateLoading: false,
        certificateDialog: false,
        certificateURL: null,
        prevRoute: null,
        error: false
    }),

    computed: {
        ...mapState('usr', {
            announcement: (state) => state.announcements,
            course: (state) => state.object,
            content: (state) => state.content,
        }),
        
        ...mapState({
            replybox: (state) => state.replybox,
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
        }
    },

    mounted() {
        if(this.prevRoute !== 'User Course Content') {
            this.objectMutation([])
            this.modulesMutation([])
            this.evaluationsMutation([])
        }

    },

    created() {

        if(this.selected_announcement !== this.$route.query.announcement_id){
            this.getCourse()
            this.getCourseProgress()
        }
    },

    methods: {
        ...mapActions('usr', [
            'showCourseTimelineAction', 
            'setCourseLog',
            'getCourseProgressAction',
            'getCourseAnnouncementsAction',
            'getAnnouncementAction',
            'generateCertificateAction',
            'markCourseAsCompletedAction',
            'courseExportAction'
        ]),

        ...mapMutations(['alertMutation']),

        ...mapMutations('usr', ['objectMutation', 'modulesMutation', 'evaluationsMutation']),

        getCourseAnnouncement(){
            this.announcementLoading = true
            this.getCourseAnnouncementsAction(this.course.id).then(() => {
                this.announcementLoading = false
            }).catch(() => {
                this.announcementLoading = false
                this.alertMutation({
                    show: true,
                    text: 'Something went wrong in fetching course announcement',
                    type: "error"
                })
            })
        },

        getCourse() {
            this.pageLoading=true
            
            this.showCourseTimelineAction(this.$route.params.id).then(() => {
                if(this.course.type_of_learning === 'BLENDED') {
                    this.setCourseLog({ status: 'Accessed', course_uuid:  this.course.uuid}).finally(() => {
                        this.pageLoading = false
                    })
                } else {
                    this.setCourseLog({ status: this.getProgress === 0 ? 'Course Started' : 'Course Continued', course_uuid:  this.course.uuid}).finally(() => {
                        this.pageLoading = false
                    })
                }
                this.getCourseAnnouncement()

                if(this.$route.query.announcement_id && this.$route.query.type == 'Announcement'){
                    this.selected_announcement = this.$route.query.announcement_id
                    this.announcement_id =`announcement-${this.$route.query.announcement_id}`
                    this.showComments = true
                    this.tab = 1
                    this.getAnnouncement(this.$route.query.announcement_id)
                } else if(this.$route.query.announcement_id && this.$route.query.announcement_comment_id && this.$route.query.type == 'Reply'){
                    this.showComments = true
                    this.selected_announcement = this.$route.query.announcement_id
                    this.selected_announcement_comment = this.$route.query.announcement_comment_id
                    this.tab = 1
                    this.getAnnouncement(this.$route.query.announcement_id)
                }
            }).catch(() => {
                this.pageLoading = false
                this.courseNotAvailable = true
            })
        },

        getAnnouncement(uuid){
            this.announcementLoading = true
            
            this.getAnnouncementAction(uuid).then(() => {
                this.announcementLoading = false
                if(this.$route.query.announcement_id && this.$route.query.type == 'Reply'){
                    setTimeout(() => {
                        this.$vuetify.goTo(`#comment-${this.selected_announcement_comment}`, { offset: 30})
                    }, 2000)
                } else {
                    this.$vuetify.goTo(`#parent-nav`, { offset: 30})
                }
            }).catch(() => {
                this.announcementLoading = false
            })
        },
    
        comments(e) {
            this.showComments=true

            this.selected_announcement = this.announcement.findIndex(i=> i.id == e.id)

            this.getAnnouncement(this.announcement[this.selected_announcement].id)
        },

        async submit_comment() {
            this.comment_load = true
            await this.$api.post(`/user/announcement/comments`, {
                comment: this.comment,
                user_id: this.$store.state.user.id,
                parent_id: null,
                commentable_id: this.announcement[0].id,
                announcement_id: this.announcement[0].uuid
            }).then(res => {
                this.comment = ''
                this.getAnnouncementAction(this.announcement[0].id).then(()=>{
                this.comment_load = false
                }).catch(() => {
                this.comment_load = false
                })
            }).catch(() => {
                this.comment_load = false
            })
        },

        getCourseProgress(){
            this.progressLoading = true
            this.getCourseProgressAction(this.$route.params.id).then(res => {
                this.progress = res
            }).catch(() => {
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
            }).finally(() => {
                this.progressLoading = false
            })
        },

        exportCourse() {
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
                text: "Course exported",
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

        generateCertificate(){
            this.certificateLoading = true
            this.generateCertificateAction({course_id: this.course.id}).then(res => {
                this.certificateDialog = true
                this.certificateLoading = false
                this.certificateURL = res
            }).catch(err => {
                this.certificateLoading = false
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

        goTo(e){
            this.$router.push({
                name: 'User Course Content',
                params: { id: this.$route.params.id, content_uuid: e.uuid, index: this.content.findIndex(item => item.uuid === e.uuid)}
            })
        },

        backFromNotif(){
            this.showComments=false
            this.announcement_id = null
            this.showComments=false
            this.selected_announcement = null
            this.selected_announcement_comment = null
            this.$router.replace({ query: { timestamp: Date.now() } })
            this.getCourseAnnouncement()
        },

    },
        

    beforeRouteEnter(to, from, next) {
        next(vm => {
            vm.prevRoute = from.name          
        })
    },


    watch: {
        tab(val){
            if(val) {
                this.$vuetify.goTo(`#parent-nav`, { offset: 30})
            }
        },

        $route(to, from) {
            if(this.selected_announcement === this.$route.query.announcement_id ){
                if(!this.showComments) this.showComments = true
                
                this.selected_announcement = this.$route.query.announcement_id
                this.selected_announcement_comment = this.$route.query.announcement_comment_id
                this.tab = 1
                this.$vuetify.goTo(`#comment-${this.selected_announcement_comment}`, { offset: 30})
            } else if(this.$route.query.announcement_id && this.$route.query.announcement_comment_id && this.$route.query.type == 'Reply'){
                this.showComments = true
                this.selected_announcement = this.$route.query.announcement_id
                this.selected_announcement_comment = this.$route.query.announcement_comment_id
                this.tab = 1
                this.getAnnouncement(this.$route.query.announcement_id)
            } else if(this.$route.query.announcement_id && this.$route.query.type == 'Announcement') {
                this.selected_announcement = this.$route.query.announcement_id
                this.announcement_id =`announcement-${this.$route.query.announcement_id}`
                this.showComments = true
                this.tab = 1
                this.loading=false
                if(this.course.uuid !== this.$route.params.id) {
                this.current_index = null
                this.getCourse()
                }
            } 
        },
    }
}

</script>