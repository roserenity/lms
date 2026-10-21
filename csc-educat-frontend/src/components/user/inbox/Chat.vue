<template>
  <section >
    <v-sheet class="ma-5 d-flex">
      <v-avatar class="" size="70">
        <v-img 
          :src="user.image ? user.image.url : require('@/assets/default-photo.png')"
          v-on:error="user.image = null">
        </v-img>
      </v-avatar>
      <div class="mx-2 mt-4">
        <h4 class="poppins fw500">{{ friend.full_name }} </h4>
        <p class="poppins secondary--text f12">{{friend.role}}</p>
      </div>
    </v-sheet>
    <v-divider/>
    <v-sheet class="overflow-y-auto scroller ma-2 pa-2" id="chat-wrapper" max-height="400" min-height="200">
      <div class="d-flex flex-column justify-end" id="chat-inner-wrapper">
        <div v-for="message in messages.private_messages" :key="message.id" 
          class="mx-3 my-2 d-flex flex-column"
          :class="user.id == message.from_user_id?'align-end':'align-start'">
            <ChatCard :message="message" :user="user"/>
        </div>
      </div>
    </v-sheet>
  </section>
</template>

<script>
import ChatCard from './ChatCard.vue'

export default {
  props: ['friend', 'messages', 'user', 'view'],
  components: {
    ChatCard
  },
  data: () => ({
    viewing: false,
    asset_path: `${process.env.VUE_APP_ASSET_PATH}`
  }),
  mounted() {
    let chat_wrapper = document.getElementById("chat-wrapper");
    chat_wrapper.scrollTop = chat_wrapper.scrollHeight;
    // this.$store.commit('usr/chatWrapperMutation', chat_wrapper)
  },
  watch: {
    view(val) {

      // this.viewing=this.view
      // setTimeout(() => {
        // let chat_wrapper = document.getElementById("chat-wrapper");
        // console.log(chat_wrapper);
        // console.log(chat_wrapper.scrollHeight);
        // chat_wrapper.scrollTop = chat_wrapper.scrollHeight;
      // }, 1000);
      
    }
  }

}
</script>
