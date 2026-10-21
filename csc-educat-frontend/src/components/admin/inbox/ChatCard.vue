<template>
  <div>
    <v-sheet class="d-flex align-end" v-if="type=='private'">
      <v-avatar size="30" class="mr-2 mb-3" v-if="user.id != message.from_user_id">
        <v-img 
          :src="friend.image? friend.image.url : require('@/assets/default-photo.png')"
          v-on:error="require('@/assets/default-photo.png')"></v-img>
      </v-avatar>
      <div class="">
        <v-sheet 
          class="pa-2 chat-card"
          :class="user.id == message.from_user_id?'rounded-tr-xl rounded-l-xl':'rounded-tl-xl rounded-r-xl'"
          :dark="user.id == message.from_user_id" 
          outlined 
          max-width="300" 
          :color="user.id == message.from_user_id?'primary':'secondary-5'">
          <div class="f12 poppins fw500">{{message.message_body}}</div>
          
          <div v-for="(item, i) in message.attachments" :key="i">
            <v-img v-if="['jpg', 'png', 'jpeg'].includes(item.file_type)"
              :src="item.file_path" 
              height="80"
              width="80"
              class="mb-2"
              id="attachment"
                @click="() => { url = item.file_path, dialog = true }"
            />
            <span v-else class="text-decoration-underline cursor-pointer poppins f12 fw500" @click="download({ url: item.file_path, file_type: item.file_type })">
              <v-icon left small>mdi-download</v-icon>{{ item.file_name }}
            </span>
          </div>

        </v-sheet>
        <div class="f10 poppins secondary--text" :class="user.id == message.from_user_id?'text-right': 'text-left'">
          {{$dateFormat.chatTime(message.created_at)}}
        </div>
      </div>
      
    </v-sheet>
    <v-sheet class="d-flex align-start" v-else>
      <div v-if="message.message_body && (message.message_body.includes('has been added to the group') || message.message_body.includes('has been removed from the group'))" class="f12 poppins secondary-2-text">
        {{ message.message_body }}
      </div>
      <div v-else class="d-flex align-end">
        <v-avatar size="30" class="mr-2 mb-4" v-if="user.id !== message.sender_id">
          <v-img 
            :src="friend.image? friend.image.url : require('@/assets/default-photo.png')"
            v-on:error="require('@/assets/default-photo.png')"></v-img>
        </v-avatar>

        <div class="d-flex flex-column ml-2" >
          <span v-if="user.id != message.sender_id" class="f11 poppins fw400 secondary-2--text text-left">
            {{ friend.first_name }} {{ friend.last_name }}
          </span>
          
          <v-sheet 
            class="pa-3 chat-card"
            :class="user.id == message.sender_id?'rounded-tr-xl rounded-l-xl':'rounded-tl-xl rounded-r-xl'"
            :dark="user.id == message.sender_id" 
            outlined 
            max-width="300" 
            :color="user.id == message.sender_id?'primary':'secondary-5'">
            <div class="f13 poppins fw500">{{message.message_body}}</div>

            <div v-for="(item, i) in message.attachments" :key="i">
              <v-img v-if="['jpg', 'png', 'jpeg'].includes(item.file_type)"
                :src="item.file_path" 
                height="80"
                width="80"
                class="mb-2"
                id="attachment"
                @click="() => { url = item.file_path, dialog = true }"
              />
              <span v-else class="text-decoration-underline cursor-pointer" @click="download({ url: item.file_path, file_type: item.file_type })">
                <v-icon left size="24">mdi-download</v-icon>{{ item.file_name }}
              </span>
            </div>

          </v-sheet>
          <div class="f10 poppins secondary--text" :class="user.id == message.sender_id?'text-right': 'text-left'">
            {{$dateFormat.chatTime(message.created_at)}}
          </div>
        </div>
      </div>
    </v-sheet>
      
    <FullScreenImage :dialog="dialog" @close="() => { dialog = false , url = null }" :imageUrl="url"/>
  </div>
</template>

<script>
import { mapActions, mapMutations } from 'vuex'
import FullScreenImage from "@/components/reusable/FullScreenImage.vue"

export default {
  props: ['message', 'user', 'type',  "friend"],
  data: () => ({
    dialog: false,
    url: null
  }),
  components: {
    FullScreenImage
  },
  methods: {
    ...mapActions('admin', [
      'downloadPrivateChatAttachmentAction',
      'downloadGroupChatAttachmentAction',
    ]),

    ...mapMutations(['alertMutation']),

    download(payload){
      if(this.type === 'private') {
        this.downloadPrivateChatAttachmentAction(payload).catch(() =>{
          this.alertMutation({
            show: true,
            text: 'Something went wrong',
            type: "error"
          })
        })
      } else {
        this.downloadGroupChatAttachmentAction(payload).catch(() =>{
          this.alertMutation({
            show: true,
            text: 'Something went wrong',
            type: "error"
          })
        })
      }
    }
  }
}
</script>

<style >
    #attachment:hover {
        cursor: zoom-in;
    }
</style>