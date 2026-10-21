<template>
    <v-sheet class="pa-5 my-3 custom-border cursor-pointer" outlined rounded="lg">
      <div class="d-flex align-start mb-5">
        <v-avatar size="40">
          <v-img :src="item.user.image?item.user.image.url: require('@/assets/default-photo.png')"
            v-on:error="require('@/assets/default-photo.png')"/>
        </v-avatar>
        <v-sheet class="ml-3 flex-grow-1">
          <div class="d-flex align-start">
            <div class="d-flex flex-column">
              <div class="d-flex flex-row align-start">
                <div class="d-inline f14 primary--text fw500 roboto mr-1">{{ item.user.full_name }}</div>
                <div class="d-inline f14 secondary-1--text fw400 roboto mr-2">({{ item.user.email }})</div>
              </div>
              <div class="d-flex flex-row mt-1">
                <v-chip class="poppins f12 fw500 text-wrap py-1 mr-1" height="100%" dense v-if="item.course"> {{ item.course.title }} </v-chip>
                <v-chip class="poppins f12 fw500 text-wrap py-1" height="100%" dense v-if="topic_categories.find((category) => category.value === item.topic_category)"> {{ topic_categories.find((category) => category.value === item.topic_category).text }} </v-chip>
              </div>
            </div>
            <v-spacer></v-spacer>
            <div class="d-flex flex-row">
              <v-icon size="18" class="mr-2" color="primary" v-if="item.is_pinned === 1"> mdi-pin </v-icon>
              <v-menu offset-y>
                <template v-slot:activator="{ on, attrs }">
                  <v-btn
                    icon
                    class="float-right"
                    v-bind="attrs"
                    v-on="on"
                    v-if="role === 'user' ? user.id === item.user_id : true"
                  >
                    <v-icon >
                      mdi-dots-vertical
                    </v-icon>
                  </v-btn>
                </template>
                  <v-list>
    
                    <v-list-item @click="pinPost(item)" v-if="role !== 'user'">
                      <v-list-item-title class="roboto f14 d-flex align-center">
                        <v-progress-circular
                                v-if="pinLoading"
                                    indeterminate
                                    rounded
                                    size="20"
                            ></v-progress-circular>
                          <div v-else class="d-flex flex-row">
                            <v-icon size="18" class="mr-2"> {{ item.is_pinned === 0 ? 'mdi-pin-outline' : 'mdi-pin' }}</v-icon>
                            <div class="">{{ item.is_pinned === 0 ? 'Pin' : 'Unpin' }}</div>
                          </div>
                      </v-list-item-title>
                    </v-list-item>

                    <v-list-item @click="$emit('update', item)">
                      <v-list-item-title class="roboto f14 d-flex align-center">
                          <v-icon size="18" class="mr-2">mdi-pencil-outline</v-icon>
                          <div class="">Edit Post</div>
                      </v-list-item-title>
                    </v-list-item>
    
                    <v-list-item @click="() => { user.id === item.user_id ? $emit('delete', item.id) : removePost(item)}">
                      <v-list-item-title class="roboto f14 d-flex align-center">
                        <v-progress-circular
                            v-if="removeLoading"
                                indeterminate
                                rounded
                                size="20" />
                        <div v-else class="d-flex flex-row">
                          <v-icon size="18" class="mr-2">mdi-trash-can-outline</v-icon>
                          <div class="">{{ user.id === item.user_id ? 'Delete' : 'Remove' }} Post</div>
                        </div>
                      </v-list-item-title>
                    </v-list-item>
                  </v-list>
              </v-menu>
            </div>
          </div>
        </v-sheet>
      </div>
      <v-divider/>
      <LinearProgress v-if="loading"/>
      <div class="my-5">
        <h3 class="mx-3">{{ item.title }}</h3>
        <v-sheet
          color="transparent"
          class="roboto mb-2 f14 pa-3 fw500 secondary-1--text text-justify overflow-hidden" 
          style="white-space: pre; text-overflow: ellipsis; text-wrap: wrap;"
          v-html="item.content ? $dompurifier(item.content) : ''"
          :height=" showFull ? 'auto' : 90"
        />
        <div class="d-flex flex-wrap mx-3"  v-if="showFull">
            <AttachmentCard  v-for="(attachment, i) in item.attachments" :key="i"  v-if="['jpg', 'png', 'jpeg'].includes(attachment.file_type)" :attachment="attachment" class="ma-1 my-2" :class="$vuetify.breakpoint.mobile"/>
        </div>
        <div class="text-decoration-underline cursor-pointer poppins f12 fw500  mb-5 mx-3" v-for="(attachment, i) in item.attachments" :key="i" v-if="showFull">
          <a
            v-if="!['jpg', 'png', 'jpeg'].includes(attachment.file_type)"
              :href="attachment.file_path"
              class="primary--text text-decoration-none f10"
              target="_blank"
              >
              <v-icon small class="mr-1" dense>mdi-open-in-new</v-icon>
              {{ attachment.file_name }}
          </a>
        </div>
        <div class="d-flex justify-space-between secondary-4--text f12 align-start">
          <span class="px-3 f12 secondary-2--text roboto" v-if="item.comments_count > 0">{{item.comments_count}} {{ item.comments_count > 1 ? 'Comments' : 'Comment'}}</span>
          <span class="px-3 f12 secondary-2--text roboto">{{$dateFormat.calendar(item.created_at)}}</span>
        </div>
      </div>
      <div class="mt-5" v-if="showComment">
        <div v-if="item.status == 'APPROVED'">
          <section class="d-flex align-center">
            <v-btn type="submit" color="primary" icon elevation="0" small dense class="mr-2" @click="addAttachment = !addAttachment">
              <v-icon dense>
                mdi-plus
              </v-icon>
            </v-btn>
            <v-form @submit.prevent="submitComment" class="d-flex flex-grow-1">
              <v-text-field
                filled 
                class="noline my-3 general-custom-field roboto f14 secondary-1--text fw500" 
                dense
                placeholder="Comment"
                hide-details="auto"
                :loading="commentLoading"
                v-model="form.comment"
                append-icon="mdi-send-outline"
                @click:append="submitComment"
                :error-messages="errorMessage"
              />
            </v-form>
          </section>
          <div v-if="addAttachment">
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
          <v-divider class="my-7" v-if="item.comments.length > 0"></v-divider>
            <PostComment 
              v-if="!selected_forum_post_id && item.comments.length > 0" 
              :item="item.comments[0]" 
              action="reply" 
              :level = "1"
              :id="`comment-${item.comments[0].id}`"
              :selected_forum_post_id="selected_forum_post_id" 
              :selected_forum_post_comment_id="selected_forum_post_comment_id"
              @getPost="$emit('getPost')"/>
          <div v-else >
            <PostComment 
              v-for="(comment, i) in item.comments" 
              :key="i" 
              :item="comment" 
              action="reply" 
              class="my-3" 
              :id="`comment-${comment.id}`"
              :selected_forum_post_id="selected_forum_post_id" 
              :selected_forum_post_comment_id="selected_forum_post_comment_id"
              :level = "1"
              @getPost="$emit('getPost')"/>
          </div>
        </div>
      </div>
    </v-sheet>
  </template>
  
  <script>
  import { mapActions, mapMutations, mapState } from 'vuex';
  import PostComment from './PostComment.vue';
  import { topic_categories } from '@/constants/forum';
  import AttachmentCard from './AttachmentCard.vue';

  export default {
      components: {
        PostComment,
        AttachmentCard
      },
      props: {
        item: {
          default: null,
          type: Object || null
        },
        showComment: {
          default: false,
          type: Boolean
        },
        selected_forum_post_id: {
          default: null,
          type: Number || null
        },
        selected_forum_post_comment_id: {
          default: null,
          type: Number || null
        },
        role: {
          default: 'user',
          type: String
        },
        showFull: {
          default: false,
          type: Boolean
        },
      },
      data: () => ({
        loading: false,
        commentLoading: false,
        pinLoading: false, 
        removeLoading: false,
        topic_categories,
        addAttachment: false,
        form: {
          comment: null,
          attachments: []
        },
        err: '',
        errorMessage: []
      }),
      computed: {
        ...mapState({
            user: (state) => state.user,
        }),
      },
      methods: {
        ...mapActions({
            postForumComment(dispatch) {
              let formData = new FormData()
              formData.append('comment', this.form.comment)
              formData.append('user_id', this.$store.state.user.id)
              formData.append('commentable_id', this.item.id)
              formData.append('announcement_id', this.announcement_id)
              
              this.form.attachments.forEach((item, i) => {
                formData.append(`attachments[${i}]`, item)
              });

              return dispatch(`${this.role === 'user' ? 'usr' : this.role}/postForumCommentAction`, formData);
            },
            pinForumComment(dispatch, item) {
              return dispatch(`${this.role === 'user' ? 'usr' : this.role}/updateForumPostAction`, { ...item, is_pinned: !item.is_pinned});
            },
            removeForumPost(dispatch, item) {
              return dispatch(`${this.role === 'user' ? 'usr' : this.role}/updateForumPostAction`, { ...item, status: "REMOVED"});
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
          
        submitComment(){
          this.commentLoading = true
          this.err = ''

          this.postForumComment().then(res => {
            this.form.comment = null
            this.form.attachments = []
            this.commentLoading = false
            this.$emit('getPost')
          }).catch(res => {
            if(res.comment) {
              this.errorMessage = ['The message contains inappropriate or banned words.']
            }
            this.alertMutation({
              show: true,
              text: "Something went wrong.",
              type: "error"
            }) 
            this.commentLoading=false
          })
        },

        pinPost(item) {
          this.pinLoading = true
          this.pinForumComment(item).then(res => {
            this.alertMutation({
              show: true,
              text: "Post successfully pinned",
              type: "success"
            }) 
            this.$emit('getData')
            if(this.selected_forum_post_id) {
              this.$emit('getPost')
            }
            this.pinLoading = false
          }).catch(res => {
            this.alertMutation({
              show: true,
              text: "Something went wrong.",
              type: "error"
            }) 
            this.pinLoading = false
          })
          
        },

        removePost(item){
          this.removeLoading = true
          this.removeForumPost(item).then(res => {
            this.alertMutation({
              show: true,
              text: "Post successfully removed",
              type: "success"
            }) 
            this.$emit('getData')
            if(this.selected_forum_post_id) {
              this.$emit('getPost')
            }
            this.removeLoading = false
            this.$emit('removeSelectedForumPostId')
          }).catch(res => {
            this.alertMutation({
              show: true,
              text: "Something went wrong.",
              type: "error"
            }) 
            this.removeLoading = false
          })
        }
      },
  }
  </script>