<template>
    <v-dialog
            persistent
            v-model="dialog"
            hide-overlay
            transition="slide-x-reverse-transition"
            transparent
            :width="$vuetify.breakpoint.mobile ? '100vw' : '60vw'"
            right
            id="timeline_dialog"
        >
        <v-card
            id="timelinedialog" :width="$vuetify.breakpoint.mobile ? '100vw' : '60vw'" class="float-right" min-height="100vh">
            <v-container>
                <v-card-title class="d-flex flex-row justify-space-between">
                    <!-- <span class="poppins fw600" style="word-break: break-word;">{{ item.name }}</span> -->
                    <v-btn icon @click="$emit('close')">
                        <v-icon>mdi-close</v-icon>
                    </v-btn>
                </v-card-title>
                <v-card-text>
                    <div class="container-frame">
                        <iframe 
                            class="responsive-iframe"
                            :src="`${item.url}`">
                        </iframe>
                    </div>
                </v-card-text>
            </v-container>
        </v-card>
    </v-dialog>
</template>

<script>
import { mapState } from 'vuex'

export default {
    props: ["dialog", "item"],

    computed: {
        ...mapState({
            customization: (state) => state.customization,
            user: (state) => state.user
        }),
        getPrimary(){
            return this.customization.hasOwnProperty('primary_buttons_color') ? this.customization.primary_buttons_color : this.$vuetify.theme.currentTheme.primary
        },
    }
}
</script>

<style>
.v-dialog:not(.v-dialog--fullscreen) {
    max-height: 100% !important;
    margin: 0px !important;
}
.expansion-timeline .v-expansion-panel-content__wrap {
  padding-bottom: 0 !important;
  
}
</style>

<style scoped>
.v-dialog__content {
    justify-content: flex-end !important;
}

.container-frame {
  position: relative;
  overflow: hidden;
  width: 100%; /* 16:9 Aspect Ratio (divide 9 by 16 = 0.5625) */
}

.responsive-iframe {
  height: 80vh;
}

@media only screen and (max-width: 768px){
  .responsive-iframe {
    width: 100%;
  }
}

</style>