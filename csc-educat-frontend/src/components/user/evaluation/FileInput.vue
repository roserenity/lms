<template>
    <section>
        <section>
            <v-chip 
                v-if="answer.file_attachment"
                class="ma-1 mb-1"
                color="primary"
                small
                outlined
                close
                @click:close="$emit('removeAttachment', index)"
                >
                <v-icon small left>
                    mdi-image
                </v-icon>
                {{ answer.file_attachment.name }}
            </v-chip>

        </section>
        <div class="bulk-border pa-5" @dragover="dragover" @dragleave="dragleave" @drop="drop">
            <div class="text-center">
                <v-icon color="rgba(63, 93, 208, 0.12)" size="68">mdi-cloud-upload-outline</v-icon>
                <div class="secondary--text"><a href="#" @click="$refs.file.click()" class="primary--text text-decoration-none">Browse</a> or Drag file to upload</div>
            </div>
        </div>
        <input class="d-none" type="file" ref="file" accept=".jpeg,.png,.jpg,.doc,.docx,.pdf,.ppt,.pptx" @change="addAttachment"/>
    </section>
</template>

<script>
export default {
    props: ['index', 'answer'],
    methods: {
        addAttachment() {
            this.$emit('saveAnswer', {index: this.index, answer: this.$refs.file.files[0]})
        },
        removeAttachment() {
            this.$emit('saveAnswer', {index: this.index, answer: this.$refs.file.files[0]})
        },
        dragover(e) {
            e.preventDefault()
            e.currentTarget.classList.add('bulk-border-drag');
        },
        dragleave(e) {
            e.preventDefault()
            e.currentTarget.classList.remove('bulk-border-drag');
        },
        drop(e) {
            e.preventDefault();
            this.$refs.file.files = e.dataTransfer.files;
            this.addAttachment()
            e.currentTarget.classList.remove('bulk-border-drag');
        },
    }
}
</script>
