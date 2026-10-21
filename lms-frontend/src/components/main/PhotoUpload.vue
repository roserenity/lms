<template>
    <v-dialog v-model="modal" persistent max-width="400">
        <v-card class="pa-3">
            <v-row no-gutters class="mb-2">
                <v-col>
                    <span class="poppins f16 fw600 text-center primary--text">{{ type === 'course' ? 'Upload course thumbnail' : `Upload ${type} photo`}}</span>
                </v-col>
                <v-col cols="1">
                    <v-btn icon small @click="$emit('close')">
                        <v-icon size="18">mdi-close</v-icon>
                    </v-btn>
                </v-col>
            </v-row>
            <div class="d-flex align-center justify-center">
                <div
                    class="bulk-border mt-2 py-8 col-12 d-flex flex-column align-center justify-center align-self-center"
                    @dragover="dragover"
                    @dragleave="dragleave"
                    @drop="drop"
                >
                    <v-avatar v-if="image && type !== 'course'" color="secondary-5" :size="90" class="pointer-cursor" id="avatar-profile" style="border: 5px solid">
                        <img
                            alt="Avatar"
                            :src="image"
                        >
                    </v-avatar>
                    <v-img
                        v-else-if="image && type === 'course'"
                        alt="Course thumbnail"
                        max-width="220"
                        height="112"
                        contain
                        :src="image"
                    />
                    <v-icon v-else x-large color="primary">
                        mdi-cloud-upload-outline
                    </v-icon>
                    <div class="d-flex flex-column align-center f12 secondary--text ">
                        <div class="f12 ml-2">
                            <a
                            href="#"
                            @click="$refs.file.click()"
                            class="primary--text text-decoration-none"
                            >
                            Browse
                            </a>
                            or drag file here
                        </div>
                        ( .png, .jpeg, .jpg )
                    </div>
                </div>
            </div>
            <alert 
                v-if="msg.show"
                :show = "msg.show"
                :text = "msg.text"
                :type = "msg.type"
            />
            <v-divider class="my-3"/>
            <div class="d-flex justify-end">
                <v-btn text class="poppins fw600 text-capitalize secondary-3--text" @click="() => {
                    this.image = null
                    this.$refs.file.value = ''
                    this.file = null
                    $emit('close')
                }"> 
                    Cancel
                </v-btn>
                <v-btn color="primary" class="poppins" @click="updatePhoto" :disabled="type === 'group' ? !file : !blob_img" :loading="loading">
                    Save
                </v-btn>
            </div>

            <input
                type="file"
                ref="file"
                class="d-none"
                accept=".jpeg,.png,.jpg"
                v-on:change="accept"
            />
        </v-card>
        <v-dialog max-width="550" v-model="dialog" persistent>
            <v-sheet class="pa-4" rounded="lg">
                <div class="d-flex align-center mb-5">
                <h3 class="poppins">
                    {{ type === 'course' ? 'Crop thumbnail' : `Crop Profile`}}
                    
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
                    alt="Source Image"
                    style="display: block;max-height:50vh;max-width:500px"
                />
                <div class="d-flex align-center justify-end mt-3">
                    <v-btn text @click="reset">Reset</v-btn>
                    <v-btn text @click="upload" :loading="loading">Crop</v-btn>
                </div>
                </v-sheet>
            </v-sheet>
        </v-dialog>
    </v-dialog>
</template>

<script>
import { mapActions, mapMutations } from 'vuex';
import VueCropper from 'vue-cropperjs';
import Cropper from 'cropperjs';
import 'cropperjs/dist/cropper.css'

