<template>
  <section id="top-bar">
    <ConnectionDialog/>
    <v-app-bar app color="app-body" flat height="100" class="py-2">
      <v-row justify="center" align="center">
        <v-col cols="12" xl="12">
          <v-sheet class="px-3 py-2 custom-border border d-flex justify-space-evenly" 
              :class="($vuetify.breakpoint.mobile && $route.name === 'Instructor Manage Course') ? 'flex-column' : 'flex-row align-center '">
            <section class="d-flex flex-row align-center">
              <v-btn class="hidden-lg-and-up" icon @click="$emit('showDrawer')" dense>
              <v-icon size="22">mdi-menu</v-icon>
              </v-btn>
              <!-- <div class="poppins text-capitalize secondary-1--text fw600 ellipsis" style="max-width: 35vw !important;" v-if="$route.name === 'Instructor Grading Course' || $route.name === 'Instructor Grading Assessment'">
                {{ grading.title }}
              </div>
              <v-btn 
                v-else-if="$route.name === 'Instructor Grading Assessment User'" 
                class="text-capitalize poppins fw600 no-space pa-0" 
                text
                exact
                dense
                :to="{ name: 'Instructor Grading Assessment', params: { id: grading.id } }">
                &lt; &nbsp; <div class="ellipsis" style="max-width: 35vw !important;">{{ grading.title }}</div>
              </v-btn> -->

              <section v-if="$route.name === 'Instructor Manage Course' && Object.keys(course).length > 0" class="d-flex align-center poppins secondary-1--text fw600">
                <v-btn class="mr-1" icon dense small v-if="!$isReadOnly.isReadOnly(course.status, course.my_permission.length > 0 ? course.my_permission[0].pivot.permission : 'owner')" @click="onEdit = !onEdit">
                  <v-icon>mdi-pencil-outline</v-icon>
                </v-btn>
                <v-text-field 
                  v-if="onEdit" v-model="course.title" 
                  class="poppins f14 secondary-1--text fw600" 
                  v-on:change="setTitle" 
                  :maxlength="255" 
                  counter
                  :rules="[v => v === '' || v.length <= 255 || 'Max 255 characters', v => !!v || 'This is required']"></v-text-field>
                <span v-if="!onEdit" class="ellipsis" style="max-width: 65vw !important;">
                  {{ course.title }}
                </span>
              </section>

              <section v-else-if="$route.name === 'Instructor Grading'" class="d-flex align-center poppins secondary-1--text fw600">
                Grading
              </section>

              <section v-else class="d-flex align-center poppins secondary-1--text fw600">
                <span class="hidden-sm-and-down">
                  {{`Hello, ${user.first_name ? user.first_name : 'Instructor'}!`}}
                </span>
              </section>
              
              <v-spacer />

              <!-- <v-menu
                transition="slide-y-transition"
                offset-y
                rounded="lg"
                nudge-width=300
              >
                <template v-slot:activator="{ on, attrs }">
                  <v-btn
                    v-show="$vuetify.breakpoint.mobile"
                    v-bind="attrs"
                    v-on="on"
                    icon
                    dense
                    v-if="$route.name == 'Instructor Manage Course'"
                  >
                    <v-icon>
                      mdi-update
                    </v-icon>
                  </v-btn>
                </template>
                <CourseActivityLog activity_log="true" @close="" popup="false"/>
              </v-menu> -->

            </section>

            <v-spacer />
            <div
              class="d-flex align-center"
              v-if="$route.name != 'Instructor Manage Course'"
            >
              <!-- <div class="hidden-sm-and-down">
                <v-autocomplete
                  v-if="$route.name === 'Instructor Dashboard'"
                  v-model="select"
                  dense
                  class="poppins f14 fw600 secondary-1--text hidden-sm-and-down"
                  prepend-inner-icon="mdi-magnify"
                  :items="[...courses, ...assessmentList]"
                  :search-input.sync="search"
                  item-text="title"
                  item-value="id"
                  return-object
                  flat
                  hide-details
                  solo
                  label="Search"
                >
                  <template v-slot:item="data">
                    <div class="my-2" v-if="data.item.title">
                      {{ data.item.title }}
                      <div class="roboto font-italic f12 secondary-3--text text-capitalize" v-if="data.item.type_of_assessment" > Grading - {{ data.item.type_of_assessment.replace('_', " ") }}</div>
                      <div class="roboto font-italic f12 secondary-3--text text-capitalize" v-else > Course </div>
                    </div>
                  </template>
                </v-autocomplete>
              </div> -->
              <!-- <v-btn icon color="primary">
                <v-icon size="22">mdi-bell-outline</v-icon>
              </v-btn> -->

              <v-btn
                @click="$emit('add')"
                color="primary"
                v-if="!$vuetify.breakpoint.smAndDown"
                class="text-capitalize poppins fw600 f12 small mr-1"
                dense
                elevation="0"
              >
                Create Course
              </v-btn>
              <v-btn
                @click="$emit('add')"
                icon
                color="primary"
                v-if="$vuetify.breakpoint.smAndDown"
                class="text-capitalize poppins fw600 f12 small"
                dense
              >
                <v-icon>mdi-book-plus-outline</v-icon>
              </v-btn>
              <v-btn
                @click="$emit('enroll')"
                outlined
                color="primary"
                v-if="!$vuetify.breakpoint.smAndDown"
                class="text-capitalize poppins fw600 f12 small mr-1"
                dense
              >
                Enroll Student
              </v-btn>
              <v-btn
                @click="$emit('enroll')"
                icon
                color="primary"
                v-if="$vuetify.breakpoint.smAndDown"
                class="text-capitalize poppins fw600 f12 small mr-1"
                dense
              >
                <v-icon>mdi-account-plus</v-icon>
              </v-btn>
              
              <v-menu
                offset-y
                transition="slide-y-transition"
                bottom
              >
                <template v-slot:activator="{ on, attrs }">
                  
                  <v-btn icon color="success" 
                      class="mx-1"
                      v-bind="attrs"
                      v-on="on"
                      dense>
                      <v-icon v-if="notification.every((notif)=> notif.read_at != null)" size="22">mdi-bell-outline</v-icon>
                    <v-badge
                      :color="unread_count === 0 ? 'transparent' : 'primary'"
                      :content="unread_count"
                      v-else
                      dense
                    >
                      <v-icon size="22">mdi-bell-outline</v-icon>
                    </v-badge>
                    
                  </v-btn>
                </template>

                <NotificationList :loading="loading" :notification="notification" @readMessage="readMessage" @fetch="getNotification" @markAll="markAllAsRead" :role="role" :page="page" :total="total"/>
              </v-menu>
            </div>
            <!-- <div
              v-if="$route.name == 'Instructor Manage Course' && !$vuetify.breakpoint.mobile && $route.query.tab === '0'"
              class="text-decoration-underline f12 poppins secondary-2--text pointer mx-2 mt-2"
              @click="open_activity_log"
            >
              View activity log
            </div> -->
            <!-- <v-menu
              v-else-if="$route.name == 'Instructor Manage Course' && !$vuetify.breakpoint.mobile && $route.query.tab !== '0'"
              transition="slide-y-transition"
              offset-y
              rounded="lg"
              nudge-width=300
            >
              <template v-slot:activator="{ on, attrs }">
                <v-sheet
                  class="text-decoration-underline f12 poppins secondary-2--text pointer mx-2 mt-2"
                  v-bind="attrs"
                  v-on="on"
                >
                  View activity log
                </v-sheet>
              </template>
              <CourseActivityLog activity_log="true" @close="" popup="false"/>
            </v-menu> -->
            <div v-if="$route.name == 'Instructor Manage Course' ? $isReadOnly.showCardAction(course.status, Object.keys(course).length > 0 ? ( course.my_permission.length > 0 ? course.my_permission[0].pivot.permission : 'owner') : 'owner') : false" :class="!$vuetify.breakpoint.mobile && 'mx-2'">
              <div class="f10 poppins text-center secondary--text">COURSE STATUS</div>
              <v-btn-toggle dense class="rounded-md d-flex flex-row mb-2">
                <v-btn outlined class="poppins f12 text-capitalize flex-grow-1" @click="draft()">
                  {{ course.status === "DRAFT" ? "Course Drafted  " : "Save to Drafts" }}
                </v-btn>
                <v-btn
                  color="primary"
                  dark
                  width="130"
                  class="poppins f12 text-capitalize flex-grow-1"
                  @click="publish()"
                >
                  {{ course.status === "PUBLISH" ? "Unpublish" : "Publish" }}
                </v-btn>
              </v-btn-toggle>
            </div>
            <v-avatar size="30" v-if="$route.name !== 'Instructor Manage Course'">
              <img
                alt="Avatar"
                :src="user.image?user.image.url:require('@/assets/default-photo.png')"
                :lazy-src="require('@/assets/default-photo.png')"
                v-on:error="user.image = null"
              >
            </v-avatar>
            <v-menu
              offset-y
              transition="slide-y-transition"
            >
              <template v-slot:activator="{ on, attrs }">
                <v-btn icon
                    v-bind="attrs"
                    v-on="on"
                    v-if="$route.name !== 'Instructor Manage Course'"
                    small>
                    <v-icon>
                      mdi-chevron-down
                    </v-icon>
                </v-btn>
              </template>
              <CommonToolsVue :role="'instructor'"/>
            </v-menu>
            <!-- <v-btn class="hidden-sm-and-down" icon>
              <v-icon>mdi-weather-night</v-icon>
            </v-btn> -->
          </v-sheet>
        </v-col>
      </v-row>
      <toaster
        v-if="alert.show"
        :show="alert.show"
        :text="alert.text"
        :type="alert.type"
        @close="alert.show = false"
      />
      <v-sheet color="transparent" style="top: 15px; position: absolute; right: 15px" class="d-flex flex-column">
        <ToasterNotification v-for="(notification , i) in notifications" 
          :key="i" 
          :header="notification.header" 
          :body="notification.body"
          @click="() => { $router.replace({ name: 'Instructor Inbox', query: { private_chat_uuid: notification.data.private_chat_uuid, timestamp: Date.now() } }) }"
          @close="() => { removeNotificationMutation(notification.id) }"/>
      </v-sheet>
    </v-app-bar>
    <DraftCourse :dialog="dialogDraft" :course="course" @close="dialogDraft = false" />
  </section>
