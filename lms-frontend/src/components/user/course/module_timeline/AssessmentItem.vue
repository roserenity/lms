<template>
    <v-list-item
        dense 
        link
        :class="['d-flex align-center', data.uuid === current ? 'warning-3' : '']"
        :disabled="module_is_locked || data.assessment_is_locked"
        @click="$emit('click', data)">
        <v-icon small color="primary">
            mdi-note-edit-outline
        </v-icon>
        <v-list-item-title  class="roboto ml-2 f12 fw500" :class="(module_is_locked || data.assessment_is_locked) ? '' : 'secondary-1--text'">
            <div class="f9 secondary-2--text">
                Assessment
            </div>
                {{data.title}}
            <div class="f10 secondary-2--text" v-if="data.assessment_score.score">
                Submitted on: {{$dateFormat.mmDDyy(data.assessment_score.created_at)}}
            </div>
            <div class="f10 secondary-2--text" v-else-if="data.due_date && JSON.parse(data.due_date).end !== ''">
                Due Date: {{data.due_date ? $dateFormat.mmDDyy(JSON.parse(data.due_date).end) : ''}}
            </div>
        </v-list-item-title>
        <v-spacer></v-spacer>
        <v-icon small color="primary" class="ml-auto">
            {{ data.assessment_is_locked ? 'mdi-lock-outline' : 'mdi-lock-open-outline'}}
        </v-icon>
        <v-progress-circular
            indeterminate
            width="3"
            size="25"
            v-if="loading && data.uuid === current "
            color="primary"/>
        <v-badge
            v-else
            bordered
            class="thin-border"
            :color="data.assessment_score.score ? 'success' : ( data.uuid === current ? 'warning' : 'secondary' )"
            size='10'
            inline
            :icon="data.assessment_score.score ? 'mdi-check' : ''"
        >
        </v-badge>
    </v-list-item>
</template>

<script>

export default {
    props: ['data', 'module_is_locked', 'current', 'loading'],
}

</script>