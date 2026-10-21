<template>
    <section class="border pt-3">
        <section class="d-flex flex-wrap justify-space-between mb-3">
            <SearchBar
                :placeholder="course_id === null ? 'Search course' : 'Search user'" 
                :value.sync="search" 
                @search="() => {page = 1, getData(course_id) }" 
                @clear="() => { search = '', page = 1, getData(course_id) }"
                class="my-2"/>
            <div class="d-flex flex-wrap justify-space-between align-center ml-auto">
                <FilterMenu
                    :initialFilter="course_filter"
                    @resetFilters="clearFilters"
                    @applyFilters="()=>{ page = 1, getData(course_id)}"
                    class="my-2"
                    v-if="course_id === null"
                >
                    <v-sheet max-height="350" class="overflow-y-auto scroller">
                        <FormSelectObject
                            :label="'STATUS'"
                            :items="status"
                            :value.sync="course_filter.status"
                        />
                        
                        <FormSelectObject
                            class="mt-3"
                            :label="'TYPE OF LEARNING'"
                            :items="type_of_learnings"
                            :value.sync="course_filter.type_of_learning"
                        />

                        <div class="mt-3">
                            <FormLabel class="mb-1" :label="'COURSE CONDUCTED DATE (YYYY-MM-DD)'" />
                        </div>
                        <label class="caption">FROM:</label>
                        <FormDatePicker 
                            :val="course_filter.date_from"
                            :max="maxDate"
                            class_="col-11"
                            @apply="(e) => {
                                course_filter.date_from = e
                                minDate = e
                            }"/>

                        <label class="caption">TO:</label>
                        <FormDatePicker 
                            :val="filter.date_to"
                            :min="minDate"
                            class_="col-11"
                            @apply="(e) => {
                                course_filter.date_to = e
                                maxDate = e
                            }"/>`
                        
                        <div>
                            <FormLabel class="mb-1" :label="'ENROLLMENT DATE (YYYY-MM-DD)'" />
                        </div>
                        <label class="caption">FROM:</label>
                        <FormDatePicker 
                            :val="course_filter.enrolled_from"
                            :max="maxDate2"
                            class_="col-11"
                            @apply="(e) => {
                                course_filter.enrolled_from = e
                                minDate2 = e
                            }"/>

                        <label class="caption">TO:</label>
                        <FormDatePicker 
                            :val="course_filter.enrolled_to"
                            :min="minDate2"
                            class_="col-11"
                            @apply="(e) => {
                                course_filter.enrolled_to = e
                                maxDate2 = e
                            }"/>`
                    </v-sheet>
                </FilterMenu>
                <ButtonExport 
                    @click="exportTable"
                    :loading="loading1"
                    :disabled="loading || items.length === 0"
                    class="my-2 ml-auto"/>
            </div>
        </section>
        <section class="d-flex flex-row justify-space-between align-center mb-3 mr-2" v-if="course_id">
            <v-btn @click="() => {
                course_id = null
                title = null
                selected = 'All'
                page = 1
                items = []
                search = ''
                type = 'courses'
                $emit('closeList')
            }"
                class="flat fw600 px-1 text-wrap" dense color="primary" text>
                <v-icon>
                    mdi-chevron-left
                </v-icon>
                {{ title }}
            </v-btn>
        </section>
        <v-expand-transition>
            <section>
                <section v-if="totalCount !== 0" class="mb-1 d-flex flex-row justify-space-between mx-2">
                    <div>
                        <h4 class="poppins secondary-1--text">
                            {{  type === 'courses' ? 'List of Courses' : ( type === 'completers' ?  'Completers' : 'Enrolled Learners' ) }}
                        </h4>
                        <FormLabel v-if="(course_filter.enrolled_from || course_filter.enrolled_to) && type !== 'all_enrollees'" :label="`(${course_filter.enrolled_from ? `from ${$dateFormat.mmDDyy(course_filter.enrolled_from)}` : ''}${course_filter.enrolled_to ? ` to ${$dateFormat.mmDDyy(course_filter.enrolled_to)}` : ''})`" class="mb-3"/>
                    </div>
                    <FormLabel :label="`${totalCount} result/s`" class="ml-auto"/>
                </section>
                <v-data-table
                    :headers="type === 'courses' ? instructor_reporting_courses_tbl : ( type === 'completers' ? instructor_reporting_completers_users_tbl : instructor_reporting_enrolled_users_tbl )"
                    :items="items"
                    class="elevation-1 custom-border border poppins"
                    hide-default-footer
                    :loading="loading"
                    :items-per-page.sync="itemsPerPage"
                    >
                    
                    <template v-slot:header.enrollments_count="{ header }" >
                            <div class="text-uppercase">{{ header.text }}</div>
                            <div class="f10">{{ `${course_filter.enrolled_from ? `from ${$dateFormat.mmDDyy(course_filter.enrolled_from)}` : ''}${course_filter.enrolled_to ? ` to ${$dateFormat.mmDDyy(course_filter.enrolled_to)})` : ''}` }}</div>
                    </template>
                    <template v-slot:header.completed_course_count="{ header }" >
                            <div class="text-uppercase">{{ header.text }}</div>
                            <div class="f10">from ENROLLEES</div>
                    </template>

                    <template v-slot:item.title="{ item }">
                        <span v-if="item.assessments_count > 0" text class="text-wrap pa-2 text-right ma-0 primary--text cursor-pointer font-weight-bold " :style="'letter-spacing: normal; justify-content: flex-start;'" @click="$emit('updateMode', { mode: 2, selected_course: item })">
                            {{ item.title }}
                        </span>
                        <span v-else text class="text-wrap pa-2 text-right ma-0" :style="'letter-spacing: normal; justify-content: flex-start;'">
                            {{ item.title }}
                        </span>
                    </template>

                    <template v-slot:item.user.enrollments.0.created_at="{ item }">
                        <span v-if="item"> {{ $dateFormat.mmDDyy(item.user.enrollments[0].created_at) }} </span>
                    </template>

                    <template v-slot:item.enrollments.0.created_at="{ item }">
                        <span v-if="item"> {{ $dateFormat.mmDDyy(item.enrollments[0].created_at) }} </span>
                    </template>

                    <template v-slot:item.total_enrollments="{ item }">
                        <span v-if="item" @click="() => {
                                if(item.total_enrollments > 0) {
                                    course_id = item.id
                                    title = item.title
                                    page = 1
                                    items = []
                                    search = ''
                                    type = 'all_enrollees'
                                    getData(course_id)
                                }
                            } "
                            class="primary--text cursor-pointer font-weight-bold ">{{ item.total_enrollments }}</span>
                    </template>

                    <template v-slot:item.enrollments_count="{ item }">
                        <span v-if="item" @click="() => {
                                if(item.enrollments_count > 0) {
                                    course_id = item.id
                                    title = item.title
                                    page = 1
                                    items = []
                                    search = ''
                                    type = 'enrollees'
                                    getData(course_id)
                                }
                            } "
                            class="primary--text cursor-pointer font-weight-bold ">{{ item.enrollments_count }}</span>
                    </template>

                    <template v-slot:item.completed_course_count="{ item }">
                        <span v-if="item" @click="() => {
                                if(item.completed_course_count > 0) {
                                    course_id = item.id
                                    title = item.title
                                    page = 1
                                    items = []
                                    search = ''
                                    type = 'completers'
                                    getData(course_id)
                                }
                            } "
                            class="primary--text cursor-pointer font-weight-bold ">{{ item.completed_course_count }}</span>
                    </template>


                    <template v-slot:item.start="{ item }">
                        <span v-if="item">{{ item.available_until?.start_date ? $dateFormat.mmDDyy(item.available_until.start_date) : '' }}</span>
                    </template>

                    <template v-slot:item.end="{ item }">
                        <span v-if="item">{{ item.available_until?.end_date ? $dateFormat.mmDDyy(item.available_until.end_date) : '' }}</span>
                    </template>
                    
                    <template v-slot:item.status="{ item }">
                        <span v-if="item && ['all_enrollees', 'enrollees'].includes(type)">
                            <v-chip small label color="success" v-if="item.user.completed_course.length > 0">COMPLETED</v-chip>
                            <v-chip small label color="warning" v-else>IN-PROGRESS</v-chip>
                        </span>
                        <span v-else="item">{{ item.status }}ED</span>
                    </template>

                    <template v-slot:item.certificate="{ item }">
                        <!-- <a
                            v-if="item.certificate && item.certificate.url !== '' && getCourseProgress(item.course) === 100"
                            :href="item.certificate.url"
                            class="primary--text text-decoration-none f10"
                            target="_blank"
                            >
                            <v-icon small class="mr-1" dense>mdi-open-in-new</v-icon>
                            View
                        </a> -->
                        <a
                            v-if="item.certificate && item.certificate.url !== ''"
                            :href="item.certificate.url"
                            class="primary--text text-decoration-none f10"
                            target="_blank"
                            >
                            <v-icon small class="mr-1" dense>mdi-open-in-new</v-icon>
                            View
                        </a>
                    </template>

                    <template v-slot:item.created_at="{ item }" >
                        {{ $dateFormat.mmDDyy(item.created_at) }}
                    </template>

                    <template v-slot:item.date_completed="{ item }" >
                        <span v-if="item.date_completed">
                            {{ $dateFormat.ymd(item.date_completed) }}
                        </span>
                        <span v-else-if="item.progress === 100">
                            {{ $dateFormat.ymd(item.course.latest_activity_date) }}
                        </span>
                        <!-- <div v-if="getCourseProgress(item.course) === 100">
                            <span v-if="item.date_completed">
                                {{ $dateFormat.ymd(item.date_completed) }}
                            </span>
                            <span v-else-if="item.certificate">
                                {{ $dateFormat.ymd(item.certificate.created_at) }}
                            </span>
                            <span v-else-if="getCourseProgress(item.course) === 100">
                                {{ $dateFormat.ymd(item.course.latest_activity_date) }}
                            </span>
                        </div> -->
                    </template>

                    <template v-slot:item.date_enrolled="{ item }" >
                        <span >
                            {{ $dateFormat.ymd(item.date_enrolled) }}
                        </span>
                    </template>

                    <template v-slot:item.progress="{ item }">
                        <section class="d-flex flex-row align-center">
                            <v-progress-linear 
                                height="8"
                                :value="item.progress"
                                rounded
                                color="primary"
                                background-color="grey lighten-3"
                                class="mr-2"
                                style="width: 80%"
                            >
                            </v-progress-linear>
                            <div class="d-flex flex-row caption">
                                {{ item.progress }}%
                            </div>
                        </section>
                    </template>
                </v-data-table>
                <FormPagination 
                    :pageCount="pageCount" 
                    :page="page"
                    :paginate="paginate"
                    @page="(e) => {page = e, getData(course_id) }" 
                    @paginate="(e) => {paginate = e, page = 1, getData(course_id) }"/>
            </section>
        </v-expand-transition>
    </section>
