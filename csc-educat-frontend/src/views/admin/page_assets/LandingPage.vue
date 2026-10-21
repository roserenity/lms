<template>
    <SkeletonLoaderCard height="400" v-if="loading && backgrounds.length === 0" class=" mt-3"/>
    <v-card class="custom-border border pa-5 fade" v-else>
    
        <div class="d-flex flex-row justify-space-between align-center mb-3">
            <ButtonPrimary 
                @click="showPreview"
                :label="'Preview'"
                :outlined="true"
                class="ml-auto"
            />
        </div>

        <v-row class="mt-5">
            <v-col cols="12" md="6">
                <div
                    class="bulk-border mb-2 d-flex flex-column align-center justify-center pa-5"
                    style="min-height: 300px;"
                    @dragover="dragover"
                    @dragleave="dragleave"
                    @drop="dropPhoto"
                >
                    <v-img
                        v-if="croppedImage"
                        contain
                        height="300"
                        :src="croppedImage"
                        class="d-flex align-center text-center my-3"
                    />
                    <div class="d-flex align-center">
                        <v-icon color="rgba(63, 93, 208, 0.12)" size="22"
                            >mdi-cloud-upload-outline</v-icon
                        >
                        <div class="secondary--text f12 ml-2 poppins">
                            <a href="#" @click="$refs.photo.click()" class="primary--text text-decoration-none" >
                                Browse
                            </a>
                                or drag file here to display in landing page
                        </div>
                    </div>
                </div>
                <alert 
                    v-if="msg.show"
                    :show = "msg.show"
                    :text = "msg.text"
                    :type = "msg.type"
                    class="my-3"
                />
                <div class="d-flex flex-row justify-end mb-3">
                    <v-spacer />
                    <ButtonPrimary 
                        @click="submit"
                        :label="'Upload'"
                        :loading="uploadingSaving"
                        :disabled="!photoBlob"
                    />
                </div>
            </v-col>
            <v-col cols="12" md="6">

                <LinearProgress v-if="backgrounds.length > 0 && loading"/>

                <div v-if="backgrounds.length > 0">
                    <div class="d-flex flex-wrap">
                        <PhotoCard v-for="(item, i) in backgrounds" :key="i" :image="item.image" class="ma-1"  
                            @delete="() => { 
                                selected = item
                                deleteDialog = true
                            }"/>
                    </div>
                </div>

                <v-sheet
                    color="transparent"
                    class="d-flex flex-column align-center justify-center pb-10"
                    height="400"
                    v-if="!loading && backgrounds.length === 0"
                >

                    <v-sheet class="pa-10 d-flex flex-column align-center justify-center">
                        <v-img 
                            max-width="200" 
                            :src="require('@/assets/default/empty_published.png')" />
                        <h3 class="text-center poppins secondary-1--text fw600 my-5 text-center">
                            No background Available
                        </h3>
                        <div class="text-center poppins secondary-1--text f12">
                            There are currently no background uploaded, using the default background.
                        </div>
                    </v-sheet>
                </v-sheet>
            </v-col>
        </v-row>

        <input
            type="file"
            ref="photo"
            class="d-none"
            accept=".png,.jpg,.jpeg"
            @change="acceptPhoto"
        />
        
        <v-dialog max-width="550" v-model="cropperDialog" persistent>
            <v-sheet class="pa-4" rounded="lg">
                <div class="d-flex align-center mb-5">
                    <h3 class="poppins">
                        Crop Background
                    </h3>
                    <v-spacer/>
                    <v-btn icon small @click="close">
                        <v-icon>mdi-close</v-icon>
                    </v-btn>
                </div>
                <v-sheet class="">
                    <img 
                        class="mx-auto"
                        :src="image" 
                        id="image" 
                        ref="img" 
                        style="display: block; max-height:50vh;max-width:500px"
                    />
                    <div class="d-flex align-center justify-end mt-3">
                        <v-btn text @click="reset">Reset</v-btn>
                        <v-btn text @click="upload" :loading="cropperLoading">Crop</v-btn>
                    </div>
                </v-sheet>
            </v-sheet>
        </v-dialog>

        <v-dialog width="600px"  v-model="previewDialog" persistent>
            <v-sheet class="pa-4" rounded="lg">
                <div class="d-flex align-center mb-5">
                    <h3 class="poppins">
                        Preview
                    </h3>
                    <v-spacer/>
                    <v-btn icon small @click="previewDialog = false">
                        <v-icon>mdi-close</v-icon>
                    </v-btn>
                </div>
                <v-sheet height="90%">
                    <Carousel :items="images"/>
                </v-sheet>
            </v-sheet>
        </v-dialog>
        
        <DeleteDialog
            type="Background"
            :dialog="deleteDialog"
            :loading="deleteLoading"
            @close="deleteDialog = false"
            @delete="deleteLandingPageBackground"
        />
    </v-card>
</template>

<script>
import VueCropper from 'vue-cropperjs';
import Cropper from 'cropperjs';
import 'cropperjs/dist/cropper.css'
import PhotoCard from '@/components/reusable/cards/PhotoCard.vue';
import SkeletonLoaderCard from '@/components/reusable/SkeletonLoaderCard.vue';
import Carousel from '@/components/reusable/Carousel.vue';
import { mapActions, mapMutations, mapState } from 'vuex';


