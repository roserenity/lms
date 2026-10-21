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
            <div class="d-inline f14 primary--text fw500 roboto mr-2">{{ item.user.full_name }}</div>
            
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
        </div>
      </v-sheet>
    </div>
    <v-divider/>
    <LinearProgress v-if="loading"/>
    <div class="my-5">
      <v-sheet
        color="transparent"
        class="roboto mb-2 f14 pa-3 fw500 secondary-1--text text-justify overflow-hidden" 
        style="white-space: pre; text-overflow: ellipsis; text-wrap: wrap;"
        v-html="item.content ? $dompurifier(item.content) : ''"
        height="90"
      />
      <div class="d-flex justify-space-between secondary-4--text f12 align-start">
        <span class="px-3 f12 secondary-2--text roboto" v-if="item.comments_count > 0">{{item.comments_count}} {{ item.comments_count > 1 ? 'Comments' : 'Comment'}}</span>
        <span class="px-3 f12 secondary-2--text roboto">{{$dateFormat.calendar(item.created_at)}}</span>
      </div>

      <div class="text-center font-italic secondary--text f12" 
        v-if="!item.allow_comments"> 
        <v-divider class="my-3"/>
        Disabled comments
      </div>

    </div>
    
  </v-sheet>
</template>

<script>
import { mapActions, mapMutations } from 'vuex';
export default {
    props: ["item", "action", 'level', 'selected_announcement_id'],
    data: () => ({
      loading: false,
      comment: null,
    }),
    methods: {
      ...mapActions('admin', ['getInstructorAllAnnouncementsAction', 'updateAnnouncementAction']),
      ...mapMutations(['alertMutation']),

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
}
</script>