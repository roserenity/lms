<template>
    <v-sheet class="pa-5 my-3 custom-border border" outlined rounded="lg">
      <div class="d-flex align-start mb-5">
        <v-avatar size="40">
          <v-img :src="item.user.image?item.user.image.url: require('@/assets/default-photo.png')"
            v-on:error="require('@/assets/default-photo.png')"/>
        </v-avatar>
        <v-sheet class="ml-3 flex-grow-1">
          <div class="d-flex align-start">
            <div class="d-flex flex-column">
              <div class="d-inline f15 secondary-2--text fw500 roboto mr-2">{{ user.first_name ? user.first_name : 'Instructor' }} {{ user.last_name ? user.last_name : 'Account' }} {{ user.suffix ? user.suffix : '' }}</div>
              <v-chip class="poppins f12 fw500 text-wrap py-1" height="100%" dense> {{ item.course.title }} </v-chip>
            </div>
            <v-spacer></v-spacer>
            <v-menu offset-y>
              <template v-slot:activator="{ on, attrs }">
                <v-btn
                  icon
                  class="float-right"
                  v-bind="attrs"
                  v-on="on"
                >
                  <v-icon >
                    mdi-dots-vertical
                  </v-icon>
                </v-btn>
              </template>
                <v-list>
                  <v-list-item @click="setAllowComment(!item.allow_comments)">
                    <v-list-item-title class="roboto f14 d-flex align-center">
                        <v-icon v-if="!item.allow_comments" size="18" class="mr-2">mdi-comment-check-outline</v-icon>
                        <v-icon v-if="item.allow_comments" size="18" class="mr-2">mdi-comment-remove-outline</v-icon>
                        <div v-if="!item.allow_comments" >Allow comments</div>
                        <div v-if="item.allow_comments">Disallow comments</div>
                    </v-list-item-title>
                  </v-list-item>

                  <v-list-item @click="$emit('updateAnnouncement', item)">
                    <v-list-item-title class="roboto f14 d-flex align-center">
                        <v-icon size="18" class="mr-2">mdi-pencil-outline</v-icon>
                        <div class="">Edit Post</div>
                    </v-list-item-title>
                  </v-list-item>

                  <v-list-item @click="$emit('remove', item.id)">
                    <v-list-item-title class="roboto f14 d-flex align-center">
                        <v-icon size="18" class="mr-2">mdi-trash-can-outline</v-icon>
                        <div class="">Delete Post</div>
                    </v-list-item-title>
                  </v-list-item>
                </v-list>
            </v-menu>
            <v-btn icon small class="mt-1" @click="$emit('setAnnouncement', null)">
              <v-icon>mdi-close</v-icon>
            </v-btn>
          </div>
        </v-sheet>
      </div>
      <v-divider/>
      <LinearProgress v-if="loading"/>
      <div class="my-5">
        <v-sheet
          color="transparent"
          class="roboto mb-2 f14 pa-3 fw500 secondary-1--text text-justify" 
          v-html="item.content ? $dompurifier(item.content) : ''"
          style="white-space: pre; text-wrap: wrap;"
        />
        <div class="d-flex justify-space-between secondary-4--text f12 align-start">
          <span class="px-3 f12 secondary-2--text roboto">{{$dateFormat.calendar(item.created_at)}}</span>
          <span class="px-3 f12 secondary-2--text roboto">{{$dateFormat.calendar(item.created_at)}}</span>
        </div>
        
      </div>
       <div class="mt-5">
        <v-form @submit.prevent="submitComment">
          <v-text-field
            filled 
            class="noline my-3 general-custom-field roboto f14 secondary-1--text fw500" 
            dense
            placeholder="Comment"
            hide-details
            :loading="commentLoading"
            v-model="comment"
            append-icon="mdi-send-outline"
            @click:append="submitComment"
          />
        </v-form>
      </div>
      <v-divider class="my-7"></v-divider>
  
      <div class="text-center font-italic secondary--text f12" v-if="!item.allow_comments"> Disabled comments</div>
      <v-container fluid class="pa-0 ma-0" >
        <ProfileCard 
          v-if="!selected_announcement_id && item.comments.length > 0" 
          :item="item.comments[0]" 
          action="reply" 
          :level = "1"
          :id="`comment-${item.comments[0].id}`"
          :selected_announcement_comment_id="selected_announcement_comment_id" 
          @getData="$emit('getData')"/>
        <div v-else >
          <ProfileCard 
            v-for="(comment, i) in item.comments" 
            :key="i" 
            :item="comment" 
            action="reply" 
            class="my-3" 
            :id="`comment-${comment.id}`"
            :selected_announcement_comment_id="selected_announcement_comment_id" 
            :level = "1"
            @getData="$emit('getData')"/>
        </div>
      </v-container>
      
      <v-btn v-if="item.comments.length > 1 && !selected_announcement_id" text @click="$emit('setAnnouncement', item.id)" class="mt-3 f12 text-capitalize secondary-2--text">
        Show all comments...
      </v-btn>
      
    </v-sheet>
  </template>
  
  <script>
  import { mapActions, mapMutations } from 'vuex';
  import ProfileCard from './ProfileCard.vue';
  export default {
      props: ["item", "user", "selected_announcement_id", "selected_announcement_comment_id", "action", 'level'],
      data: () => ({
        loading: false,
        commentLoading: false,
        comment: null,
      }),
      methods: {
        ...mapActions('instructor', ['getInstructorAllAnnouncementsAction', 'updateAnnouncementAction', 'submitCommentAction']),
        ...mapMutations(['alertMutation']),

        submitComment(){
          this.commentLoading = true

          this.submitCommentAction({
            comment: this.comment,
            user_id: this.$store.state.user.id,
            parent_id: null,
            commentable_id: this.item.id,
            announcement_id: this.announcement_id
          }).then(res => {
            this.comment = ''
            this.addReply = false
            this.commentLoading = false
            this.$emit('getData')
          }).catch(res => {
            this.alertMutation({
              show: true,
              text: "Something went wrong.",
              type: "error"
            }) 
            this.commentLoading=false
          })
        },

        setAllowComment(val){
          this.updateAnnouncement({
            ...this.item,
            allow_comments: val
          })
        },

        updateAnnouncement(val) {
          this.loading = true
          this.updateAnnouncementAction(val).then(res => {
            this.alertMutation({
              show: true,
              text: res.data.message,
              type: "success"
            })
            this.loading = false
            this.$emit('getData')
          }).catch(() => {
            this.alertMutation({
              show: true,
              text: "Something went wrong.",
              type: "error"
            })
            this.loading = false
          })
        }
      },
      components: { ProfileCard }
  }
  </script>