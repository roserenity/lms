<template>
    <section>
        <editor
            class="tiny"
            :value="value"
            v-on:input="($event) => handleSave($event)"
            :api-key="mce_key"
            :disabled="disabled"
            :rules="rules"
            :init="{
                height: 400,
                menubar: false,
                plugins: [
                    'advlist autolink lists link image charmap print preview anchor table',
                    'searchreplace visualblocks code fullscreen',
                    'insertdatetime media table paste code help wordcount',
                    'save',
                ],
                toolbar:
                    'formatselect bold italic backcolor \
                    alignleft aligncenter alignright alignjustify \
                    table tabledelete bullist numlist outdent indent removeformat',
                toolbar_mode: 'wrap',
                content_style:
                    'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }',
                invalid_elements: 'iframe,a,script',
                save_enablewhendirty : false,
                init_instance_callback: () => {
                    editorInitialized()
                }
            }"
        />
        <section v-if="showSaveButton" class="d-flex flex-row justify-end">
            <ButtonPrimary v-if="initialized" :label="'SAVE'" @click="$emit('setValue', val)" class="ml-auto poppins mt-1"/>
        </section>
    </section>
</template>

<script>
import Editor from "@tinymce/tinymce-vue";

export default {
    props: {
        disabled: {
            type: Boolean,
            default: false
        },
        value: {
            type: String,
            default: ''
        },
        rules: {
            type: Array,
            default: () => []
        },
        showSaveButton: {
            type: Boolean,
            default: false
        }
    },
    components: {
        editor: Editor,
    },
    data: () => ({
        mce_key: `${process.env.VUE_APP_TINYMCE_KEY}`,
        val: '',
        initialized: false
    }),
    mounted(){
        this.val = this.value
    },
    methods: {
        handleSave(e) {
            this.val = e
            if(!this.showSaveButton) {
                this.$emit('setValue', this.val)
            }
        },
        editorInitialized() {
            this.initialized = true
        }
    }
}
</script>