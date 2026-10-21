<template>
  <vue-plyr ref="plyr">
    <div class="plyr__video-embed" id="player">
      <iframe
        iframe
        :src="`${url}?origin=https://plyr.io&amp;iv_load_policy=3&amp;modestbranding=1&amp;playsinline=1&amp;showinfo=0&amp;rel=0&amp;enablejsapi=1`"
        allowfullscreen
        allowtransparency
        allow="autoplay"
        height="100%"
        width="100%"
      ></iframe>
    </div>
  </vue-plyr>
</template>

<script>
import { mapState } from "vuex";

export default {
  props: ["url"],
  computed: {
    ...mapState({
      player: (state) => state.player,
    }),
  },

  watch: {
    "player.action"(val) {
      console.log(val);
      switch (val) {
        case "stop":
          return this.$refs.plyr.player.stop();
      }
    },
  },
};
</script>
