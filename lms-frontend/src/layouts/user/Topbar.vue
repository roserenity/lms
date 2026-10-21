<template>
  <section id="top-bar">
    <ConnectionDialog/>
    <v-app-bar app color="app-body" flat
    :height="$vuetify.breakpoint.mobile?70:100">
      <v-row justify="center" align="center">
        <v-col cols="12" xl="11">
          <v-toolbar class="custom-border border">
            
            <v-btn class="hidden-lg-and-up" icon @click="$emit('showDrawer')">
              <v-icon size="22">mdi-menu</v-icon>
            </v-btn>
            
            <div class="hidden-sm-and-down">
              <h4 class="poppins secondary-1--text fw600" v-if="$route.name !== 'User Course Content'">
                {{`Hello, ${user.first_name ? user.first_name : 'User'}!`}}
              </h4>  
              <h4 class="poppins secondary-1--text fw600" v-else >
                {{ current_course.title }}
              </h4> 
              <div v-if="$route.name == 'User Dashboard' && origin === tour_origin" class="roboto f12 fw400 secondary-2--text hidden-sm-and-down">Get familliarized by taking the tour 
                <a href="#" class="success--text" @click="$emit('startTour')">Start</a>
              </div>
              <!-- <div v-if="$route.name == 'User Dashboard' && origin === 'http://localhost:8080'" class="roboto f12 fw400 secondary-2--text">Get familliarized by taking the tour 
                <a href="#" class="success--text" @click="$emit('startTour')">Start</a>
              </div> -->
            </div>
            
            <v-spacer />
            <div class="" v-if="$route.name == 'User Course Content'">
              <div class="f15">
                <b><span id="timer"></span></b>
              </div>
            </div>
            <div class="d-flex align-center" v-else>
              <!-- <v-autocomplete
                v-model="select"
                dense
                class="poppins f14 fw600 secondary-1--text hidden-sm-and-down"
                prepend-inner-icon="mdi-magnify"
                :items="all_courses"
                :search-input.sync="search"
                item-text="title"
                item-value="uuid"
                flat
                hide-details
                solo
                label="Search"
              >
                <template v-slot:item="data">
                  <div class="d-flex flex-column py-2" 
                    v-if="data.item.type === 'My Course'"
                    @click="$router.push({ name: 'User Course Details', params: { id: data.item.uuid}, query: { timestamp: Date.now()}})">
                    <span class="f12 poppins secondary-4--text">{{ data.item.type }}</span>
                    <span class="f14 poppins fw500">{{ data.item.title.length > 25 ? `${data.item.title.slice(0, 21)}...` : data.item.title }}</span>
                  </div>
                  <div class="d-flex flex-column py-2" 
                    v-if="data.item.type === 'Other Courses'"
                    @click="$router.push({ name: 'User Other Course Details', params: { uuid: data.item.uuid}, query: { timestamp: Date.now() }})">
                    <span class="f12 poppins secondary-4--text">{{ data.item.type }}</span>
                    <span class="f14 poppins fw500">{{ data.item.title.length > 25 ? `${data.item.title.slice(0, 21)}...` : data.item.title }}</span>
                  </div>
                </template>
              </v-autocomplete> -->
              <v-menu
                offset-y
                transition="slide-y-transition"
                bottom
              >
                <template v-slot:activator="{ on, attrs }">
                  
                  <v-btn icon color="success" 
                      v-bind="attrs"
                      v-on="on">
                      <v-icon v-if="notification.every((notif)=> notif.read_at != null)" size="22">mdi-bell-outline</v-icon>
                    <v-badge
                      :color="unread_count === 0 ? 'transparent' : 'primary'"
                      :content="unread_count"
                      v-else
                    >
                      <v-icon size="22">mdi-bell-outline</v-icon>
                    </v-badge>
                    
                  </v-btn>
                </template>

                <NotificationList :loading="loading" :notification="notification" @readMessage="readMessage" @fetch="getNotification" @markAll="markAllAsRead" :role="role" :page="page" :total="total"/>
              </v-menu>
              <!-- <v-btn class="hidden-sm-and-down" icon>
                <v-icon>mdi-weather-night</v-icon>
              </v-btn> -->

              <v-menu offset-y :close-on-content-click="closeMenu">
                  <template v-slot:activator="{ on, attrs }">
                      <v-badge
                          overlap
                          :color="cart.length === 0 ? 'transparent' : 'primary'"
                          :value="cart.length"
                          :content="cart.length"
                          offset-x="18"
                          offset-y="17">
                          <v-btn color="primary" icon
                          v-bind="attrs"
                          v-on="on">
                            <v-icon>
                              mdi-text-box-outline
                            </v-icon>
                          </v-btn>
                      </v-badge>
                  </template>
                  <Cart @close="closeMenu = true"/>
              </v-menu>


              <v-btn icon dense color="primary" @click="$router.push({ name: 'User Scan Certificate'})" class="mr-1">
                <v-icon>
                  mdi-qrcode-scan
                </v-icon>
              </v-btn>
              <v-avatar size="30">
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
                      small>
                      <v-icon>
                        mdi-chevron-down
                      </v-icon>
                  </v-btn>
                </template>
                <CommonToolsVue :role="'user'"/>
              </v-menu>
            </div>
          </v-toolbar>
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
          @click="() => { $router.push({ name: 'User Inbox', query: { private_chat_uuid: notification.data.private_chat_uuid, timestamp: Date.now() } }) }"
          @close="() => { removeNotificationMutation(notification.id) }"/>
      </v-sheet>
    </v-app-bar>
    
  </section>
