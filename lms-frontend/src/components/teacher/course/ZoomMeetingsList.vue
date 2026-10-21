<template>
    <section>
        <v-expansion-panel-header color="secondary-5"class="m-0 p-0 mat_expansion_panel ">
            <template v-slot:default="{ open }">
                <section class="d-flex flex-row align-center">
                    <section class="description_div poppins f14 fw500">
                        LIVE SESSIONS
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
            <v-container class="poppins secondary-2--text f12 fw500 pl-7" v-if="meetings.length === 0">
                No scheduled meetings
            </v-container>
            <v-list v-else class="pa-0">
                <v-list-item dense class="my-2 poppins" v-for="_meeting in meetings" :key="_meeting.id">
                    <v-list-item-title class=" f13" >
                        {{_meeting.data.topic}}
                        <v-list-item-subtitle class="mt-1 f11">
                            SCHEDULE: {{ $dateFormat.mmDDyy(_meeting.data.start_date) }} {{ $dateFormat.timeAndPeriod(_meeting.data.start_date +'T' + _meeting.data.start_time + ':00') }}
                        </v-list-item-subtitle >
                    </v-list-item-title>
                    <v-spacer></v-spacer>
                    <v-btn v-if="_meeting.attendees.length > 0 ? _meeting.attendees[0].zoom_meeting_activity : false" dense icon class="text-capitalize primary--text f12 fw600" @click.stop="() => { selected_zoom_meeting = _meeting.attendees[0], zoom_activity_dialog = true }">
                        <v-icon>mdi-text-box-edit-outline</v-icon>
                    </v-btn>
                    <v-badge
                        v-if="_meeting.attendees.length > 0 ? _meeting.attendees[0].present :false"
                        bordered
                        class="thin-border"
                        color="success"
                        size='8'
                        inline
                    />
                </v-list-item>
            </v-list>
        </v-expansion-panel-content>

        <ZoomActivityDialog
            :dialog="zoom_activity_dialog"
            :selected_zoom_meeting="selected_zoom_meeting"
            @close="() => { selected_zoom_meeting = null, zoom_activity_dialog = false}"
        />
    </section>
</template>

<script>
import { mapState } from "vuex"
import ZoomActivityDialog from "@/components/user/course/ZoomActivityDialog.vue";

export default {
    components: {
        ZoomActivityDialog
    },
    props: {
        completed: {
            default: false,
            type: Boolean
        }
    },
    computed: {
        ...mapState('instructor', {
            meetings: (state) => state.meetings
        })
    },
    data: () =>  ({
        selected_zoom_meeting: null,
        zoom_activity_dialog: false
    })
}
</script>