</template>

<script>
import { mapActions, mapMutations } from 'vuex'
import { instructor_reporting_courses_tbl, instructor_reporting_enrolled_users_tbl, instructor_reporting_completers_users_tbl } from "@/constants/tblheaders/reporting"

export default {
    props: ['menu'],
    data: () => ({
        course_id: null,
        title: '',
        selected: 'All',
        type: 'courses',
        filter: [],
        pageCount: 1,
        totalCount: 0,
        paginate: '10',
        page: 1,
        loading: false,
        loading1: false,
        items: [],
        search: '',
        instructor_reporting_courses_tbl,
        instructor_reporting_enrolled_users_tbl,
        instructor_reporting_completers_users_tbl,
        minDate: '',
        maxDate: '',
        minDate2: '',
        maxDate2: '',
        course_filter: {
            status: '',
            type_of_learning: '',
            date_from: ``,
            date_to: ``,
            enrolled_from: '',
            enrolled_to: ''
        },
        type_of_learnings: [
            { text: 'ALL', value: '' },
            { text: 'E-LEARNING', value: 'E-LEARNING' },
            { text: 'BLENDED', value: 'BLENDED' },
            { text: 'MICROLEARNING', value: 'MICROLEARNING' },
        ],
        status: [
            { text: 'ALL', value: '' },
            { text: 'PUBLISHED', value: 'PUBLISH' },
            { text: 'UNPUBLISHED', value: 'UNPUBLISH' },
        ],
    }),
    methods: {
        ...mapActions('instructor', [
            'getInstructorCoursesReportingStats',
            'getRegistrationFilterAction',
            'getInstructorCompleterUsersAction',
            'exportInstructorCompleterUsersAction',
            'getInstructorFilteredEnrolledUsersAction',
            'getInstructorEnrolledUsersAction',
            'exportInstructorCoursesReportingStats',
            'exportInstructorEnrolledUsersAction'
        ]),

        ...mapMutations(['alertMutation']),

        clearFilters(){
            this.course_filter = {
                status: '',
                type_of_learning: '',
                date_from: ``,
                date_to: ``,
                enrolled_from: '',
                enrolled_to: ''
            },

            this.minDate = ''
            this.maxDate = ''
            this.minDate2 = ''
            this.maxDate2 = ''
        },
        
        getData(item){
            if(this.type === 'courses' && !this.loading) {
                this.loading = true
                this.getInstructorCoursesReportingStats({ search: this.search, paginate: Number(this.paginate), page: this.page, date_from: this.course_filter.enrolled_from, date_to: this.course_filter.enrolled_to, ...this.course_filter }).then(res => {
                    this.items = res.data
                    this.pageCount = res.last_page
                    this.paginate = res.per_page.toString()
                    this.page = res.current_page
                    this.totalCount = res.total
                    this.loading = false
                }).catch(() => {
                    this.loading = false
                    this.fetchingErrorToast('courses')
                })
            } else if(this.type === 'all_enrollees' && !this.loading) {
                this.loading = true
                this.getInstructorEnrolledUsersAction({ course_id: item, search: this.search, paginate: Number(this.paginate), page: this.page, date_from: '', date_to: '' }).then(res => {
                    this.items = res.data
                    this.pageCount = res.last_page
                    this.paginate = res.per_page.toString()
                    this.page = res.current_page
                    this.totalCount = res.total
                    this.loading = false
                }).catch(() => {
                    this.loading = false
                    this.fetchingErrorToast('enrollees')
                })
            } else if(this.type === 'enrollees' && !this.loading) {
                this.loading = true
                this.getInstructorEnrolledUsersAction({ course_id: item, search: this.search, paginate: Number(this.paginate), page: this.page, date_from: this.course_filter.enrolled_from, date_to: this.course_filter.enrolled_to }).then(res => {
                    this.items = res.data
                    this.pageCount = res.last_page
                    this.paginate = res.per_page.toString()
                    this.page = res.current_page
                    this.totalCount = res.total
                    this.loading = false
                }).catch(() => {
                    this.loading = false
                    this.fetchingErrorToast('enrollees')
                })
            } else if ( this.type === 'completers' && !this.loading) {
                this.loading = true
                this.getInstructorCompleterUsersAction({ course_id: item, search: this.search, paginate: Number(this.paginate), page: this.page, date_from: this.course_filter.enrolled_from, date_to: this.course_filter.enrolled_to }).then(res => {
                    this.items = res.data
                    this.pageCount = res.last_page
                    this.paginate = res.per_page.toString()
                    this.page = res.current_page
                    this.totalCount = res.total
                    this.loading = false
                }).catch(() => {
                    this.loading = false
                    this.fetchingErrorToast('completers')
                })
            }
        },

        exportTable(){
            this.loading1 = true
            
            if(this.type === 'courses') {
                this.exportInstructorCoursesReportingStats({ search: this.search, date_from: this.course_filter.enrolled_from, date_to: this.course_filter.enrolled_to, ...this.course_filter }).then(() => {
                    this.alertMutation({
                        show: true,
                        text: 'Export is being processed in the background.',
                        type: "success"
                    })
                }).catch(() => {
                    this.exportingErrorToast('courses')
                }).finally(() => {
                    this.loading1 = false
                })
            } else if(this.type === 'completers')  {
                this.exportInstructorCompleterUsersAction({ course_id: this.course_id, search: this.search, date_from: this.course_filter.enrolled_from, date_to: this.course_filter.enrolled_to }).then((res) => {
                    this.$exportToCSV(`${this.title} - Completers`, res)
                }).catch(() => {
                    this.exportingErrorToast('completers')
                }).finally(() => {
                    this.loading1 = false
                })
            } else if (this.type === 'enrollees') {
                this.exportInstructorEnrolledUsersAction({ course_id: this.course_id, search: this.search, date_from: this.course_filter.enrolled_from, date_to: this.course_filter.enrolled_to }).then((res) => {
                    this.$exportToCSV(`${this.title} - Enrollees`, res)
                }).catch(() => {
                    this.exportingErrorToast('enrollees')
                }).finally(() => {
                    this.loading1 = false
                })
            }  else {
                this.exportInstructorEnrolledUsersAction({ course_id: this.course_id, search: this.search, date_from: '', date_to: '' }).then((res) => {
                    this.$exportToCSV(`${this.title} - Enrollees`, res)
                }).catch(() => {
                    this.exportingErrorToast('enrollees')
                }).finally(() => {
                    this.loading1 = false
                })
            } 
        },

        fetchingErrorToast(type){
            this.alertMutation({
                show: true,
                text: `Something went wrong in fetching ${type} data.`,
                type: "error"
            })
        },

        exportingErrorToast(type){
            this.alertMutation({
                show: true,
                text: `Something went wrong in exporting ${type} data.`,
                type: "error"
            })
        }
    },
    mounted() {
        this.getData(this.course_id)
        
    },
    watch: {
        course_id(val){
            this.search = ''

            this.pageCount = 1
            this.page = 1

            if(!val) {
                this.getData(this.course_id)
            }
            
        },

        selected(val) {
            this.$emit('changeGraphData', { filter: val, course_id: this.course_id})
        },

        menu(val) {
            if(!val) {
                this.page = 1
                this.course_id = null
                this.getData(this.course_id)
            }
        }
    },
    computed: {

        itemsPerPage(){
            return Number(this.paginate)
        }
    }
}
</script>