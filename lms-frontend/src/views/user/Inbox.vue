<template>
  <section v-if="!loading">
    <v-sheet class="custom-border border overflow-y-auto scroller px-2" height="78vh">
      <div class="d-flex flex-wrap" :class="$vuetify.breakpoint.mobile ? 'flex-column' : 'align-stretch'">
        <!-- contacts -->
        <v-sheet v-if="viewContacts" :width="(viewContacts && viewMessage ) ? 350 : '100%'" class="pa-2 lr-1 overflow-y-auto scroller">
          <!-- <SearchHeader/> -->
          <div class="d-flex align-center justify-space-between mt-2 ">
            <h4 class="poppins">
              Chats 
              <v-badge v-if="chat_count" class="ml-2" content="chat_count"/>
            </h4>
            <v-btn icon dense color="primary" @click="dialog=true">
              <v-icon small>mdi-plus-box-outline</v-icon>
            </v-btn>
          </div>
          <v-sheet class="overflow-y-auto scroller px-2" 
            height="29vh"
            v-if="chat_lists.length>0"
            id="private-chats-list-wrapper"
            v-scroll.self="onScrollPrivateChatList">
            <a href="#"  
              class="text-decoration-none" 
              v-for="item in chat_lists" 
              :key="item.id"
              @click="() => {
                viewMessage = true,
                ($vuetify.breakpoint.mobile || $vuetify.breakpoint.xs) ? viewContacts = false : viewContacts = true,
                group = null,
                private_chat = item,
                $router.replace({ query: { private_chat_uuid: item.uuid, timestamp: Date.now() } }),
                pageMessages = 1,
                messages_total = -1,
                messagesMutation([]),
                showMessages()
              }"
            >
              <ContactCard :item="item" :user="user" />
            </a>
          </v-sheet>
          <div class="f12 secondary--text text-center my-2" v-else-if="chat_lists.length === 0 && !loadingPrivateMessagesList">
            Start a conversation
          </div>
          <v-sheet color="transparent" 
            v-if="loadingPrivateMessagesList && viewContacts" 
            class="d-flex flex-column justify-end align-center secondary-2--text font-italic f10" 
          >
            loading private chats
          </v-sheet>
          <v-sheet v-else height="15px">
          </v-sheet>

          <div class="d-flex align-center justify-space-between mt-2">
            <h4 class="poppins">Groups</h4>
            <!-- <v-btn icon dense color="primary" @click="dialog_group=true">
              <v-icon small>mdi-plus-box-outline</v-icon>
            </v-btn> -->
          </div>
          <v-sheet class="overflow-y-auto scroller px-2" 
            height="28vh"
            id="group-chat-list-wrapper"
            v-scroll.self="onScrollGroupChatList"
          >
            <a 
              href="#" 
              class="text-decoration-none" 
              v-for="item in group_lists" 
              :key="item.id"
              @click="() => {
                viewMessage = true,
                ($vuetify.breakpoint.mobile || $vuetify.breakpoint.xs) ? viewContacts = false : viewContacts = true,
                private_chat = null,
                friend = null,
                group = item,
                $router.replace({ query: { group_chat_uuid: item.uuid, timestamp: Date.now() } }),
                pageMessages = 1,
                messages_total = -1,
                messagesMutation([]),
                showGroupMessages()
              }"
            >
              <GroupCard 
                :item="item" 
                :user="user"/>
            </a>
          </v-sheet>
          <v-sheet color="transparent" 
            v-if="loadingGroupMessagesList && viewContacts" 
            class="d-flex flex-column justify-end align-center secondary-2--text font-italic f10" 
          >
            loading groups chats
          </v-sheet>
          <v-sheet v-else height="15px">
          </v-sheet>
        </v-sheet>
        <!--  -->
        <div class="" v-if="viewContacts && viewMessage">
          <v-divider vertical/>
        </div>

        <!-- chat -->
        <v-sheet class="d-flex flex-column flex-grow-1" 
          height="75vh"
          v-if="viewMessage"
        >
          <v-sheet class="d-flex align-center mx-3" v-if="friend">
            <v-btn dense icon v-if="($vuetify.breakpoint.mobile || $vuetify.breakpoint.xs)" @click="viewMessage = false, viewContacts = true" color="primary">
              <v-icon>
                mdi-chevron-left
              </v-icon>
            </v-btn>
            <v-avatar class="" size="40">
              <v-img 
                :src="friend.image ? friend.image.url: require('@/assets/default-photo.png')"
                v-on:error="friend.image = null"
              />
            </v-avatar>
            <div class="mx-2 mt-4">
              <h4 class="poppins fw500">{{ friend.full_name }}</h4>
              <p class="poppins secondary--text f11">{{friend.role==='USER' ? 'LEARNER' : friend.role}}</p>
            </div>
          </v-sheet>
          <v-sheet class="d-flex align-center justify-space-between ma-3" v-else-if="group">
            <v-btn dense icon v-if="($vuetify.breakpoint.mobile || $vuetify.breakpoint.xs)" @click="viewMessage = false, viewContacts = true" color="primary">
              <v-icon>
                mdi-chevron-left
              </v-icon>
            </v-btn>
            <div class="d-flex align-center">
              <v-avatar class="" size="40">
                <v-img :src="group.image?group.image.url:require('@/assets/default-photo-group.png')"
                  v-on:error="group.image = null"
                />
              </v-avatar>
              <div class="mx-2">
                <h4 class="poppins fw500">{{`${group.name}`}}</h4>
              </div>
            </div>
            <v-spacer/>
            <!-- <v-btn @click="group_dialog=true" icon color="primary"
              v-if="group.group_chat_has_course_group.length === 0">
              <v-icon>mdi-account-plus</v-icon>
            </v-btn> -->
            <v-menu offset-y>
              <template v-slot:activator="{ on, attrs }">
                <v-btn
                  icon
                  v-bind="attrs"
                  v-on="on"
                  color="primary"
                  v-if="group && group.user_is_member"
                >
                  <v-icon >
                    mdi-dots-vertical
                  </v-icon>
                </v-btn>
              </template>
                <v-list>
                  <v-list-item class="roboto f14 d-flex align-center" @click="removeGroupImage" v-if="group.image">
                    <v-icon size="18" class="mr-2">
                      mdi-image-remove-outline
                    </v-icon>
                    Remove Group Photo
                  </v-list-item>
                  <v-list-item class="roboto f14 d-flex align-center" @click="group_photo = true">
                    <v-icon size="18" class="mr-2">
                      mdi-image-edit-outline
                    </v-icon>
                    Change Group Photo
                  </v-list-item>
                  <v-list-item class="roboto f14 d-flex align-center" @click="group_member_dialog = true">
                    <v-icon size="18" class="mr-2">
                      mdi-account-multiple-outline
                    </v-icon>
                    See members
                  </v-list-item>
                </v-list>
            </v-menu>
          </v-sheet>
          <v-divider/>

          <!-- chat messages -->
          
          <v-sheet color="transparent" 
            v-if="loadingMessages" 
            class="d-flex flex-column justify-end align-center secondary-2--text font-italic f10" 
          >
            loading messages
          </v-sheet>
          <v-sheet v-else height="16px">
          </v-sheet>
          <v-sheet class="overflow-y-auto scroller ma-2 pa-2" 
            id="chat-wrapper" 
            ref="chat-wrapper"
            :height="(group && group.user_is_member) ? '55vh' : '60vh'"
            v-scroll.self="onScrollChatMessages"
          >
              <div v-if="friend && (message.from_user_id === user.id || message.to_user_id === user.id)" 
              v-for="message in messages" :key="message.id" 
                class="mx-3 mt-2 d-flex flex-column justify-end"
                :class="user.id == message.from_user_id?'align-end':'align-start'">
                  <ChatCard 
                    :message="message"
                    :user="user" 
                    type="private"
                    :friend="friend"
                  />
              </div>
              <div v-if="group" v-for="message in messages" :key="message.id" 
                class="mx-3 mt-2 d-flex flex-column justify-end"
                :class="message.synced_users ? 'align-center' : user.id == message.sender_id?'align-end':'align-start'">
                <ChatCard :message="message" :user="user" :friend="message.sender" type="group"/>
              </div>
            <!-- </v-sheet> -->
          </v-sheet>
          <v-alert v-if="group && !group.user_is_member" color="secondary" class="italic">You cannot reply to this conversation</v-alert>
          <ChatInput v-else-if="friend || group" :friend="friend" :loadingMessages="loadingMessages" :group="group" :user="user" :private_chat="private_chat" @successSend="(e) => successSend(e)"/>
        </v-sheet>
      </div>
      
    </v-sheet>
    <ContactDialog 
      :dialog="dialog"
      @close="dialog=false"
      @startMessage="startMessage"
    />

    <GroupContactDialog 
      v-if="group"
      :dialog="group_dialog" 
      :group_chat_uuid="group.uuid"
      @close="() => { group_dialog=false, pageMessages = 1, messages_total = -1, group_lists_total = -1, getGroupChatLists(), showGroupMessages()  }"
    />

    <GroupDialog 
      :dialog="dialog_group"
      @close="() => { dialog_group=false, pageGroupMessagesList = 1, group_lists_total = -1, getGroupChatLists()} "
    />

    <GroupMembersDialog 
      v-if="group"
      :dialog="group_member_dialog" 
      :members="group.group_chat_users"
      @close="group_member_dialog = false"/>

    <PhotoUpload v-if="group" :id="group.uuid" :modal="group_photo" @close="() => {
      group_photo = false,
      pageMessages = 1,
      messages_total = -1,
      showGroupMessages()
    }" :type="'group'" :role="'usr'"/>

  </section>
  <circular v-else />