export default {
    props: ['modal', 'type', 'id', 'role'],
    data: () => ({
        image: null,
        blob_img: null,
        dialog: false,
        cropper:  null,
        loading: false,
        file: null,
        error: false,
        msg: {
            show: false,
            text: '',
            type: ''
        }
    }),
    components: {
        VueCropper
    },
    methods:  {
        ...mapMutations(['alertMutation']),
        ...mapActions("instructor", ["uploadThumbnailCourseAction"]),


        dragover(e) {
            e.preventDefault();
            e.currentTarget.classList.add("bulk-border-drag");
        },
        dragleave(e) {
            e.preventDefault();
            e.currentTarget.classList.remove("bulk-border-drag");
        },

        drop(e) {
            e.preventDefault();
            this.msg = {
                show: false,
                type: '',
                text: ''
            }
            if(!['image/png', 'image/jpeg', 'image/jpg'].includes(e.dataTransfer.files[0].type)) {
                this.msg = {
                    show: true,
                    type: 'error',
                    text: `File not supported`
                }
                this.$refs.file.value = ''
                this.file = null
                this.image = null
                this.blob_img = null
            } else {
                this.$refs.file.files = e.dataTransfer.files;
                this.accept();
            }
            e.currentTarget.classList.remove("bulk-border-drag");
        },

        accept() {
            this.msg = {
                show: false,
                type: '',
                text: ''
            }
            if(!['image/png', 'image/jpeg', 'image/jpg'].includes(this.$refs.file.files[0].type)) {
                this.msg = {
                    show: true,
                    type: 'error',
                    text: `File not supported`
                }
                this.$refs.file.value = ''
                this.file = null
                this.image = null
                this.blob_img = null
            } else {
                this.file = this.$refs.file.files[0]
                this.$refs.file.value = ''

                if(this.type === 'group') {
                    this.image = URL.createObjectURL(this.file)
                } else {
                    this.dialog=true
                    const reader = new FileReader()

                    reader.onload = (e) => {
                        this.image = e.target.result
                    }
                    
                    reader.readAsDataURL(this.file)
                    
                    setTimeout(() => {
                        if(this.type !== 'course') {
                            this.cropper = new Cropper(this.$refs.img, {
                                aspectRatio: 1,
                                responsive: true,
                                center: true,
                                background: false,          
                            })
                        } else {
                            this.cropper = new Cropper(this.$refs.img, {
                                responsive: true,
                                center: true,
                                background: false,   
                                cropBoxResizable: true,
                                aspectRatio: 125/66
                            })
                        }
                        
                    }, 1000);
                }
            }
        },

        reset() {
            this.cropper.reset()
        },

        upload() {
            let canvas = this.cropper.getCroppedCanvas()
            this.cropper.getCroppedCanvas().toBlob((blob) => {
                this.blob_img = blob;
            })
            let img = this.cropper.getCroppedCanvas().toDataURL('image/jpeg')
            this.image = img
            this.dialog = false
            this.cropper.destroy()
        },

        updatePhoto() {
            this.loading = true

            // save photo
            if(this.type === 'profile') {
                let form = new FormData()
                if (this.blob_img != null) {
                        form.append('image', this.blob_img)
                        this.$api.post('/update/profile/photo', form)
                    .then(res => {
                        this.$store.dispatch('authAction')
                        this.loading = false
                        this.$emit('close')
                    }).catch(e => {
                        this.loading = false
                        console.log(e)
                    })
                }
            }

            if(this.type === 'group') {
                let form = new FormData()
                if (this.file != null) {
                    form.append('id', this.id)
                    form.append('image_file', this.file)
                    this.$store.dispatch(`${this.role}/updateGroupPhotoAction`, {id: this.id, form: form})
                    .then(() => {
                        this.alertMutation({
                            show: true,
                            text: `Group Photo Successfully updated`,
                            type: "success"
                        });
                        this.loading = false
                        this.$emit('close')
                    }).catch(e => {
                        this.loading = false
                    })
                }
            }

            if(this.type === 'course') {
                let form = new FormData()
                if (this.blob_img != null) {
                    form.append("image_file", this.blob_img);
                    form.append("_method", 'PUT');
                    this.uploadThumbnailCourseAction({
                        id: this.id,
                        form: form,
                    }).then(()=>{
                        this.loading = false
                        this.alertMutation({
                            show: true,
                            text: 'Course successfully updated',
                            type: "success"
                        })
                        this.$emit('close')
                    }).catch(e => {
                        this.loading = false
                    })
                }
            }

            
            this.loading = true
        },

        close() {
            this.dialog=false
            this.image = null
            this.cropper.destroy()
        },
    },

    watch: {
        modal(val){
            if(!val) {
                this.image = null
                this.$refs.file.value = ''
                this.file = null
                this.msg = {
                    show: false,
                    type: '',
                    text: ''
                }
            }
        }

    }
}
</script>