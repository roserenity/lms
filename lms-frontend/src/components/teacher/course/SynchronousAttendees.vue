<template>
    <v-sheet class="mb-5 transparent" height="auto" id="synchronous">
        <v-card class="pa-5 custom-border border">
            <section class="d-flex flex-wrap align-end">
                <SearchBar 
                    :outlined="true" 
                    :placeholder="'Search student'" 
                    :disabled="!selected_meeting" 
                    :value.sync="search" 
                    @search="getEnrollees()" 
                    @clear="() => { search = '', page = 1, getEnrollees() }"
                    class="mb-5 mr-1"/>
                <section class="mb-5 col-lg-4 pa-0" >
                    <FormLabel :label="`ZOOM MEETING`"/>
                    <v-select 
                        :items="meetings" 
                        outlined dense 
                        item-value="id"
                        item-text="data"
                        v-model="selected_meeting"
                        hide-details
                        min-width="auto"
                        class="general-custom-field f12 poppins "
                        :loading="loading">
                        <template v-slot:selection="data" >
                            <v-list-item-title class="roboto f12 fw500" >
                                <span> {{ data.item.data.topic }}</span>
                            </v-list-item-title>
                        </template>

                        <template v-slot:item="data">
                                <v-list-item-title class="roboto f12 fw500" >
                                    <span> {{ data.item.data.topic }}</span>
                                </v-list-item-title>
                        </template>
                    </v-select>
                </section>
                <v-spacer />
                <v-menu offset-y transition="slide-y-transition" bottom duration :close-on-content-click="false" class="">
                    <template v-slot:activator="{ on, attrs }">
                        <v-btn 
                            outlined
                            class="text-capitalize poppins fw500 f14 ml-auto mb-5 mr-1"
                            v-bind="attrs"
                            v-on="on"
                            color="primary"
                            dense
                        >
                            Filter
                            <v-icon color="primary" small right>
                                mdi-tune
                            </v-icon>
                        </v-btn>
                    </template>
                    <v-card width="300">
                        <v-card-text>
                            <FormLabel :label="'STATUS'"/>
                            <v-chip-group column v-model="filter.status" class="d-flex flex-wrap">
                                <v-chip filter outlined label color="secondary-3" value="" class="caption">ALL</v-chip>
                                <v-chip filter outlined label color="secondary-3" value="present" class="caption">Present</v-chip>
                                <v-chip filter outlined label color="secondary-3" value="absent" class="caption">Absent</v-chip>
                                <v-chip filter outlined label color="secondary-3" value="pending" class="caption">Pending</v-chip>
                            </v-chip-group>
                        </v-card-text> 
                        <v-divider/>  
                        <v-card-text>
                        <v-btn small outlined color="primary" block dense class="mt-2" @click="resetFilters">
                            CLEAR FILTERS
                        </v-btn>    
                        <v-btn small color="primary" block dense class="mt-2" @click="getEnrollees" :disabled="!selected_meeting">
                            APPLY
                        </v-btn>
                        </v-card-text>
                    </v-card>
                    </v-menu>
                <ButtonExport 
                    @click="exportList"
                    :loading="loading4"
                    :disabled="!selected_meeting || meeting_participants.length === 0"
                    class=" mb-5"/>
            </section>
            <section class="d-flex flex-wrap">
                <v-spacer />
                <ButtonPrimary
                    :outlined="true"
                    @click="markAsPresent"
                    :loading="loading1"
                    :label="'Mark as Present'"
                    class=" mb-5 mr-1"
                />
                <ButtonPrimary
                    :outlined="true"
                    @click="markAsAbsent"
                    :loading="loading2"
                    :label="'Mark as Absent'"
                    class=" mb-5 mr-1"
                />
            </section>
            <v-data-table
                    :headers="course_zoom_meeting_participants_tbl"
                    :items="meeting_participants"
                    :loading="loading3"
                    class="poppins"
                    :page.sync="page"
                    :items-per-page.sync="itemsPerPage"
                    hide-default-footer
                    dense
                >
                <template v-slot:header.action="{ item }" >
                    <v-checkbox dense class="mt-0" v-model="selectAll" hide-details/>
                </template>
                <template v-slot:item.action="{ item }" >
                    <v-checkbox dense v-model="selected" :value="item.user.id" hide-details class="mt-0"/>
                </template>
                <template v-slot:item.name="{ item }" >
                    <div class="mb-2 mt-3" >
                        {{ item.user.first_name ? item.user.first_name : 'Learner' }}
                        {{ $userFormat.middle_initial(item.user.middle_name) }}
                        {{ item.user.last_name ? item.user.last_name : 'Account' }}
                        {{ item.user.suffix ? item.user.suffix : '' }}
                    </div>
                </template>
                <template v-slot:item.present="{ item }" >
                    <v-chip v-if="item.present === 1" dense color="#7BC14533" class="success--text ma-auto fw500">PRESENT</v-chip>
                    <v-chip v-else-if="item.present === 0" dense color="#BDBDBD33" class="secondary-2--text ma-auto fw500">ABSENT</v-chip>
                    <v-chip v-else text dense color="transparent" class="secondary-2--text ma-auto fw500">PENDING</v-chip>
                </template>
                <template v-slot:item.actions="{ item }" >
                    <v-btn dense icon class="text-capitalize primary--text f12 fw600" @click="() => { selected_zoom_meeting_attendee = item, zoom_activity_dialog = true}">
                        <v-icon>mdi-text-box-edit-outline</v-icon>
                    </v-btn>
                </template>
            </v-data-table>
            <FormPagination 
                :pageCount="pageCount" 
                :page="page"
                :paginate="paginate"
                @page="(e) => { page = e, getEnrollees() }" 
                @paginate="(e) => { paginate = e, page = 1, getEnrollees() }"/>

            <ZoomActivityDialog
                :dialog="zoom_activity_dialog"
                :selected_zoom_meeting_attendee="selected_zoom_meeting_attendee"
                :selected_zoom_meeting_id="selected_meeting"
                @close="() => { zoom_activity_dialog = false, selected_zoom_meeting_attendee = null}"
            />
        </v-card>
    </v-sheet>
