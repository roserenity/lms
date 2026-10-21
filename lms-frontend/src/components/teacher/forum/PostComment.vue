<template>
    <section :id="id">
      <v-sheet fluid class="d-flex border my-2 py-2 px-1"  :class="selected_forum_post_comment_id == item.id && 'selected_comment'" :style="{'--hightlightColor': getHighlight}">
        <div class="mr-2">
          <v-avatar size="40">
            <v-img 
              :src="item.user.image?item.user.image.url: require('../../../assets/default-photo.png')"
              v-on:error="item.user.image = null"/>
          </v-avatar>
        </div>
        <div class="flex-grow-1 roboto">
          <v-sheet color="secondary-5 rounded pa-2">
            <div class="secondary-2--text f14 d-flex flex-wrap">
              {{`${item.user.first_name} ${item.user.last_name} ${item.user.suffix ? item.user.suffix : ''}`}}
            </div>
            <div class="secondary-1--text f14 text-justify mb-0" 
              v-html="action == 'reply' 
              ? item.comment 
              : item.content"/>
            <div class="text-decoration-underline cursor-pointer poppins f12 fw500  my-3" v-for="(attachment, i) in item.attachments" :key="i">
              <a
                v-if="!['jpg', 'png', 'jpeg'].includes(attachment.file_type)"
                  :href="attachment.file_path"
                  class="primary--text text-decoration-none f10"
                  target="_blank"
                  >
                  <v-icon small class="mr-1" dense>mdi-open-in-new</v-icon>
                  {{ attachment.file_name }}
              </a>
              <v-img 
                v-else
                :src="`${attachment.file_path}`"
                height="100px"
                width="100px"
              />
            </div>
          </v-sheet>
          
          <div class="d-flex secondary-4--text f12 align-center">
            {{$dateFormat.calendar(item.created_at)}}
            <v-btn 
                v-if="action == 'reply' && level < 3"
                text 
                color="primary" 
                small 
                class="text-capitalize poppins"
                @click="replyClick( item.id )">
                <v-icon size="12">mdi-reply-outline</v-icon>
                    reply
            </v-btn>
          </div>
            <section class="d-flex align-center" v-if="this.addReply && (replybox.filter( e =>{ return e.reply_id === this.item.id })[0].value)" >
              <v-btn type="submit" color="primary" icon elevation="0" small dense class="mr-2" @click="addAttachment = !addAttachment">
                <v-icon dense>
                  mdi-plus
                </v-icon>
              </v-btn>
              
              <v-form @submit.prevent="submitReply" class=" d-flex flex-grow-1">
                <v-text-field
                  filled 
                  class="noline my-3 general-custom-field roboto f14 secondary-1--text fw500" 
                  dense
                  placeholder="Write your reply"
                  :error-messages="errorMessage"
                  hide-details="auto"
                  v-model="form.reply"
                  :loading="loading"
                  append-icon="mdi-send-outline"
                  @click:append="submitReply"
                />
              </v-form>
            </section>
            <div v-if="addAttachment && this.addReply && (replybox.filter( e =>{ return e.reply_id === this.item.id })[0].value)" >
              <label class="caption">ATTACHMENTS</label>
              <div class="mb-5">
                <v-chip 
                    v-for="(item, i) in form.attachments"
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
                :accepted_types_list="[
                    'image/jpeg',
                    'image/png',
                    'text/plain',
                    'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'application/vnd.ms-powerpoint',
                    'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                    'application/pdf'
                ]"
                :multiple="true"
                @acceptFile="acceptFile"/>

              <v-alert
                v-if="err !== ''"
                type="error"
                transition="v-scroll-y-transition"
                dense
                outlined
                class="mt-3 f13"
              >
                {{ err }}
              </v-alert>
            </div>
        </div>
      </v-sheet>
      <PostComment  
        v-for="(replies, i) in item.replies" :key="i"
        :id="`comment-${replies.id}`"
        action="reply" 
        :item="replies"
        icon="mdi-reply-outline"
        :class="`ml-${level + 6}`"
        @getPost="$emit('getPost')"
        @closeParentReply="closeParentReply"
        :selected_forum_post_id="selected_forum_post_id"
        :selected_forum_post_comment_id="selected_forum_post_comment_id"
        :level="level + 1"/>
    </section>
  </template>
  
  <style scoped>
  .selected_comment {
    animation: hightlight 5s forwards;
  }
  @keyframes hightlight {
      80% {
        background-color: var(--hightlightColor);
      }
      100% {
        background-color: none;
      }
  }
  </style>
  
  <script>
