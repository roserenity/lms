<template>
    <v-dialog v-model="dialog" persistent width="370">
        <v-card width="370" class="overflow-x-hidden">
            <section class="d-flex justify-end">
                <v-btn icon @click="$emit('close')">
                    <v-icon>
                        mdi-close
                    </v-icon>
                </v-btn>
            </section>
            <v-container fluid class="text-center primary-font fw600 f16" >
                {{ title }}
                <v-container
                    class="bulk-border mt-2 d-flex flex-column justify-center"
                    @dragover="handleDragOver"
                    @dragleave="dragleave"
                    @drop="drop"
                >
                    <div class="d-flex flex-column py-3">
                        <v-icon color="rgba(63, 93, 208, 0.12)" size="68"
                            >mdi-cloud-upload-outline</v-icon
                        >
                        <div class="secondary--text f12 poppins">
                            <a
                            href="#"
                            @click="$refs.file_input.click()"
                            class="primary--text text-decoration-none"
                            >
                            Browse
                            </a>
                            or drag file here
                        </div>
                        <div class="secondary--text f10 fw400">accepted file types: {{ input_accept }}</div>
                    </div>
                </v-container>
                <alert 
                    v-if="msg.value.show"
                    :show = "msg.value.show"
                    :text = "msg.value.text"
                    :type = "msg.value.type"
                />
                <v-container
                    v-if="file.value"
                    class="d-flex justify-space-between align-center my-3">
                    <div >
                        <div class="roboto fw600 f12" style="margin-bottom: -8px">
                            {{ file.value.name }}
                        </div>
                        <span class="roboto fw500 f10 secondary--text">
                            {{ (file.value.size / 1000).toFixed(2) }} KB
                        </span>
                    </div>
                    <v-spacer />
                    <v-progress-linear
                        v-if="loading2.value"
                        indeterminate
                        color="success"
                        height="7"
                        style="width: 120px"
                        rounded
                    />
                    <v-btn icon small @click="removeFile">
                        <v-icon size="18" color="danger-1">mdi-close</v-icon>
                    </v-btn>
                </v-container>
            </v-container>
            <section class="d-flex flex-row justify-space-evenly pa-3">
                <v-btn outlined dense color="error" @click="$emit('close')">
                    Cancel
                </v-btn>
                <v-spacer />
                <v-btn outlined dense color="success" :disabled="loading2.value && !file.value" @click="$emit('submit', file.value)" :loading="loading">
                    Submit
                </v-btn>
            </section>
            <input
                type="file"
                ref="file_input"
                class="d-none"
                id="file_input"
                :accept="input_accept"
                @change="acceptFile"
            />
        </v-card>
    </v-dialog>
</template>

<script>
import { ref } from '@vue/composition-api'
import { watch, toRef } from 'vue';

export default {
    props: {
        dialog: Boolean,
        title: String,
        input_accept: String,
        file_types: Array,
        loading: Boolean
    },
    setup(props) {
        const loading2 = ref(false)
        let file = ref(null)
        const file_input = ref(null)
        const dialogRef = toRef(props, 'dialog');
        const msg = ref({
            show: false,
            type: '',
            text: ''
        })

        function handleDragOver(e) {
            e.preventDefault();
            e.currentTarget.classList.add("bulk-border-drag");
        }

        function dragleave(e) {
            e.preventDefault();
            e.currentTarget.classList.remove("bulk-border-drag");
        }

        function drop(e) {
            e.preventDefault();
            file_input.value = e.dataTransfer.files;
            acceptFile()
            e.currentTarget.classList.remove("bulk-border-drag");
        }

        function acceptFile(e) {
            msg.value = {
                show: false,
                type: '',
                text: ''
            }

            if(e) {
                file.value = e.target.files[0]
                file_input.value = ''
                document.getElementById('file_input').value = '';
            } else {
                file.value = file_input.value[0];
            }

            if(props.file_types.includes(file.value.type)){
                loading2.value = true;
                setTimeout(() => {
                    loading2.value = false;
                }, 3000);
            } else {
                file.value = null
                msg.value = {
                    show: true,
                    type: 'error',
                    text: 'File type not supported'
                }
            }
            
            file_input.value = null            
        }

        function removeFile() {
            file.value = null
        }

        watch(dialogRef, () => {
            file.value = null
            file_input.value = null
        })

        // return {
        //     file,
        //     file_input,
        //     msg,
        //     handleDragOver,
        //     dragleave,
        //     drop,
        //     acceptFile,
        //     removeFile,
        //     loading2
        // }
    }
}
</script>