</template>

<style scoped>
.ellipsis {
    overflow: hidden !important;
    white-space: nowrap !important;
    text-overflow: ellipsis !important;
}
</style>

<script>
import { role } from '@/services/axios'
import { mapMutations, mapState, mapActions } from "vuex";
import Breadcrumb from "./InstructorBreadcrumbs.vue";
import DraftCourse from "./DraftsModal.vue";
import NotificationList from "@/constants/reusable/NotificationList.vue";
import CourseActivityLog from '@/components/teacher/course/CourseActivityLog.vue';
import CommonToolsVue from '@/components/reusable/CommonTools.vue';
import ConnectionDialog from '@/components/reusable/ConnectionDialog.vue';

export default {
  props: ["user", "course"],
  components: { Breadcrumb, DraftCourse, NotificationList, CourseActivityLog, CommonToolsVue, ConnectionDialog },

  data: () => ({
    items: ["User", "Teacher", "Admin"],
    dialog: false,
    dialogDraft: false,
    role: "",
    select: null,
    search: null,
    assessmentList: [],
    onEdit: false,
    loading: false,
    page: 1,
    total: -1,
    unread_count: 0
  }),
  template: ``,

  computed: {
    ...mapState({
      alert: (state) => state.alert,
      notifications: (state) => state.notifications,
    }),
    ...mapState('instructor', {
      grading: (state) => state.grading,
      assessments: (state) => state.assessments.filter(item => item.course != null),
      courses: (state) => state.courses,
      notification: (state) => state.notification,
      activity_log: (state) => state.activity_log
    }),
  },
  watch: {
    assessments(val) {
      val.forEach(item => {
        if(item.course != null){
          this.assessmentList.push({
          id: item.id,
          title: !item.title ? 'Untitled' : item.title,
          type_of_assessment: item.type_of_assessment,
        })
        }
      })
    },
    // courses(val){
    //   val.forEach(item => {
        
    //   console.log(item)
    //     item.assessments.forEach(assessment => {
    //       this.assessments.push({
    //         id: assessment.id,
    //         title: assessment.title,
    //         type_of_assessment: assessment.type_of_assessment,
    //       })
    //     })
    //   })
    // },
    '$route' (to, from){
      if(to.fullPath !== from.fullPath) {
        if(to.name != 'Instructor Grading Assessment') {
          this.search  = null
          this.select = null
        }
      }
    },
    select(val){
      console.log(val)
      if(val.type_of_assessment) {
        this.$router.push({ name: 'Instructor Grading Assessment', params: { id: val.id }})
      } else {
        this.$router.push({ name: 'Instructor Manage Course', params: { id: val.id, status: val.status.toLowerCase() }})
      }
    },
  },
  mounted() {
    this.role = role()
    this.getNotification()
    this.true=false
    // this.getCoursesAction().then(() => {
    //    this.loading=false
    // })
    window.addEventListener('offline', this.showConnectionDialog);
    window.addEventListener('online', this.closeConnectionDialog);
  },
  methods: {
    ...mapMutations(['alertMutation', 'removeNotificationMutation']),
    ...mapMutations("instructor", ["statusCourseMutation", "activityLogMutation", "connectionMutation"]),

    ...mapActions('instructor', [
      'getCoursesAction',
      "getNotificationAction",
      "markNotification",
      "markAllAsReadAction",
      "updateCourseDescriptionAction"
    ]),
    ...mapMutations('instructor', [
      'notificationMutation'
    ]),

    getNotification() {
      if(!this.loading && (this.total != this.notification.length)) {
        this.loading = true
        this.getNotificationAction({ page: this.page }).then(res => {
          this.unread_count = res.unread_count
          this.total = res.notifications.total
          if(this.notification.length !== res.notifications.total) {
              this.page += 1
          }
          this.loading = false
        }).catch(() => {
          this.loading = false
        })
      }
    },

    open_activity_log(){
      this.activityLogMutation({
        show: true,
        popup: false
      })
    },
        
    showConnectionDialog(){
        this.connectionMutation({ show: true })
    },

    closeConnectionDialog(){
        this.connectionMutation({ show: false })
    },

    publish() {
      // if (this.course.status == "PUBLISH") return;
      // this.statusCourseMutation({
      //   id: this.course.id,
      //   status: true,
      // });
      if (this.course.status == "PUBLISH") {
        this.statusCourseMutation({
          id: this.course.id,
          status: true,
          action: 'unpublish'
        })
        
      } else {
        this.statusCourseMutation({
          id: this.course.id,
          status: true,
          action: 'publish'
        });
      }
    },

    draft() {
      if (this.course.status == "DRAFT") return;
      this.dialogDraft = true;
      console.log(this.course);
    },

    readMessage(item) {
      this.markNotification({
        notification_id: item.id
      }).then(()=>{
        if(item.type.split('\\')[2] === 'PrivateMessageNotification') {
          this.$router.push({ name: 'Instructor Inbox', query: { private_chat_uuid: item.data.private_chat_uuid, timestamp: Date.now() } })
        }
        if(item.type.split('\\')[2] === 'AnnouncementNotification' && this.role === 'instructor') {
          this.$router.push({ name: 'Instructor Announcements', query: { announcement_id: item.data.announcement_id, timestamp: Date.now() }})
        }
        if(item.type.split('\\')[2] === 'AnnouncementCommentNotification' && this.role === 'instructor') {
          this.$router.push({ name: 'Instructor Announcements', query: { announcement_id: item.data.announcement_id, announcement_comment_id: item.data.announcement_comment_id, timestamp: Date.now() }})
        }
        if(item.type.split('\\')[2] === 'ForumPostCommentNotification' && this.role === 'instructor') {
          this.$router.push({ name: 'Instructor Forum', query: { forum_post_id: item.data.forum_post_id, forum_post_comment_id: item.data.forum_post_comment_id, timestamp: Date.now() }})
        }
        if(item.type.split('\\')[2] === 'ShareAssessmentNotification') {
          console.log(item.read_at)
          this.$router.push({ name: 'Instructor Assessment', query: { assessment_uuid: item.data.assessment_uuid, tab: 1, search: '', page: 1, paginate: 10, timestamp: Date.now() }})
        }
        if(item.type.split('\\')[2] === 'GroupMessageNotification') {
          this.$router.push({ name: 'Instructor Inbox', query: { group_chat_uuid: item.data.group_chat_uuid, timestamp: Date.now() } })
        }
      })
    },

    markAllAsRead(){
      this.loading = true
      this.markAllAsReadAction().then(() => {
        this.loading = false
      }).catch(() => {
        this.loading = false
      })
    },

    setTitle(e) {
      this.updateCourseDescriptionAction({
        ...this.course,
        title: e,
      }).then(()=>{
        this.alertMutation({
            show: true,
            text: 'Course successfully updated',
            type: "success"
        })
      })
    }
  },
};
</script>
