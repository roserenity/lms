<template>
  <section>
    <v-sheet fluid class="d-flex my-2 border py-2 px-1" :class="selected_announcement_comment == item.id && level !== 1 && 'selected_comment'" :style="{'--hightlightColor': getHighlight}">
      <div class="mr-1">
        <v-avatar size="40">
          <v-img 
            :src="item.user.image?item.user.image.url: require('../../../assets/default-photo.png')"
            v-on:error="require('../../../assets/default-photo.png')"/>
        </v-avatar>
      </div>
      <div class="flex-grow-1 roboto">
        <v-sheet color="secondary-5 rounded pa-2">
          <div class="secondary-2--text f13 fw500">
            {{`${item.user.first_name ? item.user.first_name : 'Instructor'} ${item.user.last_name ? item.user.last_name : 'Account'}`}}
          </div>
          <!-- <v-textarea class="secondary-1--text f14 noline mb-0" v-model="action == 'reply' 
            ? item.comment 
            : item.content"
            readonly
            auto-grow
            hide-details
            rows="1"
            row-height="15"
          /> -->
          <div class="secondary-1--text f14 text-justify mb-0" 
            v-html="action == 'reply' 
            ? item.comment 
            : item.content"/>
        </v-sheet>
          
        <div class="d-flex flex-wrap secondary-4--text f10 align-center">
          {{$dateFormat.calendar(item.created_at)}} 
          <span class="ml-1" v-if="action == 'comment'">
            <v-btn @click="$emit('comment')" text color="primary" small class="d-flex align-center f12 text-capitalize poppins00 secondary-4--text">
              <v-icon size="10" class="mr-1">{{icon}}</v-icon>
              {{ comments }} comments
            </v-btn>
          </span> 
          <v-btn v-if="action == 'reply' && allow_comments && level < 3 && !loading" @click="replyClick( item.id )" text color="primary" small class="text-capitalize poppins00">
            <v-icon size="12">{{icon}}</v-icon>
            reply
          </v-btn>
        </div>
        <v-form  v-if="this.addReply && allow_comments && (replybox.filter( e =>{ return e.reply_id === this.item.id })[0].value)" @submit.prevent="submit_reply">
          <v-text-field
            filled 
            class="noline my-3 general-custom-field roboto f14 secondary-1--text fw500" 
            dense
            placeholder="Write your reply"
            hide-details
            v-model="reply"
            :loading="loading"
            append-icon="mdi-send-outline"
            @click:append="submit_reply"
          />
        </v-form>
      </div>
    </v-sheet>
    <div>
      <ProfileCard  
        v-for="(replies, i) in item.replies" :key="i"
        :id="`comment-${replies.id}`" 
        action="reply" 
        :item="replies"
        icon="mdi-reply-outline"
        :class="`ml-${level + 6}`"
        :allow_comments="allow_comments"
        @closeParentReply="closeParentReply"
        :selected_announcement_comment="selected_announcement_comment"
        :level = "level + 1"/>
    </div>
  </section>
</template>

<style scoped>
p {
  margin-bottom: 5px !important;
}

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
import { mapActions, mapState } from 'vuex';

export default {
  name: 'ProfileCard', 
  props: ['action', 'icon', 'item', 'announcement_id', 'selected_announcement_comment', 'comments', 'allow_comments', 'level'],
  data: () => ({
    addReply: null,
    reply: null,
    loading: false,
    asset_path: `${process.env.VUE_APP_ASSET_PATH}`
  }),
  computed: {
    ...mapState({
      replybox: (state) => state.replybox,
    }),
    ...mapState('usr', {
      announcement: (state) => state.announcements,
    }),
    getHighlight(){
      return this.$vuetify.theme.currentTheme['primary-1']
    },
  },
  methods: {
    ...mapActions('usr', ['getAnnouncementAction']),

    async submit_reply(){
      this.loading = true
      await this.$api.post(`/user/announcement/comments`, {
        comment: this.reply,
        user_id: this.$store.state.user.id,
        parent_id: this.item.id,
        commentable_id: this.item.commentable_id,
        announcement_id: this.announcement_id
      }).then(res => {
        this.reply = null
        this.comment = ''
        this.getAnnouncementAction(this.announcement[0].id).then(()=>{
          this.addReply = false
          this.loading = false
        }).catch(() => {
          this.addReply = false
          this.loading = false
        })
      }).catch(() => {
        this.loading = false
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
    }
  }
}
</script>