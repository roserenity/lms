<template>
        <v-dialog
        v-model="dialog"
        fullscreen
        hide-overlay
        class="overflow-hidden"
        transition="dialog-bottom-transition"
        id="dialogTour"
        >
            <v-card class="pa-0 ma-0 overflow-hidden" dark>
                <v-btn icon @click="close" class="float-right" color="white" id="closeButton" large>
                    <v-icon large> mdi-close-circle-outline </v-icon>
                </v-btn>
                    <video controls id="startTourFrame">
                        <source :src="`${asset_path}/tours/start_tour.mp4`">
                    </video>
                    <!-- <iframe
                        :src="`${asset_path}/landing_page/how_it_works.mp4`"
                        class="responsive-iframe"
                    /> -->
                <!-- <iframe
                  id="startTourFrame"
                    :src="`${asset_path}/tours/start_tour.mp4`"
                    class="responsive-iframe"
                /> -->
            </v-card>
        </v-dialog>
</template>

<style scoped>
#closeButton {
  position: absolute;
  z-index: 1;
  top: 0;
  right: 0;
}
.container-frame {
  position: relative;
  width: 100%;
  overflow: hidden;
  /* padding-top: 56.25%; */
}

.responsive-iframe {
  position: absolute;
  top: 0;
  left: 0;
  bottom: 0;
  right: 0;
  width: 100%;
  height: 100%;
  border: none;
}

video {
  /* override other styles to make responsive */
  width: 100%    !important;
  height: 100vh   !important;
}

.v-dialog__content {
  height: 100vh;
}
</style>

<script>
export default {
    props: ["dialog"],
    data: () => ({
      asset_path: `${process.env.VUE_APP_ASSET_PATH}`
    }),
    methods: {
      close(){
        var iframe = document.getElementById("startTourFrame");
        iframe.pause();
        iframe.currentTime = 0;
        iframe.load();
        this.$emit('close')
      }
    }
}
</script>