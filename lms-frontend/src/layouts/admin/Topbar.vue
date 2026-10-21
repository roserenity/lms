<template>
  <section id="top-bar">
    <ConnectionDialog/>
    <v-app-bar app color="app-body" flat height="100">
      <v-row justify="center" align="center" class="my-4">
        <v-col cols="12" xl="11">
          <v-card class="custom-border border d-flex flex-row align-center pa-2 my-2 poppins fw600 secondary-1--text text-wrap">
            <v-btn class="hidden-lg-and-up" icon @click="$emit('showDrawer')">
              <v-icon size="20">mdi-menu</v-icon>
            </v-btn>
              {{ $route.name.replace('Admin ', "") }}
            <v-spacer/>
            <div class="d-flex align-center">
            </div>
            
            <v-btn v-if="$vuetify.breakpoint.smAndUp" dense @click="$emit('add')" color="primary" class="text-capitalize poppins fw600 f11 small mr-1">
              Upload User
            </v-btn>
            <v-btn 
              icon 
              v-else
              dense
              @click="$emit('add')" color="primary" class="text-capitalize poppins fw600 f11">
              <v-icon>mdi-account-multiple-plus</v-icon>
            </v-btn>
            <v-btn v-if="$vuetify.breakpoint.smAndUp" dense @click="$emit('enroll')" color="primary" outlined class="text-capitalize poppins fw600 f11 small mr-3">
              Enroll Users
            </v-btn>
            <v-btn 
              icon 
              dense
              v-else
              @click="$emit('enroll')" color="primary" class="text-capitalize poppins fw600 f11 mr-3">
              <v-icon>mdi-account-multiple-outline</v-icon>
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
            <v-avatar size="30">
              <img
                alt="Avatar"
                :src="user.image?user.image.url:require('../../assets/default-photo.png')"
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
              <CommonToolsVue :role="'admin'"/>
            </v-menu>
          </v-card>
        </v-col>
      </v-row>
      <toaster
        v-if="alert.show"
        :show="alert.show"
        :text="alert.text"
        :type="alert.type"
        @close="alert.show = false"
      />
    </v-app-bar>
  </section>  
</template>

<script>
import { role } from '@/services/axios'
import { mapMutations, mapState, mapActions } from 'vuex'
import NotificationList from "@/constants/reusable/NotificationList.vue";
import CommonToolsVue from '@/components/reusable/CommonTools.vue';
import ConnectionDialog from '@/components/reusable/ConnectionDialog.vue';

export default {
  props: ["user"],
  components: {
    CommonToolsVue,
    NotificationList,
    ConnectionDialog
  },
  data: () => ({
    items: ['User','Teacher', 'Admin'],
    dialog: false,
    role: '',
    loading: false,
    filter: {
      role: null,
      class_category_id: [],
      course_id: [],
      status: null,
      created_at: null
    },
    page: 1,
    total: -1,
    unread_count: 0
  }),
  mounted(){
    this.role = role()
    this.reset()
    this.getNotification()
    this.getRegistrationTablesAction()
    window.addEventListener('offline', this.showConnectionDialog);
    window.addEventListener('online', this.closeConnectionDialog);
  },
  created(){
    this.reset()
  },
  computed: {
    ...mapState({
      alert: (state) => state.alert,
      notifications: (state) => state.notifications,
    }),
    ...mapState('admin', {
      users: (state) => state.users,
      courses: (state) => state.courses,
      notification: (state) => state.notification,
    }),
  },
  methods: {
    ...mapMutations('admin', ['filteredUsersMutation', 'notificationMutation']),
    ...mapActions('admin', ['getRegistrationTablesAction', 
      "getNotificationAction",
      "markNotification",
      "markAllAsReadAction"]),
    
    ...mapMutations(['alertMutation', 'removeNotificationMutation', 'connectionMutation']),

    ...mapState({
      tenant: (state) => state.tenant
    }),

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

    reset(){
      this.filteredUsersMutation(this.users)
      this.filter = {
        role: null,
        class_category_id: null,
        course_id: [],
        status: null,
        created_at: null
      }
    },

    // filterUsers(){
    //   if( this.users.length > 0 ) {
    //     let { role, course_id, status, created_at } = this.filter
    //     let _users = this.users
    //     if ( role !== null ) {
    //       _users = _users.filter(item => item.role === (role ? 'INSTRUCTOR' : 'USER'))
    //     }
    //     if ( course_id.length > 0) {
    //       _users = _users.filter(item => item.courses !== [] && item.courses.some(_course_id => course_id.includes(_course_id)))
    //     }
    //     if ( status !== null ) {
    //       _users = _users.filter(item => item.status === (status ? 'inactive' : 'active'))
    //     }
    //     if( created_at !== null ) {
    //       if( created_at === 0 ) {
    //         _users = _users.filter(i => 
    //           this.$dateFormat.mmDDyy(i.created_at) == this.$dateFormat.mmDDyy(new Date())
    //         )
    //       } else {
            
    //         let type = null
    //         if(created_at === 1) type = 'week'
    //         if(created_at === 2) type = 'month'
    //         if(created_at === 3) type = 'year'

    //         _users = _users.filter(i => {
    //           let item_created_at = moment(i.created_at)
    //           let from_date = moment().startOf(type)
    //           let to_date = moment().endOf(type)

    //           return from_date <= item_created_at && to_date >= item_created_at
    //         })
    //       }
    //     }

    //     if(_users.length === 0) {
    //       _users = 'EMPTY'
    //     }

    //     this.filteredUsersMutation(_users)
    //   }
    // },

    readMessage(item) {
      this.markNotification({
        notification_id: item.id
      }).then(()=>{
        if(item.type.split('\\')[2] === 'PrivateMessageNotification') {
          this.$router.push({ name: 'Admin Inbox', query: { private_chat_uuid: item.data.private_chat_uuid, timestamp: Date.now() } })
        }
        if(item.type.split('\\')[2] === 'AnnouncementCommentNotification' && this.role === 'admin') {
          this.$router.push({ name: 'Admin Announcements', query: { announcement_id: item.data.announcement_id, announcement_comment_id: item.data.announcement_comment_id, timestamp: Date.now() }})
        }
        if(item.type.split('\\')[2] === 'ForumPostCommentNotification' && this.role === 'admin') {
          this.$router.push({ name: 'Admin Forum', query: { forum_post_id: item.data.forum_post_id, forum_post_comment_id: item.data.forum_post_comment_id, timestamp: Date.now() }})
        }
        if(item.type.split('\\')[2] === 'GroupMessageNotification') {
          this.$router.push({ name: 'Admin Inbox', query: { group_chat_uuid: item.data.group_chat_uuid, timestamp: Date.now() } })
        }
        if(['TicketReceiveNotification', 'TicketMessageNotification'].includes(item.type.split('\\')[2])) {
          this.$router.push({ name: 'Admin Ticket', query: { ticket_uuid: item.data.ticket_uuid, timestamp: Date.now() } })
        }
      })
    },
        
    showConnectionDialog(){
        this.connectionMutation({ show: true })
    },

    closeConnectionDialog(){
        this.connectionMutation({ show: false })
    },

    markAllAsRead(){
      this.loading = true
      this.markAllAsReadAction().then(() => {
        this.loading = false
      }).catch(() => {
        this.loading = false
      })
    },
  },
}
</script>
