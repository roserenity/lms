<template>
  <section>
    <v-sheet fluid class="d-flex border my-2 py-2 px-1"  :class="selected_announcement_comment_id == item.id && 'selected_comment'" :style="{'--hightlightColor': getHighlight}">
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
        <v-form v-if="this.addReply && (replybox.filter( e =>{ return e.reply_id === this.item.id })[0].value)" @submit.prevent="submitReply">
          <v-text-field
            filled 
            class="noline my-3 general-custom-field roboto f14 secondary-1--text fw500" 
            dense
            placeholder="Write your reply"
            hide-details
            v-model="reply"
            :loading="loading"
            append-icon="mdi-send-outline"
            @click:append="submitReply"
          />
        </v-form>
      </div>
    </v-sheet>
    <ProfileCard  
      v-for="(replies, i) in item.replies" :key="i"
      :id="`comment-${replies.id}`"
      action="reply" 
      :item="replies"
      icon="mdi-reply-outline"
      :class="`ml-${level + 6}`"
      @getData="$emit('getData')"
      @closeParentReply="closeParentReply"
      :selected_announcement_comment_id="selected_announcement_comment_id"
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
import { mapActions, mapState, mapMutations } from 'vuex';

export default {
  name: 'ProfileCard', 
  props: ['item', 'showAllComments', 'action', 'selected_announcement_id', 'selected_announcement_comment_id', 'level'],
  data: () => ({
    addReply: false,
    reply: null,
    loading: false,
    asset_path: `${process.env.VUE_APP_ASSET_PATH}`,
  }),
  computed: {
    ...mapState({
      replybox: (state) => state.replybox,
    }),

    getHighlight(){
      return this.$vuetify.theme.currentTheme['primary-1']
    },
  },
  methods: {
    ...mapActions('instructor', ['getInstructorAllAnnouncementsAction', 'getInstructorAnnouncementAction', 'submitCommentAction']),
    ...mapMutations(['alertMutation']),

    submitReply(){
      this.loading = true
      this.submitCommentAction({
        comment: this.reply,
        user_id: this.$store.state.user.id,
        parent_id: this.item.id,
        commentable_id: this.item.commentable_id,
        announcement_id: this.announcement_id
      }).then(res => {
        this.addReply = false
        this.loading=false
        this.reply = ''
        this.$emit('getData')
      }).catch(() => {
        this.loading=false
        this.addReply = false
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