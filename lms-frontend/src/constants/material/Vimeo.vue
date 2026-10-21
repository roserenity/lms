<template>
  <section>
    <div v-if="type=='mp4'" id="player" data-plyr-provider="vimeo" :data-plyr-embed-id="uri.replace('/videos/', '')"></div>
    <div v-if="type=='youtube'" id="player" data-plyr-provider="youtube" :data-plyr-embed-id="uri.replace('https://youtube.com/embed/', '')"></div>
    <!-- <div v-if="type=='youtube'"  class="plyr__video-embed" id="player">
      <iframe
        :src="`${uri}?origin=https://plyr.io&amp;iv_load_policy=3&amp;modestbranding=1&amp;playsinline=1&amp;showinfo=0&amp;rel=0&amp;enablejsapi=1`"
        allowfullscreen="true"
        webkitallowfullscreen="true"
        mozallowfullscreen="true"
        allowtransparency
        allow="autoplay"
      ></iframe>
    </div> -->
  </section>
</template>

<script>
import Plyr from 'plyr';
import 'plyr/dist/plyr.css'
import { mapState } from 'vuex';

export default {
  props: ["uri", "type", "data"],

  data: () => ({
    height: 500,
    player: null,
    loaded: false
  }),

  computed: {
    ...mapState({
      vimeo: (state) => state.player
    })
  },

  mounted() {
    const access_payload = localStorage.getItem('access_payload');

    this.player = new Plyr('#player', {
      title: 'sample',
      fullscreen: {
        enabled: true,
        fallback: true,
        iosNative: false,
        container: '#container'
      },
      listeners: {
        // seek (e) {
        //   e.preventDefault();
        //   console.log(`prevented`);
        //   return false;
        // }
      }
    })

    localStorage.setItem('access_payload', access_payload);

    this.player.on('play', _ => {
      this.$emit('start')
    })

    this.player.on('ended', () => {
      this.$emit('end')
    })

    // if(this.type === 'mp4') {
    //   var element = document.querySelector('#player')
    //   element.setAttribute('data-plyr-provider', 'vimeo')
    //   element.setAttribute('data-plyr-embed-id', this.uri.replace('/videos/', ''))
    // } else {
    //   var element = document.querySelector('#player')
    //   element.setAttribute('data-plyr-provider', 'youtube')
    //   element.setAttribute('data-plyr-embed-id', this.uri.replace('https://youtube.com/embed/', ''))
    // }

  },

  methods: {

  },

  created(){
    console.log('created vimeo')
  },

  watch: {
    'vimeo.action'(val) {
      const access_payload = localStorage.getItem('access_payload');
      if(val == 'stop') this.player.stop()
      localStorage.setItem('access_payload', access_payload);
    },

    data(val){
      // this.player.stop()
      // this.player = null

      const access_payload = localStorage.getItem('access_payload');

      var element = document.querySelector('#player')

      if(this.type === 'mp4') {
        var element = document.querySelector('#player')
        element.setAttribute('data-plyr-provider', 'vimeo')
        element.setAttribute('data-plyr-embed-id', this.uri.replace('/videos/', ''))
      } else {
        var element = document.querySelector('#player')
        element.setAttribute('data-plyr-provider', 'youtube')
        element.setAttribute('data-plyr-embed-id', this.uri.replace('https://youtube.com/embed/', ''))
      }

      this.player = new Plyr('#player', {
        title: 'sample',
        fullscreen: {
          enabled: true,
          fallback: true,
          iosNative: false,
          container: '#container'
        },
      })

      localStorage.setItem('access_payload', access_payload);
    }
  },

  beforeDestroy() {
    const access_payload = localStorage.getItem('access_payload');
    localStorage.setItem('access_payload', access_payload);
  },

};
</script>
