<template>
    <section class="mx-auto">
        <section class="d-flex flex-wrap justify-space-between">
            <SearchBar 
                :placeholder="'Search course'"
                :value.sync="search" 
                class='mb-3' 
                @clear="clearSearch"
                @search="onSearch"/>
            <div class="d-flex flex-row align-center justify-space-between mb-3">
                <v-select
                    dense
                    solo
                    class="custom-border general-custom-field col-xl-9 roboto f13 secondary-1--text fw500 mr-1"
                    :items="classes"
                    item-value="class_code"
                    v-model="selectedClass"
                    hide-details
                    :loading="loadingClasses"
                    flat
                    @change="onClassChange"
                >
                    <template slot="item" slot-scope="data">
                        <v-list-item-title class="fw500"><span>{{ data.item.class_code }} - </span>
                            <span class="secondary-3--text">{{ data.item.class_name }}</span>
                        </v-list-item-title>
                    </template>
                    <template slot="selection" slot-scope="data">
                        <v-list-item-title class="fw500"><span>{{ data.item.class_code }} - </span>
                            <span class="secondary-3--text">{{ data.item.class_name }}</span>
                        </v-list-item-title>
                    </template>
                </v-select>
                <FilterMenu
                    class="mb-3"
                    :initialFilter="filter"
                    @resetFilters="() => {
                        page = 1
                        minDate = ''
                        maxDate = ''
                        resetFilters()
                    }"
                    @applyFilters="() => { page = 1, updateQuery() }"
                    >
                    <v-sheet max-height="300" class="overflow-y-auto scroller">
                    <div class="mt-3">
                        <FormLabel class="mb-1" :label="'TYPE OF LEARNING'" />
                        <FormSelectObject
                            :items="type_of_learning"
                            :value.sync="filter.type_of_learning"
                            :class="'mb-1'"
                        />
                    </div>
                    <FormSelectObject
                        :label="'STATUS'"
                        :items="status"
                        :value.sync="filter.status"
                    />
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
                        class_="col-11 mb-3"
                        @apply="(e) => {
                            filter.created_to = e
                            maxDate = e
                        }"/>
                    </v-sheet>
                </FilterMenu>
            </div>
        </section>
        
        <v-data-table
            :loading="loadingCourses"
            :headers="instructor_courses_tbl"
            :items="courses"
            :items-per-page.sync="itemsPerPage"
            class="elevation-1 poppins custom-border border"
            hide-default-footer
        >
            <template v-slot:item.status="{ item }">
                <span v-if="item">{{ item.status }}ED</span>
            </template>

            <template v-slot:item.start="{ item }">
                <span v-if="item">{{ item.available_until?.start_date ? $dateFormat.mmDDyy(item.available_until.start_date) : '' }}</span>
            </template>

            <template v-slot:item.end="{ item }">
                <span v-if="item">{{ item.available_until?.end_date ? $dateFormat.mmDDyy(item.available_until.end_date) : '' }}</span>
            </template>

            <template v-slot:item.action="{ item }">
                <v-btn icon dense color="primary" text class="f12" 
                    @click="$router.push({name: 'Instructor Student Outputs Enrollees', params: {course_uuid: item.uuid}, query: { search: '', page: 1, paginate: 10 }})">
                    <v-icon small class="mr-1" dense>
                        mdi-eye-outline
                    </v-icon>
                </v-btn>
            </template>
        </v-data-table>

        <FormPagination 
            :pageCount="pageCount" 
            :page="page"
            :paginate="paginationPaginate"
            @page="onPageChange" 
            @paginate="onPaginateChange"/>
    </section>
</template>

<script>
import { mapActions, mapMutations, mapState } from "vuex";
import searchAndPaginateMixin from '@/mixins/searchAndPaginateMixin';
import { instructor_courses_tbl } from "@/constants/tblheaders/studentprogress"
import { type_of_learning } from "@/constants/typeoflearning";

export default {
    mixins: [searchAndPaginateMixin],
    data: () => ({
        loadingClasses: false,
        loadingCourses: false,
        instructor_courses_tbl,
        selectedClass: '',
        pageCount: 1,
        type_of_learning,
        minDate: '',
        maxDate: '',
        status: [
            { text: 'ALL', value: '' },
            { text: 'PUBLISHED', value: 'PUBLISH' },
            { text: 'UNPUBLISHED', value: 'UNPUBLISH' },
        ],
    }),
    mounted(){
        this.selectedClass = this.$route.query.classes
        if(this.getClass(this.$route.query.classes) === -1) {
            this.selectedClass = ''
            this.filter = {}
        } else {
            this.getData()
        }

        
        this.minDate = this.filter.created_from
        this.maxDate = this.filter.created_to
    },
    computed: {
        ...mapState("instructor", {
            classes: (state) => {
                return [ {class_name: '', class_code: ''}, ...state.classes ]
            },
            courses: (state) =>  state.courses
        }),

        itemsPerPage(){
            return Number(this.paginate)
        },

        paginationPaginate(){
            return String(this.paginate)
        }
    },
    methods: {
        ...mapActions("instructor", ["getClassesAction", "getStudentOutputCoursesAction"]),
        ...mapMutations(["alertMutation"]),

        onClassChange(e) {
            this.filter = { classes: e };
            this.updateQuery();
        },
        
        getClass(class_code) {
            return this.classes.findIndex((item) => item.class_code === class_code)
        },
        
        getClasses(){
            if(!this.loadingClasses) {
                this.loadingClasses = true
                this.getClassesAction().then(() => {
                    this.loadingClasses = false
                }).catch(() => {
                    this.alertMutation({
                        show: true,
                        text: 'Something went wrong',
                        type: "error"
                    })
                    this.loadingClasses = false
                })
            }
        },

        getData(){
            if(!this.loadingCourses && !this.loadingClasses) {
                this.loadingCourses = true
                this.getStudentOutputCoursesAction({ paginate: Number(this.paginate), page: this.page, search: this.search, classes: this.$route.query.classes, ...this.filter}).then(res => {
                    this.page = res.current_page
                    this.pageCount = res.last_page
                    this.paginate = String(res.per_page)
                    this.loadingCourses = false
                }).catch(() => {
                    this.alertMutation({
                        show: true,
                        text: 'Something went wrong',
                        type: "error"
                    })
                    this.loadingCourses = false
                })
            }
        },

        resetFilters(){
            this.filter = {
                status: '',
                created_from: ``,
                created_to: ``,
                type_of_learning: ''
            }

            this.page = 1
            this.updateQuery()
        },

    },
  
    beforeRouteEnter(to, from, next) {
        next(vm => {
            vm.prevRoute = from       
            vm.getClasses();   
        })
    },
}
</script>