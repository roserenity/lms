<template>
    <section>
        <div
            class="bulk-border pa-3 mb-2"
            @dragover="dragoverfile"
            @dragleave="dragleave"
            @drop="drop"
        >
            <div class="d-flex align-center justify-center">
            <div class="d-flex flex-column  align-center">
                <v-icon color="rgba(63, 93, 208, 0.12)" size="32"
                >mdi-cloud-upload-outline</v-icon
                >
                <div class="secondary-2--text fw500">
                <a
                    href="#"
                    @click="$refs.fileInputRef.click()"
                    class="primary--text text-decoration-none"
                    >Browse</a
                >
                or drag file here 
                </div>
                <div class="secondary-2--text fw500 f12">{{ accepted_types }}</div>
            </div>
            </div>
        </div>

        <alert 
            v-if="msg.value.show"
            :show = "msg.value.show"
            :text = "msg.value.text"
            :type = "msg.value.type"
        />

        <input class="d-none" :multiple="multiple" type="file" ref="fileInputRef" id="fileInputRef" :accept="accept" @change="setAttachment"/>

    </section>
</template>

<script>
import { defineComponent, ref, toRefs, onMounted } from '@vue/composition-api'

export default defineComponent({
    props: {
        accepted_types: {
            type: String,
            default: ''
        },
        accept: {
            type: String,
            default: ''
        },
        accepted_types_list: {
            type: Array,
            default: []
        },
        multiple : {
            type: Boolean,
            default: false
        },
        show : {
            type: Boolean,
            default: false
        },
    },

    watch: {
        show(val) {
            if(val) {
                document.getElementById('fileInputRef').value = '';
            }
        }
    },

    methods:{
        clear() {
            document.getElementById('fileInputRef').value = '';
        },
    },

    setup(props, {emit}) {
        const { accepted_types_list, multiple } = toRefs(props)
        const fileInput = ref([])
        const msg = ref({
            show: false,
            type: '',
            text: ''
        })

        const dragoverfile = (e) => {
            e.preventDefault();
            e.currentTarget.classList.add("bulk-border-drag");
        }

        const dragleave = (e) => {
            e.preventDefault();
            e.currentTarget.classList.remove("bulk-border-drag");
        }

        const drop = (e) => {
            e.preventDefault();
            fileInput.value.push(...e.dataTransfer.files)
            e.currentTarget.classList.remove("bulk-border-drag");
            validate()
        }

        const setAttachment = (e) => {
            console.log(e)
            console.log(document.getElementById('fileInputRef'))
            if(!multiple) {
                document.getElementById('fileInputRef').value = null;
            }
            fileInput.value.push(...e.srcElement.files)
            validate()
        }

        const validate = () => {
            msg.value = {
                show: false,
                type: '',
                text: ''
            }

            fileInput.value.forEach(file => {
                if(accepted_types_list.value.length === 0) {
                    emit('acceptFile', file)
                } else if(accepted_types_list.value.includes(file.type)){
                    emit('acceptFile', file)
                } else {
                    msg.value = {
                        show: true,
                        type: 'error',
                        text: `${file.name} file type not supported`
                    }
                }
            })
            fileInput.value = []
        }

        return {
            msg,
            dragleave,
            dragoverfile,
            drop,
            setAttachment
        }
    }
})

</script>