import PostComment from './PostComment.vue';
  import { mapActions, mapState, mapMutations } from 'vuex';
  
  export default {
    name: 'PostComment', 
    components: {
      PostComment
    },
    props: {
      item: {
        default: null,
        type: Object || null
      },
      selected_forum_post_id: {
        default: null,
        type: Number || null
      },
      selected_forum_post_comment_id: {
        default: null,
        type: Number || null
      },
      action: {
        default: '',
        type: String
      },
      level: {
        default: 0,
        type: Number
      },
      id: {
        default: null,
        type: Number
      }
    },
    data: () => ({
      addReply: false,
      reply: null,
      form: {
        reply: null,
        attachments: []
      },
      loading: false,
      asset_path: `${process.env.VUE_APP_ASSET_PATH}`,
      addAttachment: false,
      err: '',
      errorMessage: [],
    }),
    computed: {
      ...mapState({
        replybox: (state) => state.replybox,
        user: (state) => state.user,
      }),
  
      getHighlight(){
        return this.$vuetify.theme.currentTheme['primary-1']
      },
    },
    methods: {
      ...mapActions({
          postForumComment(dispatch) {
              let formData = new FormData()
              formData.append('comment', this.form.reply)
              formData.append('user_id', this.$store.state.user.id)
              formData.append('parent_id', this.item.id)
              formData.append('commentable_id',  this.item.commentable_id)
              formData.append('announcement_id', this.announcement_id)
              
              this.form.attachments.forEach((item, i) => {
                formData.append(`attachments[${i}]`, item)
              });

            return dispatch(`${this.user.role.toLowerCase() === 'user' ? 'usr' : this.user.role.toLowerCase()}/postForumCommentAction`, formData);
          },
      }),

      ...mapMutations(['alertMutation']),

      remove(i){
          this.form.attachments.splice(i, 1)
      },

      acceptFile(e) {
        this.err = ''
        console.log(e)
        console.log(this.form)
        if(this.form.attachments.length === 1) {
          this.err = 'You could only attach up to 1 file'
        } else {
          this.form.attachments.push(e)
        }
      },
  
      submitReply(){
        this.loading = true
        this.err = ''
        this.postForumComment().then(res => {
          this.addReply = false
          this.loading=false
          this.form.reply = ''
          this.form.attachments = []
          this.$emit('getPost')
        }).catch(res => {
          if(res.comment) {
            this.errorMessage = ['The message contains inappropriate or banned words.']
          }
          this.loading=false
          this.alertMutation({
            show: true,
            text: "Something went wrong.",
            type: "error"
          })
        })
      },
  
      closeParentReply() {
        this.addReply = false
      },
  
      replyClick(id){
        this.$emit('closeParentReply')
        this.addReply = true
  
        if( this.replybox.length === 0) {
          this.$store.commit('replyboxMutation', [{ reply_id: id, value: true }])
        } else {
          var _replybox =  this.replybox
          let index = this.replybox.findIndex(item => id === item.reply_id)
          if(index === -1) {
            _replybox.push({ reply_id: id, value: true })
          }
          _replybox.map(item => {
            item.reply_id === id ? item.value = true : item.value = false
          })
  
          this.$store.commit('replyboxMutation', _replybox)
        }
      },
    },
  }
  </script>