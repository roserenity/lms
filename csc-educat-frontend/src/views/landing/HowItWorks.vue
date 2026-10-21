<template>
    <v-container class="pb-10 mt-3">
        <v-row class="primary-font">
            <v-col cols="12" md="6" class="py-3 f14 d-flex flex-column justify-center">
                <span class="f20 fw600">How it Works</span>
                <v-divider class="l-primary my-5" width="70%" />
                <v-list dense>
                    <v-list-item v-for="(item, i) in steps" :key="i">
                        <v-list-item-avatar>
                            <v-avatar class="primary-fade">
                                <v-icon 
                                    tile
                                    color="l-primary"
                                    v-text="item.icon">
                                </v-icon>
                            </v-avatar>
                        </v-list-item-avatar>
                        <v-list-item-content class="primary-font f16">
                            {{ item.text }}
                        </v-list-item-content>
                    </v-list-item>
                </v-list>
            </v-col>
            <v-col cols="12" md="6" class="d-flex align-center justify-center">
                <div class="container-frame">
                    <iframe
                        :src="`${asset_path}/landing_page/how_it_works.mp4`"
                        class="responsive-iframe"
                    />
                </div>
            </v-col>
        </v-row>
    </v-container>
</template>

<style scoped>
.primary-fade {
    background-color: rgba(224, 88, 53, 0.1);
}

.container-frame {
  position: relative;
  width: 100%;
  overflow: hidden;
  padding-top: 56.25%; /* 16:9 Aspect Ratio */
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

</style>

<script>
import {steps} from '@/constants/steps'

export default {
    name: 'how-it-works',
    data: ()=>({
        steps,
        asset_path: `${process.env.VUE_APP_ASSET_PATH}`
    })
}
</script>