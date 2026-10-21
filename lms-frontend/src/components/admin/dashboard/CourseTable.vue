<template>
    <v-expand-transition>
        <section>
            <section class="d-flex flex-wrap justify-space-between">
                <SearchBar
                    v-if="selected === 'Total Users per Course'" 
                    :placeholder="'Search course'" 
                    :value.sync="search" 
                    @search="() => { page = 1, getContent() }" 
                    @clear="() => { search = '', page = 1, getContent() }"
                    class="mb-5"/>
                <section class="mb-3 ml-auto">
                    <FilterMenu
                        :initialFilter="filter"
                        @resetFilters="resetFilters"
                        @applyFilters="() => { page = 1, getContent() }"
                        >
                        <v-sheet max-height="350" class="overflow-y-auto scroller">
                            <FormSelectObject
                                :label="'STATUS'"
                                :items="status"
                                :value.sync="filter.status"
                            />
                            
                            <FormSelectObject
                                class="mt-3"
                                :label="'TYPE OF LEARNING'"
                                :items="type_of_learnings"
                                :value.sync="filter.type_of_learning"
                            />

                            <section  v-if="selected"> 
                                <div class="mt-3">
                                    <FormLabel class="mb-1" :label="'COURSE CONDUCTED DATE (YYYY-MM-DD)'" />
                                </div>
                                <label class="caption">FROM:</label>
                                <FormDatePicker 
                                    :val="filter.created_from"
                                    :max="maxDate"
                                    class_="col-11 mb-3"
                                    @apply="(e) => {
                                        filter.created_from = e
                                        minDate = e
                                    }"/>

                                <label class="caption">TO:</label>
                                <FormDatePicker 
                                    :val="filter.created_to"
                                    :min="minDate"
                                    class_="col-11"
                                    @apply="(e) => {
                                        filter.created_to = e
                                        maxDate = e
                                    }"/>`
                            </section>
                        </v-sheet>
                    </FilterMenu>
                    <ButtonExport 
                        @click="exportTable"
                        :loading="exportLoading"
                        :disabled="loading || courses.length === 0"
                        class="poppins"/>
                </section>
            </section>
            <section v-if="totalCount !== 0" class="mb-1 d-flex flex-row justify-space-between">
                <FormLabel v-if="selected === 'Total Users per Course' && (filter.created_from || filter.created_to)" :label="`Courses conducted ${filter.created_from ? `from ${$dateFormat.mmDDyy(filter.created_from)}` : ''} ${filter.created_to ? `to ${$dateFormat.mmDDyy(filter.created_to)}` : ''}`" class="mb-3"/>
                <FormLabel :label="`${totalCount} result/s`" class="ml-auto"/>
            </section>
            <v-data-table
                :headers="headers"
                :items="courses"
                :loading="loading"
                class="elevation-1 custom-border border poppins"
                :page.sync="page"
                :items-per-page.sync="itemsPerPage"
                hide-default-footer
                >
                <template v-slot:header.enrollments_count="{ header }" >
                        <div class="text-uppercase">{{ header.text }}</div>
                        <div class="f10">{{ header.subtext }}</div>
                </template>
                <template v-slot:header.completed_course_count="{ header }" >
                        <div class="text-uppercase">{{ header.text }}</div>
                        <div class="f10">{{ header.subtext }}</div>
                </template>
                <template v-slot:header.users_count="{ header }" >
                        <div class="text-uppercase">{{ header.text }}</div>
                        <div class="f10">{{ header.subtext }}</div>
                </template>
                <template v-slot:header.course_complete_count="{ header }" >
                        <div class="text-uppercase">{{ header.text }}</div>
                        <div class="f10">{{ header.subtext }}</div>
                </template>
                
                <template v-slot:item.created_at="{ item }" class="text-capitalize">
                    <span v-if="item">{{$dateFormat.mmDDyy(item.created_at)}}</span>
                </template>
                
                <template v-slot:item.start="{ item }">
                    <span v-if="item">{{ item.available_until?.start_date ? $dateFormat.mmDDyy(item.available_until.start_date) : '' }}</span>
                </template>

                <template v-slot:item.end="{ item }">
                    <span v-if="item">{{ item.available_until?.end_date ? $dateFormat.mmDDyy(item.available_until.end_date) : '' }}</span>
                </template>

                <template v-slot:item.status="{ item }">
                    <span v-if="item">{{ item.status }}ED</span>
                </template>

                <template v-slot:item.total_enrollments="{ item }">
                    <span @click="item.total_enrollments > 0 && 
                        $emit('viewTotalEnrolleeList', { 
                                filter: selected, 
                                value: item.id,
                                listName: item.title
                            })" 
                            text class="poppins text-wrap pa-2 text-right ma-0 primary--text cursor-pointer" 
                            :style="'letter-spacing: normal; justify-content: flex-start;'">
                            {{ item.total_enrollments }}
                    </span>
                </template>

                <template v-slot:item.users_count="{ item }">
                    <span @click="item.users_count > 0 && 
                        $emit('viewEnrolleeList', { 
                                filter: selected, 
                                value: item.listName,
                                listName: item.title
                            })" 
                            text class="poppins text-wrap pa-2 text-right ma-0 primary--text cursor-pointer" 
                            :style="'letter-spacing: normal; justify-content: flex-start;'">
                            {{ item.users_count }}
                    </span>
                </template>

                <template v-slot:item.course_complete_count="{ item }">
                    <span @click="item.course_complete_count > 0 && 
                        $emit('viewCompletersList', { 
                                filter: selected, 
                                value: item.listName,
                                listName: item.title
                            })" 
                            text class="poppins text-wrap pa-2 text-right ma-0 primary--text cursor-pointer" 
                            :style="'letter-spacing: normal; justify-content: flex-start;'">
                            {{ !item.hasOwnProperty('course_complete_count') ? '0' : item.course_complete_count }} <span class="secondary-2--text"> {{ (!item.hasOwnProperty('course_complete_count') || (item.course_complete_count === 0 || item.users_count === 0)) ? '( 0% )' : `(${((item.course_complete_count / item.users_count) * 100).toFixed(2)}%)` }} </span>
                    </span>
                </template>

                <template v-slot:item.enrollments_count="{ item }">
                    <span @click="item.enrollments_count > 0 && 
                        $emit('viewEnrolleeList', { 
                                filter: selected, 
                                value: item.id,
                                listName: item.title
                            })" 
                            text class="poppins text-wrap pa-2 text-right ma-0 primary--text cursor-pointer" 
                            :style="'letter-spacing: normal; justify-content: flex-start;'">
                            {{ item.enrollments_count }}
                    </span>
                </template>

                <template v-slot:item.completed_course_count="{ item }">
                    <span @click="item.completed_course_count > 0 && 
                        $emit('viewCompletersList', { 
                                filter: selected,
                                value: item.id,
                                listName: item.title
                            })"
                            text class="poppins text-wrap pa-2 text-right ma-0 primary--text cursor-pointer" :style="'letter-spacing: normal; justify-content: flex-start;'">
                            {{ !item.hasOwnProperty('completed_course_count') ? '0' : item.completed_course_count }} <span class="secondary-2--text"> {{ (!item.hasOwnProperty('completed_course_count') || (item.completed_course_count === 0 || item.enrollments_count === 0)) ? '( 0% )' : `( ${((item.completed_course_count / item.total_enrollments) * 100).toFixed(2)}% )` }} </span>
                    </span>
                </template>
            </v-data-table>
            <FormPagination 
                :pageCount="pageCount" 
                :page="page"
                :paginate="paginate"
                @page="(e) => {page = e, getContent()}" 
                @paginate="(e) => {paginate = e, page = 1, getContent()}"/>
        </section>
    </v-expand-transition>