</template>

<script>
import { mapActions, mapState, mapMutations } from 'vuex';
import { course_zoom_meeting_participants_tbl } from '@/constants/tblheaders/course';
import ZoomActivityDialog from './ZoomActivityDialog.vue';

export default {
    props: ["item"],
    components: {
        ZoomActivityDialog
    },
    data: () => ({
        search: '',
        loading: false,
        loading1: false,
        loading2: false,
        loading3: false,
        loading4: false,
        pageCount: 1,
        page: 1,
        paginate: '10',
        course_zoom_meeting_participants_tbl,
        selected: [],
        selectAll: false,
        selected_meeting: null,
        filter: {
            status: ''
        },
        selected_zoom_meeting_attendee: null,
        zoom_activity_dialog: false
    }),
    computed: {
        ...mapState('instructor',{
            meetings: (state) => state.meetings,
            meeting_participants: (state) => state.meeting_participants
        }),

        itemsPerPage(){
            return Number(this.paginate)
        }
        
    },
    watch: {

        selected_meeting(val) {
            if(val) this.getEnrollees()
        },

        selectAll(val) {
            if(val) {
            let _selected = []

            this.meeting_participants.forEach(item => {
                if(!this.selected.includes(item)) {
                    _selected.push(item.user.id)
                }
            })

            this.selected = [..._selected]

            } else if (!val && this.meeting_participants.length === this.selected.length) {
                this.selected = [] 
            }
        },

        selected(val){
            if(val.length !== this.meeting_participants.length) {
                this.selectAll = false
            } else if (val.length === this.meeting_participants.length && this.meeting_participants.length !== 0) {
                this.selectAll = true
            }
        }
    },
    mounted(){
        this.loading = true
        this.getMeetingsAction({course_id: this.$route.params.id}).then(()=> this.loading = false).catch(() => {
            this.loading = false
        })
    },
    methods: {
        ...mapMutations(['alertMutation']),
        ...mapActions('instructor', [
            'getMeetingParticipantsAction',
            'updateMeetingParticipantsAttendanceStatusAction',
            'exportMeetingParticipantsAction',
            'getMeetingsAction']),

        getEnrollees(){
            if(this.selected_meeting) {
                this.loading3 = true

                this.getMeetingParticipantsAction({
                    course_id: this.$route.params.id,
                    zoom_meeting_id: this.selected_meeting,
                    page: this.page, 
                    paginate: Number(this.paginate),
                    search: this.search,
                    status: this.filter.status
                }).then(res => {
                    this.page = res.current_page
                    this.paginate = String(res.per_page)
                    this.pageCount = res.last_page
                }).finally(() => {
                    this.loading3 = false
                })

            }
        },

        markAsPresent(){
            this.loading1 = true
            this.updateMeetingParticipantsAttendanceStatusAction({
                course_id: this.$route.params.id,
                zoom_meeting_id: this.selected_meeting,
                user_ids: this.selected,
                status: 1
            }).then(() => {
                this.selected = []
                this.getEnrollees()
                this.alertMutation({
                    show: true,
                    text: 'Attendance status updated',
                    type: "success"
                })
            }).catch(() => {
                this.alertMutation({
                    show: true,
                    text: 'Something went wrong',
                    type: "error"
                })
            }).finally(() => {
                this.loading1 = false
            })
        },

        markAsAbsent(){
            this.loading2 = true
            this.updateMeetingParticipantsAttendanceStatusAction({
                course_id: this.$route.params.id,
                zoom_meeting_id: this.selected_meeting,
                user_ids: this.selected,
                status: 0
            }).then(() => {
                this.selected = []
                this.getEnrollees()
                this.alertMutation({
                    show: true,
                    text: 'Attendance status updated',
                    type: "success"
                })
            }).catch(() => {
                this.alertMutation({
                    show: true,
                    text: 'Something went wrong',
                    type: "error"
                })
            }).finally(() => {
                this.loading2 = false
            })
        },

        resetFilters(){
            this.filter = {
                present: ''
            }
        },

        exportList(){
            this.loading4 = true
            this.exportMeetingParticipantsAction({
                course_id: this.$route.params.id,
                zoom_meeting_id: this.selected_meeting,
                search: this.search,
                status: this.filter.status
            }).then(res => {
                this.loading4 = false
                this.alertMutation({
                    show: true,
                    text: 'Export is being processed in the background.',
                    type: "success"
                })
            }).catch(() => {
                this.loading4 = false
                this.alertMutation({
                    show: true,
                    text: 'Something went wrong',
                    type: "error"
                })
            })
        }
    }
}

</script>