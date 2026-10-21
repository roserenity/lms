<template>
    <section>
        <v-expansion-panel-header color="secondary-5"class="m-0 p-0 mat_expansion_panel ">
            <template v-slot:default="{ open }">
                <section class="d-flex flex-row align-center">
                    <section class="description_div poppins f14 fw500">
                        COURSE EVALUATIONS
                    </section>
                    <v-spacer></v-spacer>
                    <v-badge
                        bordered
                        size='8'
                        inline
                        class="ml-auto"
                        :color="completed ? 'success' : 'warning'"
                    />
                </section>
            </template>
        </v-expansion-panel-header>
        <v-expansion-panel-content class="expansion-timeline ">
            <v-list class="pa-0">
                <v-list-item @click.stop="" dense class="pa-1 poppins" v-for="evaluations in course_evaluations" :key="evaluations.id">
                    <v-icon small color="primary">
                        mdi-file-document-edit-outline
                    </v-icon>

                    <v-list-item-title class="poppins ml-2 f13 fw500">
                        {{evaluations.title}}
                        <div class="f9 secondary-2--text roboto d-flex flex-row align-center" v-if="evaluations.student_course_evaluation_progress && evaluations.student_course_evaluation_progress.time_spent">
                            TIME SPENT: {{ evaluations.student_course_evaluation_progress.time_spent === 0 ? '-' : $dateFormat.duration(evaluations.student_course_evaluation_progress.time_spent) }} 
                        </div>
                    </v-list-item-title>

                    <v-badge
                        v-if="evaluations.student_is_done"
                        bordered
                        class="thin-border"
                        color="success"
                        size='10'
                        inline
                    >
                    </v-badge>
                </v-list-item>
            </v-list>
        </v-expansion-panel-content>
    </section>
</template>

<script>
import { mapState } from "vuex"

export default {
    props: {
        completed: {
            default: false,
            type: Boolean
        }
    },
    computed: {
        ...mapState('instructor', {
            course_evaluations: (state) => state.course_evaluations,
        })
    }
}
</script>
