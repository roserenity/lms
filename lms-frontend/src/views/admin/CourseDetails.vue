<template>
    <v-container fluid>
        <section class="my-3">
            <v-row dense no-gutters>
                <v-col cols="12" md="8">
                    <section class="d-flex flex-column" style="min-width: 0">
                        <v-card-title class="d-flex flex-column align-start primary-font fw600 break-word mb-4">
                            {{course.title}}
                        </v-card-title>
                        <v-card-subtitle v-if="course.is_free" class="f12 fw500">
                            <span class="secondary-2--text" >COST : </span>
                            FREE!
                        </v-card-subtitle>
                        <v-card-subtitle v-else-if="!course.is_free" class="f12 fw500">
                            <span class="secondary-2--text" >COST : </span>
                            PHP {{ course.price ? course.price : 0 }}
                        </v-card-subtitle>
                        <v-card-subtitle class="f12 fw500 pt-0">
                            <span class="secondary-2--text" >TRAINING HOURS : </span>
                            {{ course.training_hours }} {{ course.training_hours > 1 ? 'hours' : 'hour' }}
                        </v-card-subtitle>
                        <v-card-subtitle v-if="course.available_until" class="fw500 pt-0 f12">
                            <span class="secondary-2--text" >AVAILABILITY: </span>
                            <span v-if="course.available_until.start_date"> from {{ $dateFormat.mmDDyy(course.available_until.start_date) }} </span>
                            <span v-if="course.available_until.end_date"> until {{ $dateFormat.mmDDyy(course.available_until.end_date) }} </span>
                        </v-card-subtitle>
                        <v-card-subtitle class="fw500 pt-0 f12">
                            <span class="secondary-2--text" >FACILITATOR: </span>
                            <span class="f12 text-capitalize" v-if="!course.facilitator">
                                {{`${course.uploader.first_name ? course.uploader.first_name : 'Instructor'} ${course.uploader.last_name ? course.uploader.last_name : 'Account'} ${course.uploader.suffix ? course.uploader.suffix : ''}`}}
                            </span>
                            <span class="f12 text-capitalize" v-else>
                                {{ course.facilitator }}
                            </span>
                        </v-card-subtitle>
                    </section>
                </v-col>
            </v-row>
            <v-card-subtitle v-html="course.description ? $dompurifier(course.description) : ''" class="my-3"></v-card-subtitle>
        </section>
    </v-container>
</template>

<script>
import {mapActions, mapMutations, mapState} from 'vuex'


export default {
    name: 'course-details',
    props: ['course'],
    data:() => ({
    }),
}
</script>