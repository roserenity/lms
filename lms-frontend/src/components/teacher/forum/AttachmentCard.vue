<template>
    <v-card width="200" class="custom-border">
        <v-img 
            alt="attachment"
            :src="attachment.file_path"
            :lazy-src="attachment.file_path"
            contain
            height="150"
            class="align-end"
            gradient="to top, rgba(0,0,0,.1), rgba(0,0,0,.5)"
        >
            <v-sheet>
                <v-row no-gutters>
                    <v-col cols="12">
                        <v-btn
                            color="l-primary"
                            plain
                            class="f10"
                            block
                            @click="view(attachment.file_path)"
                        >
                            VIEW
                        </v-btn>
                    </v-col>
                </v-row>
            </v-sheet>
        </v-img>
        <v-dialog
            v-model="dialog"
            persistent
            overlay
            >
                <v-sheet>
                    <v-btn icon @click="close" class="ml-auto float-right" dense>
                        <v-icon>mdi-close</v-icon>
                    </v-btn>
                    <div class="iframe-scaler">
                        <v-img 
                            :src="`${selected}`"
                            contain
                        />
                    </div>
                </v-sheet>
        </v-dialog>
    </v-card>
</template>

<style scoped>
    .container-frame {
        position: relative;
        overflow: hidden;
        width: 100%; /* 16:9 Aspect Ratio (divide 9 by 16 = 0.5625) */
    }
    .iframe-scaler {
        width: 100%;
        height: 0;
        padding-bottom: 56.25%; /* 16:9 aspect ratio */
        position: relative;
        overflow: hidden;
    }

    .scaled-iframe {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        transform: scale(1); /* adjust if needed */
        transform-origin: top left;
        border: none;
    }
</style>

<script>
import { mapActions } from 'vuex';

export default {
    props: ['attachment'],
    data: () => ({
        dialog: false,
        selected: null,
        loading: false
    }),
    methods: {
        ...mapActions('usr', ['downloadCourseCertificateAction']),
        view(url){
            this.dialog = true
            this.selected = url
        },
        close () {
            this.dialog = false
        },
    }
}
</script>