<template>
    <v-card width="200" class="custom-border">
        <v-img 
            alt="certificate"
            :src="require('@/assets/images/certificate-default.png')"
            :lazy-src="require('@/assets/images/certificate-default.png')"
            contain
            height="150"
            class="align-end"
            gradient="to top, rgba(0,0,0,.1), rgba(0,0,0,.5)"
        >
            <v-sheet class="px-3 pt-1 fw600 text-center ">
                {{ !certificate.course ? 'Untitled' : certificate.course.title.length > 20 ? `${certificate.course.title.slice(0, 17)}...` : certificate.course.title}}
                <div class=" py-0 secondary-2--text f11 text-uppercase">
                    ISSUED {{ $dateFormat.mmDDyy(certificate.updated_at) }} 
                </div>
            </v-sheet>
            <v-sheet>
                <v-row no-gutters>
                    <v-col cols="6">
                        <v-btn
                            color="l-primary"
                            plain
                            class="f10"
                            block
                            @click="view(certificate.image.url)"
                        >
                            VIEW
                        </v-btn>
                    </v-col>
                    <v-col cols="6">
                        <v-btn
                            :loading="loading"
                            color="primary"
                            plain
                            class="f10"
                            @click="download(certificate.image.url)"
                        >
                            DOWNLOAD
                        </v-btn>
                    </v-col>
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
                        class="responsive-iframe"
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
    height: 500px;
    }

    @media only screen and (max-width: 768px){
    .responsive-iframe {
        height: 300px;
        width: 100%;
    }
    }

</style>

<script>
import { mapActions
 } from 'vuex'
export default {
    props: ['certificate'],
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
        download(url){
            this.downloadCourseCertificateAction({url: url}).then(res =>  
                //console.log(res)
                window.open(res)
            ).catch(errs => 
                this.alertMutation({
                    show: true,
                    text: 'Unexpected error encountered while downloading.',
                    type: "error"
                })
            )
        }
    }
}
</script>