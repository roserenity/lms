<template>
  <div class="d-flex flex-column align-center my-5">
    <v-hover v-slot="{ hover }">
      <section>
        <v-avatar @click="dialog = true" :size="!mini ? 90 : 60" v-if="hover" color="transparent" class="my-7 pointer-cursor d-flex flex-column" id="avatar-camera" style="border: 5px solid">
          <v-icon color="secondary-4">
            mdi-camera
          </v-icon>
        </v-avatar>
        <v-avatar color="secondary-5" :size="!mini ? 90 : 60" class="my-7 pointer-cursor" id="avatar-profile" style="border: 5px solid" :style="hover && 'opacity: 0.5'">
          <img
            alt="Avatar"
            :src="role=='Super' ? require('../../assets/default-photo.png')
            : user.image ? user.image.url : require('../../assets/default-photo.png')"
            v-on:error="require('../../assets/default-photo.png')"
          >
        </v-avatar>
      </section>
    </v-hover>
    <div class="poppins f14 fw600 text-center text-capitalize" :class="getColorByBgColor" v-if="!mini">
      {{role=='Super'?'Platform Admin': name}}
    </div >
    <div class="poppins f14 fw600 text-center white--text text-capitalize" v-else :class="getColorByBgColorMini">
      {{name ?  name : `${role} Account`}}
    </div>
    <div class="grey--text text-uppercase f12" v-if="!mini">{{role === 'user' ? 'LEARNER' : role}}</div>

    <PhotoUpload :modal="dialog" @close="dialog = false" :type="'profile'" :role="'usr'"/>
    <!-- <input class="d-none" type="file" ref="file" accept=".jpeg,.png,.jpg" @change="accept"/> -->
  </div>
</template>

<style scoped>
#avatar-camera {
  position: absolute;
  z-index: 1;
}
</style>

<script>
import { mapState } from 'vuex'
import PhotoUpload from './PhotoUpload.vue';

export default {
  props: ['name', 'role', 'mini', 'user'],
  data: () => ({
    asset_path: `${process.env.VUE_APP_ASSET_PATH}`,
    dialog: false,
  }),
  components: {
    PhotoUpload
  },
  computed: {
    ...mapState({
      customization: (state) => state.customization
    }),
    
    getColorByBgColor() {
      if(this.customization.hasOwnProperty('navigation_background_color')) {
        if(!this.customization.navigation_background_color) return '#000000'
        return (parseInt(this.customization.navigation_background_color.replace('#', ''), 16) > 0xffffff / 2) ? 'black--text' : 'white--text';
      } else return '#000000'
      
    },

    getColorByBgColorMini() {
      if(this.customization.hasOwnProperty('mini_navigation_background_color')) {
        if(!this.customization.mini_navigation_background_color) return '#000000'
        return this.customization.mini_navigation_background_color ? ((parseInt(this.customization.mini_navigation_background_color.replace('#', ''), 16) > 0xffffff / 2) ? 'black--text' : 'white--text') : 'white--text';
      } else return '#000000';
    }
  },
  methods: {
    uploadPhoto() {

    }
  }
}
</script>