</template>

<script>
import { role } from '@/services/axios'
import { mapMutations, mapActions, mapState } from "vuex";
import NotificationList from "@/constants/reusable/NotificationList.vue";
import Cart from '@/components/user/course/Cart.vue'
import CommonToolsVue from '@/components/reusable/CommonTools.vue';
import ConnectionDialog from '@/components/reusable/ConnectionDialog.vue';

export default {
    props: ["user", "course", "drawer"],
    data: () => ({
        tour_origin: process.env.VUE_APP_TOUR_ORIGIN || '',
        items: ["User", "Teacher", "Admin"],
        dialog: false,
        dialogDraft: false,
        role: "",
        countDown: null,
        display: "",
        search: null,
        select: null,
        myCourses: null,
        asset_path: `${process.env.VUE_APP_ASSET_PATH}`,
        closeMenu: false,
        origin: location.origin,
        loading: false,
        page: 1,
        total: -1,
        unread_count: 0
    }),
    computed: {
      ...mapState({
          alert: (state) => state.alert,
          tenant: (state) => state.tenant,
          other_courses: (state) => state.other_courses,
          notifications: (state) => state.notifications,
        }),
      ...mapState("usr", {
        current_course: (state) => state.object,
        courses: (state) => state.courses,
        assessment: (state) => state.assessments,
        start: (state) => state.startAssessment,
        notification: (state) => state.notification,
        cart: (state) => state.cart
      }),
      all_courses() {
        let _all_courses = []

       this.courses.forEach(item => { 
          item.type = 'My Course'
          _all_courses.push(item)
        })
        this.other_courses.forEach(item => { 
          item.type = 'Other Courses'
          _all_courses.push(item)
        })

        return _all_courses
      }
    },
    mounted() {
      this.role = role()
      this.getNotification()
      this.getAllCartItemsAction()
      window.addEventListener('offline', this.showConnectionDialog);
      window.addEventListener('online', this.closeConnectionDialog);
      window.onoffline = (event) => {
        this.closeConnectionDialog;
      };
    },
    methods: {
      ...mapActions('usr', [
        "getNotificationAction",
        "markNotification",
        "markAllAsReadAction",
        "getAllCartItemsAction",
      ]),

      ...mapMutations([
        "removeNotificationMutation",
        "connectionMutation"
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

      redirectTo( type, _uuid) {
        if(type === 'My Course') {
          location.href = `/course/${_uuid}`
          // this.$router.push({ name: 'User Course Details', params: { id: _uuid}})
        } else {
          location.href = `/course/${_uuid}/learn_more`
          // this.$router.push({ name: 'User Other Course Details', params: { uuid: _uuid}, query: { timestamp: Date.now() }})
        }
      },

      startTimer(duration) {
        let timer = duration, minutes, seconds;
        var vm = this;
        this.countDown = setInterval(() => {
            minutes = parseInt(timer / 60, 10);
            seconds = parseInt(timer % 60, 10);
            minutes = minutes < 10 ? "0" + minutes : minutes;
            seconds = seconds < 10 ? "0" + seconds : seconds;
            vm.display.textContent = minutes + ":" + seconds;
            if (--timer < 0) {
                clearInterval(vm.countDown);
                vm.display.textContent = "";
                vm.$store.commit("usr/startAssessmentMutation", {
                    action: "ended",
                    status: false
                });
            }
        }, 1000);
      },

      readMessage(item) {
        this.markNotification({
          notification_id: item.id
        }).then(()=>{
          if(item.type.split('\\')[2] === 'PrivateMessageNotification') {
            this.$router.push({ name: 'User Inbox', query: { private_chat_uuid: item.data.private_chat_uuid, timestamp: Date.now() } })
          }
          if(item.type.split('\\')[2] === 'AnnouncementNotification') {
            this.$router.push({name: 'User Course Details', params: {id: item.data.course_uuid }, query: { announcement_id: item.data.announcement_id, type: 'Announcement'}})
          }
          if(item.type.split('\\')[2] === 'AnnouncementCommentNotification' && this.role === 'user') {
            this.$router.push({name: 'User Course Details', params: { id: item.data.course_uuid }, query: { announcement_id: item.data.announcement_id, type: 'Reply', announcement_comment_id: item.data.announcement_comment_id, timestamp: Date.now() } })
          }
          if(item.type.split('\\')[2] === 'ForumPostCommentNotification' && this.role === 'user') {
            this.$router.push({ name: 'User Forum', query: { forum_post_id: item.data.forum_post_id, forum_post_comment_id: item.data.forum_post_comment_id, timestamp: Date.now() }})
          }
          if(item.type.split('\\')[2] === 'GroupMessageNotification') {
            this.$router.push({ name: 'User Inbox', query: { group_chat_uuid: item.data.group_chat_uuid, timestamp: Date.now() }  })
          }
          if(item.type.split('\\')[2] === 'AssessmentDeadlineNotification') {
            this.$router.push({name: 'User Course Details', params: {id: item.data.course_uuid }})
          }
          if(item.type.split('\\')[2] === 'ForumPostReviewNotification') {
            this.$router.push({name: 'User Forum', query: { forum_post_id: item.data.forum_post_id, timestamp: Date.now() }})
          }
          if(['TicketStatusUpdateNotification', 'TicketMessageNotification'].includes(item.type.split('\\')[2])) {
            this.$router.push({ name: 'User Help Desk', query: { ticket_uuid: item.data.ticket_uuid, timestamp: Date.now() } })
          }
          if(['ProfileFilloutNotification'].includes(item.type.split('\\')[2])) {
            this.$router.push({ name: 'User Account Profile' })
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
        
      showConnectionDialog(){
          this.connectionMutation({ show: true })
      },
  
      closeConnectionDialog(){
          this.connectionMutation({ show: false })
      },
      
    },
    watch: {
        start(val) {
            if (val.status) {
                if (!this.assessment.time_limit)
                    return;
                this.display = document.querySelector("#timer");
                return this.startTimer(this.assessment.time_limit * 60);
            }
            else {
                this.display.textContent = "";
                clearInterval(this.countDown);
            }
        },
        "$route"(to, from) {
            if (to.fullPath !== from.fullPath) {
                if (to.name != "User Course Details" || (to.name == "User Course Details" && from.name == "User Course Details")) {
                    this.search = null;
                    this.select = null;
                }
            }
        },
        // select(val) {
        //     this.$router.push({ name: "User Course Details", params: { id: val } });
        // }
    },
    components: { 
      NotificationList, 
      Cart,
      CommonToolsVue,
      ConnectionDialog
    }
};
</script>

<style>
  /* .v-autocomplete{
    white-space: nowrap !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
  } */
</style>