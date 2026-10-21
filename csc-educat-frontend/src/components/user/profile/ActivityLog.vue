<template>
    <div>
        <SkeletonLoaderCard height="400" v-if="loading && history.length === 0"/>
        <v-card class="custom-border border" v-else>
            <v-card-title class="d-flex flex-wrap align-center justify-space-between">
                <section class="d-flex flex-row align-center">
                    <v-icon color="primary" class="pr-2">mdi-clock-outline</v-icon>
                    <div class="f18 primary-font secondary-1--text fw600">Activity Log</div>
                </section>
                <section class="d-flex flex-column justify-end ml-auto">
                    <FormLabel :label="'DATE: '" />
                    <FormMonthPicker 
                        :val="selectedDate"
                        :class_="'col-12'"
                        @setMonth="(e) => { selectedMonth = e }"
                        @setYear="(e) => { selectedYear = e }"
                        @apply="getActivityLog"/>
                </section>
            </v-card-title>
            <v-progress-linear
                indeterminate
                color="primary"
                rounded
                height="3px"
                v-if="loading"
            ></v-progress-linear>
            <v-divider></v-divider>
        <v-sheet height="auto" max-height="50vh" class="d-flex overflow-y-auto scroller flex-wrap ma-5 fade">
                <v-timeline
                    align-top
                    dense
                    v-if="history.length > 0 && !loading"
                >
                    <v-timeline-item
                        v-for="(item, i) in history"
                        :key="i"
                        :color="item.hasOwnProperty('revoked') ? (item.revoked ? 'secondary' : 'primary') : 'success'"
                        small
                        fill-dot
                        >
                        <div v-if="item.hasOwnProperty('revoked')">
                            <v-btn
                                    elevation="0"
                                    x-small
                                    flat
                                    :color="item.revoked ? 'secondary' : 'primary'"
                                    class="mb-1 text-uppercase"
                                    dense
                                >
                                {{ item.revoked ? 'Log out' : 'Log in' }}
                            </v-btn>
                        </div>
                        <div v-else>
                            <section v-if="item.log_name === 'user_enrollment'">
                                <v-btn
                                    elevation="0"
                                    x-small
                                    flat
                                    color="success"
                                    class="mb-1"
                                    dense
                                >
                                    ENROLLED
                                </v-btn>
                                <div class="fw500 f14">
                                    {{ item?.course?.title }}
                                </div>
                                <div class="fw500 f11 text-uppercase secondary-1--text mt-2" v-if="item?.subject_id !== item?.causer_id">
                                    ENROLLED BY: 
                                    {{ item?.causer?.first_name ? item?.causer?.first_name : item?.causer?.role }}
                                    {{ $userFormat.middle_initial(item?.causer?.middle_name) }}
                                    {{ item?.causer?.last_name ? item?.causer?.last_name : 'Account' }}
                                    {{ item?.causer?.suffix ? item?.causer?.suffix : '' }}
                                </div>
                            </section>
                            <section v-if="['user_course_activity', 'course_checkout'].includes(item.log_name)">
                                <v-btn
                                    elevation="0"
                                    x-small
                                    flat
                                    color="amber"
                                    class="mb-1 text-uppercase"
                                    dense
                                >
                                    {{ item?.description }}
                                </v-btn>
                                <div class="fw500 f14">
                                    {{ item?.course?.title }}
                                </div>
                            </section>
                            <section v-if="['user_certificate_gen'].includes(item.log_name)">
                                <v-btn
                                    elevation="0"
                                    x-small
                                    flat
                                    color="amber"
                                    class="mb-1 text-uppercase"
                                    dense
                                >
                                    COURSE CERTIFICATE GENERATED
                                </v-btn>
                                <div class="fw500 f14">
                                    {{ item?.course?.title }}
                                </div>
                            </section>
                            <section v-if="['user_course_complete'].includes(item.log_name)">
                                <v-btn
                                    elevation="0"
                                    x-small
                                    flat
                                    color="amber"
                                    class="mb-1 text-uppercase"
                                    dense
                                >
                                    COURSE COMPLETED
                                </v-btn>
                                <div class="fw500 f14">
                                    {{ item?.course?.title }}
                                </div>
                            </section>
                        </div>
                        <div class="secondary-2--text text-uppercase fw500 f10" >{{ $dateFormat.dayDateTime(item.created_at) }} </div>
                    </v-timeline-item>
                </v-timeline>

                <div class="fw400 py-0 secondary-2--text f14 mb-3" v-else-if="history.length === 0 && !loading">
                    You have no login activities for {{ $dateFormat.MMYY(this.selectedDate) }}
                </div>
            </v-sheet>
        </v-card>
    </div>
</template>

<script>
import { mapActions } from 'vuex'
import SkeletonLoaderCard from '@/components/reusable/SkeletonLoaderCard.vue'

export default {
    components: {
        SkeletonLoaderCard
    },
    data: () => ({
        history: [],
        loading: false,
        selectedDate: '',
        selectedMonth: '',
        selectedYear: '',
    }),
    methods: {
        ...mapActions(['getLoginHistory', 'getActivityLogAction']),

        getActivityLog() {
            this.loading = true
            this.selectedDate = `${this.selectedYear}-${this.selectedMonth}`
            console.log(this.selectedDate)
            this.getActivityLogAction({ month: this.selectedMonth, year: this.selectedYear }).then(res => {
                this.history =  Object.values(res);
            }).finally(() => {
                this.loading = false
            })
        }
    },
    mounted(){
        this.loading = true
        this.selectedDate = `${new Date().getFullYear()}-${(new Date().getMonth() + 1)}`
        this.getActivityLogAction({ month: new Date().getMonth()  + 1, year: new Date().getFullYear()}).then(res => {
            this.history =  Object.values(res);
        }).finally(() => {
            this.loading = false
        })
        /* this.getLoginHistory({ date_from: (new Date(Date.now() - (new Date()).getTimezoneOffset() * 60000)).toISOString().substr(0, 10), date_to: (new Date(Date.now() - (new Date()).getTimezoneOffset() * 60000)).toISOString().substr(0, 10)}).then(res => {
            this.history = res
        }) */
    },
}
</script>