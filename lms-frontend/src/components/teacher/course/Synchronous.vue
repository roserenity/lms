<template>
    <section class="d-flex flex-wrap" v-if="!loading" id="synchronous">
        <v-row justify="center">
            <v-col cols="12" lg="9">
                <v-sheet class="transparent" height="auto">
                    <v-card class="py-10 px-10 custom-border border">
                        <v-form ref="form" v-if="selectedMeeting === null">
                            <v-row no-gutters class="">
                                <v-col cols="12" sm="3" class="mt-2">
                                    <label class="caption">TOPIC TITLE *</label>
                                </v-col>
                                <v-col cols="12" sm="6">
                                    <FormTextFieldRequired
                                        :value.sync="topic"
                                        :disabled="!$isReadOnly.showCardAction(status, permission)"
                                        class="mb-2"
                                        :error="errors.topic"
                                    />
                                </v-col>
                            </v-row>
                            <!-- <v-row no-gutters>
                                <v-col cols="12" sm="3" class="">
                                    <div class="f12 poppins secondary-4--text fw500 ma-1">SECURITY *</div>
                                </v-col>
                                <v-col cols="12" sm="6" class="d-flex flex-wrap">
                                    <v-row no-gutters>
                                        <v-col cols="12" sm="5">
                                            <v-checkbox
                                                v-model="default_password"
                                                :value="default_password"
                                                label="Set Password"
                                                class="f12 mr-3"
                                                :disabled="$isReadOnly.isReadOnly(status, permission)">
                                            </v-checkbox>
                                        </v-col>
                                        <v-col cols="12" sm="7">
                                            <v-text-field 
                                                v-if="default_password"
                                                outlined
                                                dense
                                                class="general-custom-field roboto f14 fw500 secondary--text"
                                                :rules="[v => !!v || 'This is required',
                                                    v => !!v && v.length > 5 || 'Password must be at least 6 characters']"
                                                type="password"
                                                v-model="pw"
                                                prefix="PW"
                                                :readonly="$isReadOnly.isReadOnly(status, permission)"
                                            />
                                        </v-col>
                                    </v-row>
                                </v-col>
                            </v-row> -->
                            <v-row no-gutters>
                                <v-col cols="12" sm="3" class="mt-2">
                                    <label class="caption">AGENDA *</label>
                                </v-col>
                                <v-col cols="12" sm="6">
                                    <FormTextFieldRequired
                                        :value.sync="agenda"
                                        :disabled="!$isReadOnly.showCardAction(status, permission)"
                                        class="mb-2"
                                        :error="errors.agenda"
                                    />
                                </v-col>
                            </v-row>

                            <v-row no-gutters>
                                <v-col cols="12" sm="3" class="mt-2">
                                    <label class="caption">ZOOM URL *</label>
                                </v-col>
                                <v-col cols="12" sm="6">
                                    <FormTextFieldRequired
                                        :value.sync="zoom_url"
                                        :disabled="!$isReadOnly.showCardAction(status, permission)"
                                        :error="errors.zoom_url"
                                        class="mb-2"
                                    />
                                </v-col>
                            </v-row>

                            <v-row no-gutters>
                                <v-col cols="12" sm="3" class="mt-2">
                                    <label class="caption">SCHEDULE *</label>
                                </v-col>
                                <v-col cols="12" sm="6">
                                    <v-menu
                                        ref="menu"
                                        v-model="menu"
                                        :close-on-content-click="false"
                                        :return-value.sync="date"
                                        transition="scale-transition"
                                        offset-y
                                        min-width="auto"
                                    >
                                        <template v-slot:activator="{ on, attrs }">
                                            <v-text-field
                                                outlined
                                                dense
                                                v-model="date"
                                                prepend-icon="mdi-calendar"
                                                v-bind="attrs"
                                                v-on="on"
                                                required
                                                hide-details="auto"
                                                class="general-custom-field mb-2"
                                                :error-messages="errors.start_date"
                                                :rules="[v => !!v || 'This is required']"
                                                :readonly="!$isReadOnly.showCardAction(status, permission)"
                                                :disabled="!$isReadOnly.showCardAction(status, permission)"
                                            ></v-text-field>
                                        </template>
                                        <v-date-picker
                                            v-model="date"
                                            no-title
                                            scrollable
                                            :allowed-dates="(val) => val >= new Date().toISOString().substr(0, 10)"
                                            >
                                            <v-spacer></v-spacer>
                                            <v-btn
                                                text
                                                color="primary"
                                                @click="menu = false"
                                            >
                                                Cancel
                                            </v-btn>
                                            <v-btn
                                                text
                                                color="primary"
                                                @click="$refs.menu.save(date)"
                                            >
                                                OK
                                            </v-btn>
                                        </v-date-picker>
                                    </v-menu>
                                    <v-menu
                                        ref="menu2"
                                        v-model="menu2"
                                        :close-on-content-click="false"
                                        :nudge-right="40"
                                        :return-value.sync="time"
                                        transition="scale-transition"
                                        offset-y
                                        max-width="290px"
                                        min-width="290px"
                                    >
                                        <template v-slot:activator="{ on, attrs }">
                                        <v-text-field
                                            outlined
                                            dense
                                            class="general-custom-field mb-2"
                                            v-model="getTime"
                                            prepend-icon="mdi-clock-time-four-outline"
                                            readonly
                                            v-bind="attrs"
                                            v-on="on"
                                            required
                                            hide-details="auto"
                                            :error-messages="errors.start_time"
                                            :rules="[v => !!v || 'This is required']"
                                            :disabled="!$isReadOnly.showCardAction(status, permission)"
                                        ></v-text-field>
                                        </template>
                                        <v-time-picker
                                            v-if="menu2"
                                            v-model="time"
                                            full-width
                                            ampm-in-title
                                            format="ampm"
                                            :min="this.date === new Date().toISOString().substring(0, 10) ? new Date().toTimeString().split(' ')[0] : ''"
                                            @click:minute="$refs.menu2.save(time)"
                                        ></v-time-picker>
                                    </v-menu>
                                </v-col>
                            </v-row>
                        </v-form>
                        <v-form ref="form" v-else>
                            <v-row no-gutters>
                                <v-col cols="3" align="">
                                    <label class="caption">TOPIC TITLE *</label>
                                </v-col>
                                <v-col cols="6">
                                    <FormTextFieldRequired
                                        :value.sync="selectedMeeting.data.topic"
                                        :disabled="!$isReadOnly.showCardAction(status, permission)"
                                        class="mb-2"
                                        :error="errors.topic"
                                    />
                                </v-col>
                            </v-row>
                            <v-row no-gutters>
                                <v-col cols="3">
                                    <label class="caption">AGENDA *</label>
                                </v-col>
                                <v-col cols="6">
                                    <FormTextFieldRequired
                                        :value.sync="selectedMeeting.data.agenda"
                                        :disabled="!$isReadOnly.showCardAction(status, permission)"
                                        class="mb-2"
                                        :error="errors.agenda"
                                    />
                                </v-col>
                            </v-row>
                            <v-row no-gutters>
                                <v-col cols="12" sm="3" class="mt-2">
                                    <label class="caption">SCHEDULE *</label>
                                </v-col>
                                <v-col cols="12" sm="6">
                                    <v-menu
                                        ref="menu"
                                        v-model="menu"
                                        :close-on-content-click="false"
                                        :return-value.sync="date"
                                        transition="scale-transition"
                                        offset-y
                                        min-width="auto"
                                    >
                                        <template v-slot:activator="{ on, attrs }">
                                            <v-text-field
                                                v-model="selectedMeeting.data.start_date"
                                                :error-messages="errors.start_date"
                                                prepend-icon="mdi-calendar"
                                                readonly
                                                v-bind="attrs"
                                                v-on="on"
                                                required
                                                hide-details="auto"
                                                outlined
                                                dense
                                                class="general-custom-field mb-2"
                                                :rules="[v => !!v || 'This is required']"
                                                :readonly="!$isReadOnly.showCardAction(status, permission)"
                                                :disabled="!$isReadOnly.showCardAction(status, permission)"
                                            ></v-text-field>
                                        </template>
                                        <v-date-picker
                                            v-model="selectedMeeting.data.start_date"
                                            no-title
                                            scrollable
                                            :allowed-dates="(val) => val >= new Date().toISOString().substr(0, 10)"
                                            >
                                            <v-spacer></v-spacer>
                                            <v-btn
                                                text
                                                color="primary"
                                                @click="menu = false"
                                            >
                                                Cancel
                                            </v-btn>
                                            <v-btn
                                                text
                                                color="primary"
                                                @click="$refs.menu.save(date)"
                                            >
                                                OK
                                            </v-btn>
                                        </v-date-picker>
                                    </v-menu>
                                    <v-menu
                                        ref="menu2"
                                        v-model="menu2"
                                        :close-on-content-click="false"
                                        :nudge-right="40"
                                        :return-value.sync="time"
                                        transition="scale-transition"
                                        offset-y
                                        max-width="290px"
                                        min-width="290px"
                                    >
                                        <template v-slot:activator="{ on, attrs }">
                                        <v-text-field
                                            v-model="selectedTime"
                                            prepend-icon="mdi-clock-time-four-outline"
                                            readonly
                                            v-bind="attrs"
                                            v-on="on"
                                            required
                                            hide-details="auto"
                                            outlined
                                            dense
                                            :error-messages="errors.start_time"
                                            class="general-custom-field mb-2"
                                            :rules="[v => !!v || 'This is required']"
                                            :disabled="!$isReadOnly.showCardAction(status, permission)"
                                        ></v-text-field>
                                        </template>
                                        <v-time-picker
                                            v-if="menu2"
                                            v-model="selectedMeeting.data.start_time"
                                            full-width
                                            ampm-in-title
                                            format="ampm"
                                            :min="this.date === new Date().toISOString().substring(0, 10) ? new Date().toTimeString().split(' ')[0] : ''"
                                            @click:minute="$refs.menu2.save(time)"
                                        ></v-time-picker>
                                    </v-menu>
                                </v-col>
                            </v-row>
                            <v-row no-gutters>
                                <v-col cols="12" sm="3" class="mt-2">
                                    <label class="caption">ZOOM URL *</label>
                                </v-col>
                                <v-col cols="12" sm="6">
                                    <v-textarea 
                                        outlined
                                        dense
                                        class="general-custom-field roboto f14 fw500 secondary--text"
                                        required
                                        :error-messages="errors.zoom_url"
                                        v-model="selectedMeeting.data.join_url"
                                        :rules="[v => !!v || 'This is required']"
                                        :readonly="!$isReadOnly.showCardAction(status, permission)"
                                        :disabled="!$isReadOnly.showCardAction(status, permission)"
                                    />
                                </v-col>
                                <v-col cols="1" class="ml-1">
                                    <v-btn dense class="roboto f12 secondary-1--text" depressed @click="copyToClipboard(selectedMeeting.data.join_url)">
                                        <v-icon size="18">
                                            mdi-checkbox-multiple-blank-outline
                                        </v-icon>
                                        Copy
                                    </v-btn>
                                </v-col>
                            </v-row>

                            <!-- <v-row no-gutters align="center">
                                <v-col cols="3">
                                    <div class="f12 poppins secondary-4--text fw500 ma-1">START TIME *</div>
                                </v-col>
                                <v-col cols="6" class="d-flex flex-row">
                                    {{ $dateFormat.dayDateTime(selectedMeeting.data.start_time)}}
                                </v-col>
                            </v-row> -->

                            <!-- <v-row no-gutters align="center" class="mt-5">
                                <v-col cols="3">
                                    <div class="f12 poppins secondary-4--text fw500 ma-1">MEETING LINK</div>
                                </v-col>
                                <v-col cols="6" class="d-flex flex-row">
                                    <div 
                                        id="start_url"
                                        v-html="selectedMeeting.data.start_url"
                                        class="roboto f14 fw500 primary--text"></div>
                                </v-col>
                                <v-col cols="1" class="ml-1">
                                    <v-btn dense class="roboto f12 secondary-4--text" depressed @click="copyToClipboard(selectedMeeting.data.start_url)">
                                        <v-icon size="18">
                                            mdi-checkbox-multiple-blank-outline
                                        </v-icon>
                                        Copy
                                    </v-btn>
                                </v-col>
                            </v-row> -->

                            <!-- <v-row no-gutters align="center" class="mt-5">
                                <v-col cols="3">
                                    <div class="f12 poppins secondary-4--text fw500 ma-1">PASSCODE</div>
                                </v-col>
                                <v-col cols="6" class="d-flex flex-row">
                                    <div 
                                        v-html="selectedMeeting.data.password"
                                        class="roboto f14 fw500 primary--text"
                                        append-icon="mdi-account"
                                        ></div>
                                </v-col>
                                <v-col cols="1" class="ml-1">
                                    <v-btn dense class="roboto f12 secondary-4--text" depressed @click="copyToClipboard(selectedMeeting.data.password)">
                                        <v-icon size="18">
                                            mdi-checkbox-multiple-blank-outline
                                        </v-icon>
                                        Copy
                                    </v-btn>
                                </v-col>
                            </v-row> -->

                        </v-form>
                        <v-divider class="my-5"></v-divider>
                        <section class="d-flex align-center justify-end">
                            <v-btn dense v-if="!selectedMeeting && $isReadOnly.showCardAction(status, permission)" color="primary" class="my-auto f12 poppins white--text fw600" @click="createMeeting" :loading="loadingSave">
                                SAVE MEETING
                            </v-btn>
                        </section>

                        <section class="d-flex flex-wrap" v-if="$isReadOnly.showCardAction(status, permission)">

                            <v-btn v-if="selectedMeeting" depressed transparent color="error" class="my-auto f12 poppins white--text fw600 mb-2" @click="deleteMeeting({id: selectedMeeting.id, course_id: selectedMeeting.course_id})" :loading="loadingDelete">
                                DELETE MEETING
                            </v-btn>    

                            <v-spacer />

                            <v-btn v-if="selectedMeeting" color="primary" class="my-auto f12 poppins white--text fw600 mb-2" @click="saveMeeting" :loading="loadingSave">
                                UPDATE
                            </v-btn>
                        </section>
                    </v-card>
                </v-sheet>
            </v-col>
            <v-col cols="12" lg="3" class="">
                <div class="py-0 pr-0">
                    <v-btn dense v-if="meetings.length > 0 || !$isReadOnly.isReadOnly(status, permission)" block color="primary" class="f12 poppins white--text fw600" @click="reset()">
                        <v-icon>mdi-plus</v-icon>
                        CREATE NEW MEETING
                    </v-btn>
                    <v-expansion-panels class="custom-border border mt-3" accordion flat v-model="expand_material">
                        <v-expansion-panel>
                        <v-expansion-panel-header color="secondary-5"
                            class="m-0 p-0 mat_expansion_panel ">
                            <template v-slot:default="{ open }">
                            <v-container class="m-0 p-0 flex-row align-center roboto f14 fw500 secondary-1--text">
                                <b>ZOOM SCHEDULES</b> 
                            </v-container>
                            </template>
                        </v-expansion-panel-header>
                        <v-expansion-panel-content class="m-0 p-0 expansion-timeline ">
                            <v-container class="roboto secondary-2--text f12 fw500 pl-3" v-if="meetings.length === 0">
                            No scheduled meetings
                            </v-container>
                            <v-list v-else>
                                <v-list-item 
                                    v-for="(_meeting, i) in meetings" :key="i" dense class="d-flex flex-row align-center pl-3 py-5"
                                    @click="selectedMeeting = _meeting">
                                    <section class="d-flex flex-column">
                                        <div class="roboto f13 fw500 secondary-1--text">{{ _meeting.data.topic }}</div>                                
                                        <span class="roboto f11 secondary-2--text">SCHEDULE: {{ $dateFormat.mmDDyy(_meeting.data.start_date) }} {{ $dateFormat.timeAndPeriod(_meeting.data.start_date +'T' + _meeting.data.start_time + ':00') }}</span>
                                    </section>
                                </v-list-item>
                            </v-list>
                        </v-expansion-panel-content>
                        </v-expansion-panel>
                    </v-expansion-panels>
                </div>
            </v-col>
        </v-row>
    </section>
    <circular v-else />
