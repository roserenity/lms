<template>
  <section>
    <video 
      ref="vidPlayer" 
      class="video-js vjs-fluid vjs-default-skin vjs-big-play-centered responsive-iframe"
      data-setup='{}'
    />
      <!-- <source src='https://vimeo.com/721363602' type='video/vimeo'> </video> -->
    <!-- <video-player 
      ref="vidPlayer" 
      class="vjs-custom-skin"
      :options="options"
    ></video-player> -->
    <!-- <video ref="vidPlayer" class="video-js"/> -->
    <!-- https://player.vimeo.com/video/703963182?h=22467f7489&badge=0&autopause=0&player_id=0&app_id=58479/embed -->
  </section>
</template>
<script>
// import VideoPlayer from 'vue-videojs7'
import videojs from 'video.js'
import 'video.js/dist/video-js.css'
import 'video.js/dist/video.min.js'
import 'videojs-youtube/dist/Youtube.min.js'
// import 'videojs-vimeo/lib/video-js.css'
// import 'videojs-vimeo/lib/video.js'
// const videojs = window.videojs
import { mapState } from "vuex";
export default {
  name: "vid",
  components: {
    // VideoPlayer,
    // Videojs
  },
  props: ["url"],
  data: () => ({
    player: null,
    options: {
      autoplay: true,
      controls: true,
      responsive: true,
      fluid: true,
      sources: [
        {
          // src: 'https://player.vimeo.com/video/703963182?h=22467f7489&badge=0&autopause=0&player_id=0&app_id=58479/embed',
          // src: 'https://vimeo.com/721363602',
          src: 'https://www.youtube.com/watch?v=Yj_ct7BlXV0&ab_channel=ArkVibes',
          type: 'video/youtube'
        }
      ]
    }
  }),
  computed: {
    ...mapState({
      // player: (state) => state.player,
    }),
  },
  mounted() {
    this.player = videojs(this.$refs.vidPlayer, this.options, () => {
      this.player.log('onPlayReady', this)
    })
  }
  // watch: {
  //   "player.action"(val) {
  //     console.log(val);
  //     switch (val) {
  //       case "stop":
  //         return this.$refs.plyr.player.stop();
  //     }
  //   },
  // },
};
</script>