</template>

<script>
import SearchHeader from '@/components/user/inbox/SearchHeader.vue'
import ContactCard from '@/components/user/inbox/ContactCard.vue'
import Chat from '@/components/user/inbox/Chat.vue'
import ChatInput from '@/components/user/inbox/ChatInput.vue'
import ContactDialog from '@/components/user/inbox/ContactDialog.vue'
import GroupContactDialog from '@/components/user/inbox/GroupContactDialog.vue'
import GroupDialog from '@/components/user/inbox/GroupDialog.vue'
import GroupCard from '@/components/user/inbox/GroupCard.vue'
import ChatCard from '@/components/user/inbox/ChatCard.vue'
import GroupMembersDialog from '@/components/user/inbox/GroupMembersDialog.vue'
import PhotoUpload from '@/components/main/PhotoUpload.vue'

import { mapActions, mapMutations, mapState } from 'vuex'

export default {
  components: {
    SearchHeader,
    ContactCard,
    Chat,
    ChatInput,
    ContactDialog,
    GroupDialog,
    GroupCard,
    ChatCard,
    GroupContactDialog,
    GroupMembersDialog,
    PhotoUpload
  },
  data: () => ({
    dialog: false,
    group_dialog: false,
    dialog_group: false,
    group_member_dialog: false,
    group_photo: false,
    friend: null,
    group: null,    
    private_chat: null,
    chat_wrapper: null,
    view: false,
    chat_count: 0,
    loading: false,
    viewContacts: true,
    viewMessage: false,
    pageMessages: 1,
    pagePrivateMessagesList: 1,
    pageGroupMessagesList: 1,
    loadingPrivateMessagesList: false,
    loadingGroupMessagesList: false,
    loadingMessages: false,
    private_lists_total: -1,
    group_lists_total: -1,
    messages_total: -1,
  }),
  computed: {
    ...mapState('usr', {
      messages: (state) => state.messages,
      chat_lists: (state) => state.chat_lists,
      group_lists: (state) => state.group_lists,
    }),
    ...mapState({
      user: (state) => state.user,
    })
  },  
  
  mounted() {
    window.addEventListener('resize', this.onResize, { passive: true })
    
    this.getChatLists()
    this.getGroupChatLists()
    if(this.$route.query.private_chat_uuid) {
      this.onResize()
      this.viewMessage = true
      this.pageMessages = 1
      this.showMessages()
    }

    if(this.$route.query.group_chat_uuid) {
      this.onResize()
      this.viewMessage = true
      this.pageMessages = 1
      this.showGroupMessages()
    }
  },   

  methods: {
    ...mapActions('usr', [
      'getChatListsAction',
      'showMessagesAction', 
      'sendMessageAction',
      'getGroupListsAction',
      'sendGroupChatAction',
      'showGroupChatAction',
      'removeGroupPhotoAction'
    ]),

    ...mapMutations(['alertMutation']),
    ...mapMutations('usr', ['messagesMutation']),

    onScrollPrivateChatList (e) {
      if(!this.loadingPrivateMessagesList) {
        if (e.target.scrollHeight - e.target.clientHeight <= e.target.scrollTop) {
          this.getChatLists()
        }
      }
    },

    onScrollGroupChatList (e) {
      if(!this.loadingGroupMessagesList) {
        if (e.target.scrollHeight - e.target.clientHeight <= e.target.scrollTop) {
          this.getGroupChatLists()
        }
      }
    },

    onScrollChatMessages(e) {
      if(!this.loadingMessages) {
        if (e.target.scrollTop === 0) {
          if (this.private_chat) {
            this.showMessages()
          }
          if(this.group) {
            this.showGroupMessages()
          }
        }
      }
    },

    getChatLists() {
      if(!this.loadingPrivateMessagesList && (this.private_lists_total != this.chat_lists.length)) {
        this.loadingPrivateMessagesList = true
        this.getChatListsAction(this.pagePrivateMessagesList).then(res => {
          this.private_lists_total = res.total
          if(this.chat_lists.length !== this.private_lists_total) {
            this.pagePrivateMessagesList += 1
          }
          this.loadingPrivateMessagesList = false
          this.scroll()
        }).catch(() => {
          this.loadingPrivateMessagesList = false
          this.scroll()
        })
      }
    },

    getGroupChatLists() {
      if(!this.loadingGroupMessagesList && (this.group_lists_total != this.group_lists.length)) {
        this.loadingGroupMessagesList = true
        this.getGroupListsAction(this.pageGroupMessagesList).then(res => {
          this.group_lists_total = res.total
          if(this.group_lists.length !== this.group_lists_total) {
            this.pageGroupMessagesList += 1
          }
          this.loadingGroupMessagesList = false
          this.scroll()
        }).catch(() => {
          this.loadingGroupMessagesList = false
          this.scroll()
        })
      }
    },

    onResize () {
      if(!this.$vuetify.breakpoint.smAndDown || !this.$vuetify.breakpoint.mobile) {
        this.viewContacts = true
      }
      if(this.$vuetify.breakpoint.smAndDown || this.$vuetify.breakpoint.mobile) {
        if(this.viewMessage) this.viewContacts = false
      }
      
    },

    showMessages( scroll = true ) {
      this.onResize()
      if(!this.loadingMessages && (this.messages_total != this.messages.length)) {
        this.group = null
        this.loadingMessages = true
        let last_position = null


        // if(document.getElementById("chat-wrapper")) {

        //   let chat_wrapper = document.getElementById("chat-wrapper");

        //   last_position = chat_wrapper.scrollHeight
        // }

        this.showMessagesAction({uuid: this.$route.query.private_chat_uuid, page: this.pageMessages})
          .then(res => {

            this.private_chat = res
            this.dialog=false
            this.loadingMessages = false
            this.$router.replace({ query: { private_chat_uuid: this.private_chat.uuid, timestamp: Date.now() } });
            if(this.user.id == this.private_chat.sender.id) this.friend = this.private_chat.receiver
            if(this.user.id == this.private_chat.receiver.id) this.friend = this.private_chat.sender

            if(this.pageMessages === 1 && this.messages_total === -1) {
              if(scroll) {
                this.scroll()
              }
            } 
            
            // if(last_position){
            //   let chat_wrapper = document.getElementById("chat-wrapper");

            //   chat_wrapper.scrollTop = chat_wrapper.scrollHeight - last_position;
            // }

            this.messages_total = res.private_messages_count
            if(this.messages.length !== this.messages_total) {
              this.pageMessages += 1
            }
          }).catch(() => {
            this.loadingMessages = false
          })
      }
    },

    startMessage(e) {
      this.messagesMutation([])
      this.private_chat=null
      this.group = null
      this.friend = e
      
      this.viewMessage = true
      this.onResize()
      this.pageMessages = 1
      this.dialog = false

      if(e.private_chat_as_creator) {
        this.$router.replace({ query: { private_chat_uuid: e.private_chat_as_creator.uuid, timestamp: Date.now() } });
      }else if(e.private_chat_as_user) {
        this.$router.replace({ query: { private_chat_uuid: e.private_chat_as_user.uuid, timestamp: Date.now() } });
      } else {
        this.$router.replace({ query: { timestamp: Date.now() } });
      }
    },

    successSend(uuid){
      if (this.friend) {
        this.loadingPrivateMessagesList = true
        this.pagePrivateMessagesList = 1
        this.getChatListsAction(this.pagePrivateMessagesList).then(res => {
          this.group = null
          if(this.user.id === this.messages.creator_id) {
            this.friend = this.messages.user
          }
          if(this.user.id === this.messages.user_id) {
            this.friend = this.messages.creator
          }

          this.loadingPrivateMessagesList = false
          this.pageMessages = 1
          this.messages_total = -1
          if(!this.$route.query.private_chat_uuid || this.$route.query.private_chat_uuid !== uuid) {
            this.$router.replace({ query: { private_chat_uuid: uuid, timestamp: Date.now() } });
          } else {
            this.showMessages(false)
          }
        }).catch(() => {
            this.loadingPrivateMessagesList = false
        })
      }

      if (this.group) {
        this.loadingGroupMessagesList = true
        this.pageGroupMessagesList = 1
        this.getGroupListsAction(this.pageGroupMessagesList).then(res => {
          this.friend = null
          this.private_chat = null
          this.loadingGroupMessagesList = false
          this.pageMessages = 1
          this.messages_total = -1
          this.showGroupMessages()
        }).catch(() => {
            this.loadingGroupMessagesList = false
        })
      }
    },

    removeGroupImage() {
      this.removeGroupPhotoAction(({ id: this.group.uuid })).then(() => {
        this.alertMutation({
          show: true,
          text: 'Group chat photo removed successfully.',
          type: "success"
        })
        this.pageMessages = 1,
        this.messages_total = -1,
        this.showGroupMessages()
      }).catch(() => {
        this.alertMutation({
          show: true,
          text: 'Something went wrong.',
          type: "error"
        })
      })
    },

    showGroupMessages() {
      this.onResize()
      if(!this.loadingMessages && (this.messages_total != this.messages.length)) {
        this.friend= null
        this.loadingMessages = true
        let last_position = null

        if(document.getElementById("chat-wrapper")) {

          let chat_wrapper = document.getElementById("chat-wrapper");

          last_position = chat_wrapper.scrollHeight
        }

        this.showGroupChatAction({uuid: this.$route.query.group_chat_uuid, page: this.pageMessages})
          .then(res => {
            this.group = res
            this.dialog=false
            this.loadingMessages = false
            this.$router.replace({ query: { group_chat_uuid: this.group.uuid, timestamp: Date.now() } });

            if(this.pageMessages === 1 && this.messages_total === -1) {
              this.scroll()
            } else if(last_position){
              let chat_wrapper = document.getElementById("chat-wrapper");

              chat_wrapper.scrollTop = chat_wrapper.scrollHeight - last_position;
            }

            this.messages_total = res.group_messages_count
            if(this.messages.length !== this.messages_total) {
              this.pageMessages += 1
            }
          }).catch(() => {
            this.loadingMessages = false
            this.alertMutation({
              show: true,
              text: "Couldn't fetch group chat",
              type: "error"
            })
            this.viewMessage = false
          })
      }
    },

    scroll() {
      if(this.viewMessage && !this.loadingMessages) {
        setTimeout(() => {
          let chat_wrapper = document.getElementById("chat-wrapper");
          chat_wrapper.scrollTop = chat_wrapper.scrollHeight;
        },100);
      }
    },
  },

  watch: { 
    '$route' (to, from){
      if(to.query.private_chat_uuid) {
        this.viewMessage = true
        if(!this.private_chat) {
          this.pageMessages = 1
          this.messagesMutation([])
          this.showMessages()
        } else if (this.private_chat.uuid !== to.query.private_chat_uuid) {
          this.pageMessages = 1
          this.messagesMutation([])
          this.showMessages()
        }
      }
      
      if(to.query.group_chat_uuid) {
        this.viewMessage = true
        if(!this.group) {
          this.pageMessages = 1
          this.messagesMutation([])
          this.showGroupMessages()
        } else if (this.group.uuid !== to.query.group_chat_uuid) {
          this.pageMessages = 1
          this.messagesMutation([])
          this.showGroupMessages()
        }
      }
    }
  }
}
</script>