</template>

<script>
import { mapActions, mapState, mapMutations } from 'vuex';
export default {
    props: {
        selected: {
            type: String,
            default: ""
        },
        dates: {
            type: Array,
            default: []
        },
        menu: {
            type: Boolean,
            default: false
        }
    },
    data: () => ({
        headers: [ 
            { text: "COURSE", value: "title"},
            { text: "CODE", value: "course_code"},
            { text: "STATUS", value: "status", align: 'center', sortable: false},
            { text: "TYPE OF LEARNING", value: "type_of_learning", width: "10%", align: 'center', sortable: false},
            { text: "DATE CREATED", value: "created_at", width: "10%", align: 'center', sortable: false},
            { text: "INSTRUCTOR", value: "uploader.full_name", align: 'center' },
            { text: "TOTAL NO. OF ENROLLEES", value: "total_enrollments", align: 'center', sortable: false },
            { text: "ENROLLEES", value: "enrollments_count", subtext: '', align: 'center', sortable: false },
            { text: "COMPLETERS", value: "completed_course_count",  subtext: '', align: 'center', sortable: false  },
        ],
        loading: false,
        exportLoading: false,
        totalCount: 0,
        pageCount: 1,
        paginate: '10',
        page: 1,
        search: '',
        minDate: '',
        maxDate: '',
        filter: {
            status: '',
            type_of_learning: '',
            created_from: ``,
            created_to: ``
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
        ]
    }),
    methods: {
        ...mapMutations(['alertMutation']),

        ...mapActions('admin', ['getCoursesUserCount', 'exportAdminAnalyticsAction']),

        resetFilters(){
            this.filter = {
                status: '',
                type_of_learning: '',
                created_from: ``,
                created_to: ``
            }

            this.minDate = this.filter.created_from
            this.maxDate = this.filter.created_to
            this.page = 1
            this.getContent()
        },

        getContent(){
            this.loading = true
            this.getCoursesUserCount({ 
                    filter: this.selected, 
                    search: this.search, 
                    paginate: Number(this.paginate), 
                    page: this.page, 
                    date_from: this.dates[0], 
                    date_to: this.dates[1],
                    ...this.filter
                }).then(res => {

                this.page = res.current_page
                this.paginate = String(res.per_page)
                this.pageCount = res.last_page
                this.totalCount = res.total

                if(this.selected !== 'Total Users per Course') {
                    this.headers = [ 
                        { text: this.selected, value: "title", sortable: false },
                        { text: "TOTAL NO. OF ENROLLEES", value: "total_enrollments", align: 'center', sortable: false },
                        { text: "ENROLLEES", value: "users_count", subtext: `(from ${this.$dateFormat.mmDDyy(this.dates[0])} to ${this.$dateFormat.mmDDyy(this.dates[1])})`, align: 'center', sortable: false },
                        { text: "COMPLETERS", value: "course_complete_count", subtext: `(from ${this.$dateFormat.mmDDyy(this.dates[0])} to ${this.$dateFormat.mmDDyy(this.dates[1])})`, align: 'center', sortable: false },
                    ]
                } else {
                    this.headers = [ 
                        { text: "COURSE", value: "title", sortable: false },
                        { text: "CODE", value: "course_code"},
                        { text: "STATUS", value: "status", align: 'center', sortable: false},
                        { text: "TYPE OF LEARNING", value: "type_of_learning", width: "10%", align: 'center', sortable: false},
                        { text: "DATE CONDUCTED START", value: "start", width: "120px", align: 'center', sortable: false},
                        { text: "DATE CONDUCTED END", value: "end", width: "120px", align: 'center', sortable: false},
                        { text: "INSTRUCTOR", value: "uploader.full_name", align: 'center', sortable: false },
                        { text: "TOTAL NO. OF ENROLLEES", value: "total_enrollments", align: 'center', sortable: false },
                        { text: "ENROLLEES", value: "enrollments_count", subtext: `(from ${this.$dateFormat.mmDDyy(this.dates[0])} to ${this.$dateFormat.mmDDyy(this.dates[1])})`, align: 'center', sortable: false },
                        { text: "COMPLETERS", value: "completed_course_count", subtext: `(from ${this.$dateFormat.mmDDyy(this.dates[0])} to ${this.$dateFormat.mmDDyy(this.dates[1])})`, align: 'center', sortable: false  },
                    
                    ]
                }
                this.loading = false
            }).catch(() => {
                this.loading = false
            })
        },

        exportTable() {
            this.exportLoading = true
            this.exportAdminAnalyticsAction({ 
                filter: this.selected, 
                search: this.search, 
                date_from: this.dates[0], 
                date_to: this.dates[1],
                ...this.filter
            }).then(res => {
                this.exportLoading = false
                this.alertMutation({
                    show: true,
                    text: 'Export is being processed in the background.',
                    type: "success"
                })
            }).catch(() => {
                this.exportLoading = false
                this.alertMutation({
                    show: true,
                    text: 'Something went wrong',
                    type: "error"
                })
            })
        }
    },
    created() {
        this.minDate = this.filter.created_from
        this.maxDate = this.filter.created_to

        this.getContent()
    },
    computed: {
        ...mapState('admin', {
            courses: (state) => state.courses
        }),

        itemsPerPage(){
            return Number(this.paginate)
        }
    },
    watch: {
        selected() {
            this.search = ''
            this.getContent()
        },

        menu(val) {
            if(!val) {
                this.getContent()
            }
        }
    }
}
</script>