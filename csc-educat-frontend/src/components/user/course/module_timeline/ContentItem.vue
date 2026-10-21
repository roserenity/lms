<template>
    <v-list-item dense 
        class="d-flex align-center"
        :class="[data.topic_has_assessments ? '' : 'pl-8', data.uuid === current ? 'warning-3' : '']"
        :disabled="module_is_locked"
        @click="$emit('click', data)"
    >
        <v-icon small color="primary" v-if="data.learning_material != null">
        {{ 
            data.learning_material.file_type == 'youtube'
            ? 'mdi-youtube'
            : data.learning_material.file_type == 'pdf'
            ? 'mdi-file-pdf-box'
            : data.learning_material.file_type == 'mp4' 
            ? 'mdi-file-video-outline'
            : data.learning_material.file_type == 'scorm_file'
            ? 'mdi-archive-outline'
            : 'mdi-folder-question-outline'}}
            
        </v-icon>
        <v-icon small color="primary" v-else>mdi-eye-off-outline</v-icon>

        <v-list-item-title class="roboto ml-2 f13 fw500" :class="module_is_locked ? '' : 'secondary-1--text'">
            {{data.title}}
        </v-list-item-title>

        <v-progress-circular
            indeterminate
            width="3"
            size="25"
            v-if="loading && data.uuid === current "
            color="primary"/>

        <v-badge
            v-else
            bordered
            :color="data.student_done_status ? 'success' : ( data.uuid === current ? 'warning' : 'secondary' )"
            size='10'
            inline
            :icon="data.student_done_status ? 'mdi-check' : ''"
        >
        </v-badge>
    </v-list-item>
</template>

<script>

export default {
    props: ['data', 'module_is_locked', 'current', 'loading']
}

</script>