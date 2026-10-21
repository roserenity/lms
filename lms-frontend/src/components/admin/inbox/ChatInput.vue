<template>
  <div class="mx-4 mt-4">
    <section class="d-flex align-center">
      <v-btn type="submit" color="primary" icon elevation="0" small dense class="mr-2" @click="addAttachment = !addAttachment">
        <v-icon dense>
          mdi-plus
        </v-icon>
      </v-btn>
      <v-form @submit.prevent="send" class="d-flex flex-grow-1">
        <v-text-field
          autofocus
          rounded
          filled 
          class="noline general-custom-field poppins f13 secondary-1--text fw500" 
          dense
          placeholder="Type a message..."
          :loading="loading"
          hide-details="auto"
          :error-messages="err"
          v-model="chat.message"
        />
        <!-- append-icon="mdi-emoticon" -->
        <v-btn type="submit" color="primary" fab elevation="0" small class="ml-2"  :disabled="chat.attachments.length === 0 && chat.message === '' || loading || loadingMessages">
          <v-icon>
            mdi-arrow-right
          </v-icon>
        </v-btn>
      </v-form>
    </section>

    <section  v-if="addAttachment" class="mt-3">
      <div class="mb-5">
        <v-chip 
            v-for="(item, i) in chat.attachments"
            class="ma-1"
            color="primary"
            :key="i"
            small
            outlined
            close
            @click:close="remove(i)"
            >
          <v-icon small left>
              mdi-image
          </v-icon>
            {{ item.name }}
        </v-chip>
      </div>
      <FormUploadFile 
        :accepted_types="'.jpg, .jpeg, .png, .txt, .doc, .docx, .ppt, .pptx, .pdf'"
        :accept="''"
        :accepted_types_list="[]"
        :multiple="true"
        @acceptFile="acceptFile"/>
    </section>
  </div>
</template>

<script>
import { mapActions, mapMutations, mapState } from 'vuex'

export default {
  props: ['friend', 'group', 'user', 'private_chat', 'loadingMessages'],

  data: () => ({
    chat: {
      message: '',
      attachments: [],
    },
    addAttachment: false,
    loading: false,
    err: ''
  }),
  methods: {
    ...mapActions('admin', [
      'sendMessageAction',
      'sendGroupChatAction',
    ]),

    send() {

      this.err = ''

      if(this.friend) {
        let form = new FormData()

        form.append('private_chat_id', this.private_chat ? this.private_chat.id : '')
        form.append('message_body', this.chat.message)
        form.append('to_user_id', this.friend.id)
        form.append('from_user_id', this.user.id)
        form.append("_method", 'POST');

        this.chat.attachments.forEach((item, i) => {
          form.append(`attachments[${i}]`, item)
        });

        this.loading = true
        this.sendMessageAction(form).then(res => {
          this.chat = {
            message: '',
            attachments: [],
          }

          this.addAttachment = false

          this.loading = false

          this.$emit('successSend', res);
        }).catch(err => {
          if(Object.keys(err).some(key => key.startsWith('attachments.'))) {
            this.err = 'File type not supported'
          } else {
            this.err = 'Something went wrong.'
          }
          this.loading = false
        })
      }
      
      if(this.group) {
        
        let form = new FormData()

        form.append('uuid', this.group.uuid)
        form.append('group_chat_id', this.group.id)
        form.append('message_body', this.chat.message)
        form.append('synced_users', false)
        form.append('sender_id', this.user.id)
        form.append("_method", 'POST');

        this.chat.attachments.forEach((item, i) => {
          form.append(`attachments[${i}]`, item)
        });

        this.loading = true
        this.sendGroupChatAction(form).then(() => {
          
          this.chat = {
            message: '',
            attachments: [],
          }

          this.addAttachment = false

          this.$emit('successSend');
          this.loading = false
        }).catch(err => {
          if(Object.keys(err).some(key => key.startsWith('attachments.'))) {
            this.err = 'File type not supported'
          } else {
            this.err = 'Something went wrong.'
          }
          this.loading = false
        })
      }
    },

    acceptFile(e) {
      this.err = ''
      if(this.chat.attachments.length === 5) {
        this.err = 'You could only attach up to 5 files'
      } else {
        this.chat.attachments.push(e)
      }
    },
    
    remove(i){
        this.chat.attachments.splice(i, 1)
    },
  },

  watch: {
    addAttachment(val) {
      if(!val) {
        this.chat.attachments = []
      }
    }
  }
}
</script>