</template>

<script>
import { mapActions, mapMutations, mapState } from "vuex";

export default { 
    props: ['course_id', 'permission', 'status'],
    data: () => ({
        topic: '',
        agenda: '',
        pw: '',
        loadingSave: false,
        default_password: false,
        start_time: null,
        mode: 'add',
        expand_material: 0,
        menu: false,
        menu2: false,
        date: null,
        time: null,
        selectedMeeting: null,
        period: 'AM',
        loading: false,
        loadingDelete: false,
        zoom_url: '',
        errors: {}
    }),
    computed: {
        ...mapState('instructor', {
            meetings: (state) => state.meetings
        }),

        getTime: {
            set() {
                return this.time
            },
            get(val) {
                if( !this.date ) return this.time
                if(this.date) {
                    return this.time ? this.$dateFormat.timeAndPeriod(this.date +'T' + this.time + ':00') : null
                }
            }
        },

        selectedTime() {
            return this.$dateFormat.timeAndPeriod(this.selectedMeeting.data.start_date +'T' + this.selectedMeeting.data.start_time + ':00')
        }
    },
    methods:{
        ...mapMutations(['alertMutation']),
        ...mapActions('instructor', ['getMeetingsAction', 'addMeetingAction', 'deleteMeetingAction', 'updateMeetingAction']),

        createMeeting(){
            this.errors = {}
            if(this.$refs.form.validate()) {
                this.loadingSave = true
                // let _timezone = "Asia/Singapore"
                let _form = {}

                this.start_time = this.date +'T' + this.time + ':00'

                // if(this.default_password) {
                //     _form = {
                //         course_id: this.course_id,
                //         topic: this.topic,
                //         default_password: true,
                //         password: this.pw,
                //         agenda: this.agenda,
                //         timezone: _timezone,
                //         start_time: this.start_time,
                //         duration: 60
                //     }
                // } else {
                //     _form = {
                //         course_id: this.course_id,
                //         topic: this.topic,
                //         default_password: false,
                //         agenda: this.agenda,
                //         timezone: _timezone,
                //         start_time: this.start_time,
                //         duration: 60
                //     }
                // }

                _form = {
                    course_id: this.course_id,
                    topic: this.topic,
                    agenda: this.agenda,
                    zoom_url: this.zoom_url,
                    start_date: this.date,
                    start_time: this.time
                }
                    
                this.addMeetingAction(_form).then(()=>{
                    this.loadingSave = false
                    this.time = null
                    this.$refs.form.reset()
                    this.alertMutation({
                        show: true,
                        text: 'Meeting successfully created!',
                        type: "success"
                    })
                }).catch(err => {
                    this.errors = err
                    this.loadingSave = false
                    this.alertMutation({
                        show: true,
                        text: 'Something went wrong',
                        type: "error"
                    })
                })
            }
        },

        saveMeeting(){
            this.errors = {}
            if(this.$refs.form.validate()) {
                this.loadingSave = true
                // let _timezone = "Asia/Singapore"

                // this.start_time = this.date +'T' + this.time + ':00'

                this.updateMeetingAction({
                    // id: this.selectedMeeting.id,
                    // course_id: this.selectedMeeting.course_id,
                    // topic: this.selectedMeeting.data.topic,
                    // agenda: this.selectedMeeting.data.agenda,
                    // meeting_id: this.selectedMeeting.data.id,
                    // start_time: this.selectedMeeting.data.start_time,
                    // password: this.selectedMeeting.data.password
                    course_id: this.course_id,
                    id: this.selectedMeeting.id,
                    topic: this.selectedMeeting.data.topic,
                    agenda: this.selectedMeeting.data.agenda,
                    zoom_url: this.selectedMeeting.data.join_url,
                    start_date: this.selectedMeeting.data.start_date,
                    start_time: this.selectedMeeting.data.start_time
                }).then(()=>{
                    this.loadingSave = false
                    this.alertMutation({
                        show: true,
                        text: 'Meeting successfully updated!',
                        type: "success"
                    })
                }).catch(err => {
                    this.errors = err
                    this.loadingSave = false
                    this.alertMutation({
                        show: true,
                        text: 'Something went wrong',
                        type: "error"
                    })
                })
            }
        },

        deleteMeeting(_meeting){
            this.errors = {}
            this.loadingDelete = true
            this.deleteMeetingAction(_meeting).then(()=>{
                this.alertMutation({
                    show: true,
                    text: 'Meeting successfully deleted!',
                    type: "success"
                })
                this.reset()
                this.loadingDelete = false
            }).catch(err => {
                this.errors = err
                this.loadingSave = false
                this.alertMutation({
                    show: true,
                    text: 'Something went wrong',
                    type: "error"
                })
            })
        },

        reset(){
            this.errors = {}
            this.selectedMeeting = null
            this.$refs.form.reset()
        },

        getFormattedDate(date) {
            return this.$dateFormat.mmDDyy(date)
        },

        getFormattedTime(date) {
            return this.$dateFormat.hhMMss(date)
        },

        copyToClipboard(text){
            navigator.clipboard.writeText(text);
            this.alertMutation({
                show: true,
                text: 'Copied!',
                type: "success"
            })
        }
    },
    mounted(){
        this.loading = true
        this.getMeetingsAction({course_id: this.course_id}).then(()=> this.loading = false).catch(() => {
            this.loading = false
        })
    },
    watch: {
        course_id() {
            this.getMeetingsAction({course_id: this.course_id})
        },
        selectedMeeting(){
            this.$vuetify.goTo('#synchronous', { offset: 0})
        }
    },
}
</script>

<style scoped>
.v-input--selection-controls {
    margin-top: 0 !important;
}

#start_url {
    word-break: break-all !important;
}
</style>