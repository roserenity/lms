<template>
    <div>
        <div width="100px" height="100px" class="bulk-border mt-2 py-8 col-12 d-flex flex-column align-center justify-center align-self-center"
            @dragover="dragover"
            @dragleave="dragleave"
            @drop="drop">
            <img
                v-if="image && !crop"
                alt="Avatar"
                :src="src"
                height="100"
            />
            <img
                v-if="croppedImage"
                alt="Avatar"
                :src="croppedImage"
                height="100"
            />
            <v-icon v-else x-large color="primary">
                mdi-cloud-upload-outline
            </v-icon>
            <div class="d-flex flex-column align-center secondary--text f12 mt-3">
                <div>
                    <a
                    href="#"
                    @click="$refs.file.click()"
                    class="primary--text text-decoration-none"
                    >
                    Browse
                    </a>
                    or drag file here
                </div>
                (.png, .jpg, .jpeg)
            </div>
        </div>
        <alert 
            v-if="msg.show"
            :show = "msg.show"
            :text = "msg.text"
            :type = "msg.type"
        />
        <input type="file" ref="file" class="d-none" @change="accept"  accept=".jpg,.png,.jpeg"/>
        <v-dialog max-width="550" v-model="dialog" persistent>
            <v-sheet class="pa-4" rounded="lg">
                <div class="d-flex align-center mb-5">
                <h3 class="poppins">
                    Crop
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
    </div>
</template>

<script>
import { ref } from 'vue';
import VueCropper from 'vue-cropperjs';
import Cropper from 'cropperjs';
import 'cropperjs/dist/cropper.css'


export default {
    props: {
        crop: {
            default: false,
            type: Boolean
        }, 
        error: {
            default: null,
            type: String
        }
    },
    data: () => ({
        items: ['User', 'Instructor'],
        name: '',
        image: '',
        file: null,
        croppedImage: '',
        src: '',
        blobImg: null, 
        loading: false,
        dialog: false,
        msg: {
            show: false,
            text: '',
            type: ''
        },
    }),
    methods: {
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
            e.currentTarget.classList.remove("bulk-border-drag");
            this.$refs.file.files = e.dataTransfer.files;
            this.accept()
        },
    
        accept() {
            this.msg = {
                show: false,
                type: '',
                text: ''
            }
            if(!['image/png', 'image/jpeg'].includes(this.$refs.file.files[0].type)){
                this.msg = {
                    show: true,
                    type: 'error',
                    text: `File not supported`
                }
                this.$refs.file.value = ''
                this.image = null
            } else {
                if(!this.crop) {
                    this.image = this.$refs.file.files[0]
                    this.src = URL.createObjectURL(this.image)
                    this.$refs.file.value = ''
                    this.$emit('acceptImage', this.image)
                    this.$emit('acceptImageSrc', this.src)
                } else {
                    this.dialog = true
                    this.file = this.$refs.file.files[0]

                    const reader = new FileReader()

                    reader.onload = (e) => {
                        this.image = e.target.result
                    }
                    
                    reader.readAsDataURL(this.file)
                    
                    setTimeout(() => {
                        this.cropper = new Cropper(this.$refs.img, {
                            responsive: true,
                            center: true,
                            background: false,   
                            cropBoxResizable: true,
                            aspectRatio: 12/7
                        })
                        
                    }, 1000);
                }
            }
        },

        upload() {

            this.cropper.getCroppedCanvas().toBlob((blob) => {
                this.blob_img = blob;

                this.src = URL.createObjectURL(blob);

                this.$emit('acceptImage', this.croppedImage)
                this.$emit('acceptImageSrc', this.src);
                this.$emit('acceptBlob', this.blob_img);

                this.dialog = false;
                this.close();
                this.cropper.destroy();

                setTimeout(() => URL.revokeObjectURL(this.src), 3000); 
            });

            let img = this.cropper.getCroppedCanvas().toDataURL('image/jpeg');
            this.croppedImage = img;

        },


        clear() {
            document.getElementById('file').value = '';
        },

        close() {
            this.dialog=false
            this.image = null
            this.file = null
            this.cropper.destroy()
        },

        reset() {
            this.cropper.reset()
        },
    },
    watch: {
        error(val) {
            if(val) {
                this.msg = {
                    show: true,
                    type: 'error',
                    text: val
                }
            } else {
                this.msg = {
                    show: false,
                    type: 'error',
                    text: ''
                }
            }
        }
    }
}
</script>