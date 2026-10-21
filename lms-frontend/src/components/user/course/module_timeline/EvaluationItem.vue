<template>
    <v-list-item dense 
        class="d-flex pt-2"
        :class="[data.validity !== 'anytime' ? 'align-start' : 'align-center', data.uuid === current ? 'warning-3' : '']"
        :disabled="(type === 'module' ? data.module_evaluation_is_locked : data.course_evaluation_is_locked)"
        @click="$emit('click', data)"
    >
        <v-icon small color="primary">
            mdi-file-document-edit-outline
        </v-icon>
        <div class="d-flex flex-column align-left px-2">
            <section class="roboto f13 fw500" :class="(type === 'module' ? data.module_evaluation_is_locked : data.course_evaluation_is_locked) ? '' : 'secondary-1--text'" >
                <div class="f9">
                    Evaluation
                </div>
                {{ data.title }}
            </section>
            <div class="description_div f10 secondary-2--text mt-1 mb-3 " v-if="data.validity === 'date'">
                This evaluation will be accessible
                <template v-if="dateFormattedAvailableFrom"> from {{ dateFormattedAvailableFrom }}</template>
                <template v-if="dateFormattedAvailableTo"> until {{ dateFormattedAvailableTo }}</template>
            </div>
        </div>
        
        <v-spacer></v-spacer>

        <v-icon small color="primary">
            {{ type === 'module' ? (data.module_evaluation_is_locked ? 'mdi-lock-outline' : 'mdi-lock-open-outline') : (data.course_evaluation_is_locked ? 'mdi-lock-outline' : 'mdi-lock-open-outline')}}
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
            :color="type === 'module' ? (data.student_module_evaluations_count > 0 ? 'success' : ( data.uuid === current ? 'warning' : '' )) : (data.student_course_evaluations_count > 0 ? 'success' : ( data.uuid === current ? 'warning' : 'secondary' ))"
            size='10'
            inline
            :icon="type === 'module' ? (data.student_module_evaluations_count > 0 ? 'mdi-check' : '') : (data.student_course_evaluations_count > 0 ? 'mdi-check' : '')"
        >
        </v-badge>
        </v-list-item>
</template>

<script>
import moment from 'moment'

export default {
    props: ['data', 'module_is_locked', 'type', 'current', 'loading'],
    
    computed: {
        
        dateFormattedAvailableFrom() {
            return this.data.available_from ? moment(this.data.available_from).format('LL') : ''
        },

        dateFormattedAvailableTo() {
            return this.data.available_to ? moment(this.data.available_to).format('LL') : ''
        },
    }
}

</script>