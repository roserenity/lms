<template>
    <v-card width="200" class="custom-border border">
        <v-img 
            v-if="image"
            :src="image.url"
            :lazy-src="image.url"
            contain
            height="150"
            class="align-end"
            gradient="to top, rgba(0,0,0,.1), rgba(0,0,0,.5)"
        >
            <v-sheet class="pa-1">
                <v-row align="center">
                    <v-col :cols="trash ? 8 : 9">
                        <div class="secondary-2--text f11 text-uppercase">
                            Uploaded on {{ $dateFormat.mmDDyy(image.created_at) }} 
                        </div>
                    </v-col>
                    <v-col cols="1">
                        <v-btn
                            color="l-primary"
                            plain
                            class="f10"
                            block
                            @click="view(image.url)"
                            icon
                            dense
                        >
                            <v-icon small>mdi-eye-outline</v-icon>
                        </v-btn>
                    </v-col>
                    <v-col cols="1" v-if="trash">
                        <v-btn
                            color="l-primary"
                            plain
                            class="f10"
                            block
                            @click="$emit('delete')"
                            icon
                            dense
                        >
                            <v-icon small>mdi-trash-can-outline</v-icon>
                        </v-btn>
                    </v-col>
                    <!-- <v-col cols="6">
                        <v-btn
                            :loading="loading"
                            color="primary"
                            plain
                            class="f10"
                            @click="download(certificate.image.url)"
                        >
                            DOWNLOAD
                        </v-btn>
                    </v-col> -->
                </v-row>
            </v-sheet>
        </v-img>
        <v-dialog
            v-model="dialog"
            persistent
            :width="$vuetify.breakpoint.mobile ? '80vw' : '40vw'"
            >
            <v-card>
                <v-btn icon @click="close" class="ml-auto float-right" dense>
                    <v-icon>mdi-close</v-icon>
                </v-btn>
                <div class="container-frame">
                    <iframe 
                        class="responsive-iframe ma-auto"
                        :src="`${selected}`">
                    </iframe>
                </div>
            </v-card>
        </v-dialog>
    </v-card>
</template>

<style scoped>
    .container-frame {
        position: relative;
        overflow: hidden;
        width: 100%; /* 16:9 Aspect Ratio (divide 9 by 16 = 0.5625) */
    }

    .responsive-iframe {
        height: 600px;
    }

    @media only screen and (max-width: 768px){
        .responsive-iframe {
            height: 400px;
            width: 100%;
        }
    }
</style>

<script>
import trash from '@/store/instructor/actions/trash';

export default {
    props: {
        image: {
            default: null,
            type: Object || null
        },
        trash: {
            default: true,
            type: Boolean
        }
    },
    data: () => ({
        dialog: false,
        selected: null,
        loading: false
    }),
    methods: {
        view(url){
            this.dialog = true
            this.selected = url
        },
        close () {
            this.dialog = false
        },
        trash(){

        }
    }
}
</script>