export default {
    components: { VueCropper, PhotoCard, SkeletonLoaderCard, Carousel },
    mounted(){
        this.getLandingPageBackgrounds()
    },
    data: () => ({
        image: null,
        photoData: null,
        photoBlob: null,
        assetPath: `${process.env.VUE_APP_ASSET_PATH}`,
        cropperDialog: false,
        cropper: null,
        loading: false,
        deleteLoading: false,
        cropperLoading: false,
        uploadingSaving: false,
        croppedImage: null,
        previewDialog: false,
        deleteDialog: false,
        selected: null,
        msg: {
            show: false,
            type: '',
            text: ''
        }
    }),

    methods: {
        ...mapActions('admin', ['getLandingPageBackgroundsAction', 'createLandingPageBackgroundAction', 'updateLandingPageBackgroundAction', 'deleteLandingPageBackgroundAction']),
        ...mapMutations(['alertMutation']),

        dragover(e) {
            e.preventDefault();
            e.currentTarget.classList.add("bulk-border-drag");
        },

        dragleave(e) {
            e.preventDefault();
            e.currentTarget.classList.remove("bulk-border-drag");
        },

        dropPhoto(e) {
            e.preventDefault();
            this.$refs.photo.files = e.dataTransfer.files;
            this.acceptPhoto();
            e.currentTarget.classList.remove("bulk-border-drag");
        },

        acceptPhoto() {


            this.photoData = this.$refs.photo.files[0]
            this.$refs.photo.value = ''

            const validTypes = ['image/jpeg', 'image/jpg', 'image/png'];
            if (!validTypes.includes(this.photoData.type)) {
                this.image = null
                this.croppedImage = null
                this.photoBlob = null
                this.photoData = null
                this.alertMutation({
                    show: true,
                    text: 'Only JPG, JPEG, and PNG files are allowed.',
                    type: "error"
                })

                return
            }

            this.cropperDialog=true

            const reader = new FileReader()

            reader.onload = (e) => {
                this.image = e.target.result
            }
            
            reader.readAsDataURL(this.photoData)
            
            setTimeout(() => {
                this.cropper = new Cropper(this.$refs.img, {
                    responsive: true,
                    center: true,
                    background: false,   
                    cropBoxResizable: true,
                    aspectRatio: 4/3
                })
                
            }, 1000);       
        },

        reset() {
            this.cropper.reset()
        },

        upload() {
            let canvas = this.cropper.getCroppedCanvas()
            this.cropper.getCroppedCanvas().toBlob((blob) => {
                this.photoBlob = blob;
            })
            let img = this.cropper.getCroppedCanvas().toDataURL('image/jpeg')
            this.croppedImage = img
            this.cropperDialog = false
            this.cropper.destroy()
        },

        close() {
            this.cropperDialog = false
            this.image = null
            this.croppedImage = null
            this.photoBlob = null
            this.photoData = null
            this.cropper.destroy()
        },

        getLandingPageBackgrounds() {
            this.loading = true
            this.getLandingPageBackgroundsAction().then(() => {
                this.loading = false
            }).catch(() => {
                this.loading = false
            })
        },

        submit() {
            
            this.msg = {
                show: false,
                type: '',
                text: ''
            }

            let form = new FormData()
            form.append('image', this.photoBlob)
            form.append('asset_key', 'background')
            
            this.uploadingSaving = true
            this.createLandingPageBackgroundAction(form).then(() => {

                this.uploadingSaving = false
                this.cropperDialog = false
                this.croppedImage = null
                this.photoBlob = null
                this.image = null
                this.alertMutation({
                    show: true,
                    text: 'Successfully uploaded a new background.',
                    type: "success"
                })
                this.getLandingPageBackgrounds()

            }).catch((e) => {
                
                if(!this.errors.image) {
                    this.msg = {
                        show: true,
                        type: 'error',
                        text: e.response.data.message ? e.response.data.message : 'Something went wrong.'
                    }
                } else {
                    this.msg = {
                        show: true,
                        type: 'error',
                        text: this.errors.image[0]
                    }
                }
                this.uploadingSaving = false
                this.cropperDialog = false
            })
        },

        showPreview() {
            this.previewDialog = true
        },

        deleteLandingPageBackground(){
            this.deleteLoading = true
            this.deleteLandingPageBackgroundAction(this.selected.id).then(() => {

                this.selected = null
                this.deleteLoading = false
                this.deleteDialog = false
                this.alertMutation({
                    show: true,
                    text: 'Successfully deleted a background.',
                    type: "success"
                })
                this.getLandingPageBackgrounds()

            }).catch((e) => {
                this.deleteLoading = false
                this.deleteDialog = false
                this.alertMutation({
                    show: true,
                    text: 'Something went wrong.',
                    type: "error"
                })
            })
        }
    },
    computed: {
        ...mapState({
            errors: (state) => state.errors,
        }),

        ...mapState('admin', {
            backgrounds: (state) => state.landing_page_backgrounds,
        }),

        images(){
            let images = []

            this.backgrounds.forEach(item => {
                images.push(item.image.url)
            });

            return images
        }
    },
}
</script>