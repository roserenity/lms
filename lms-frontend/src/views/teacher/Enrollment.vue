    <template>
    <section>
        <section class="d-flex flex-wrap justify-space-between">
            <SearchBar 
                :placeholder="'Search course'" 
                :value.sync="search" 
                @clear="clearSearch"
                @search="onSearch"
                class="mb-2"/>

            <section class="mb-3 ml-auto">
                <FilterMenu
                    :initialFilter="filter"
                    @resetFilters="resetFilters"
                    @applyFilters="() => { page = 1, updateQuery(), getData() }"
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
                        <div class="mt-3">
                            <FormLabel class="mb-1" :label="'COURSE CONDUCTED DATE (YYYY-MM-DD)'" />
                        </div>
                        <label class="caption">FROM:</label>
                        <FormDatePicker 
                            :val="filter.date_from"
                            :max="maxDate"
                            class_="col-11"
                            @apply="(e) => {
                                filter.date_from = e
                                minDate = e
                            }"/>

                        <label class="caption">TO:</label>
                        <FormDatePicker 
                            :val="filter.date_to"
                            :min="minDate"
                            class_="col-11"
                            @apply="(e) => {
                                filter.date_to = e
                                maxDate = e
                            }"/>`
                    </v-sheet>
                </FilterMenu>
                <ButtonExport 
                    @click="exportCourses"
                    :loading="exportLoading"
                    :disabled="loading || enrollees.length === 0"
                    class="ma-1 ml-auto"/>
            </section>
        </section>

        <section class="mb-1 d-flex flex-row justify-space-between">                
            <FormLabel v-if="filter.date_from || filter.date_to" :label="`Courses conducted ${filter.date_from ? `from ${$dateFormat.mmDDyy(filter.date_from)}` : ''} ${filter.date_to ? `to ${$dateFormat.mmDDyy(filter.date_to)}` : ''}`" class="mb-3"/>
            <FormLabel class="ml-auto" :label="`${totalCount} result/s`" v-if="totalCount !== 0"/>
        </section>

        <v-data-table
            :loading="loading"
            :headers="instructorCourseEnrollment"
            :items="enrollees"
            hide-default-footer
            :items-per-page.sync="itemsPerPage"
            class="text--center custom-border poppins f14">
            <template v-slot:item.start="{ item }">
                <span v-if="item">{{ item.available_until?.start_date ? $dateFormat.mmDDyy(item.available_until.start_date) : '' }}</span>
            </template>
            <template v-slot:item.end="{ item }">
                <span v-if="item">{{ item.available_until?.end_date ? $dateFormat.mmDDyy(item.available_until.end_date) : '' }}</span>
            </template>

            <template v-slot:item.status="{ item }">
                <span v-if="item">{{ item.status }}ED</span>
            </template>

            <template v-slot:item.action="{ item }" >
                <v-btn dense icon color="primary" @click="$router.push({ name: 'Instructor User Enrollment', params: { course_uuid: item.uuid }, query : { invoice_status: '', type_of_payment: '', date_from: '', date_to: ''}})">
                    <v-icon small>
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
    import { mapActions, mapState, mapMutations } from 'vuex';
    import searchAndPaginateMixin from '@/mixins/searchAndPaginateMixin';
    import { instructorCourseEnrollment } from '@/constants/tblheaders/enrollment';

    export default {
    mixins: [searchAndPaginateMixin],
    data: () => ({
        loading: false,
        exportLoading: false,
        instructorCourseEnrollment,
        totalCount: 0,
        pageCount: 1,
        minDate: '',
        maxDate: '',
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
        ...mapActions('instructor', ['getEnrolleeCountAction', 'getEnrolleeCountExportAction']),
        ...mapMutations(['alertMutation']),

        getData(){
            if(!this.loading) {
                this.loading = true

                this.getEnrolleeCountAction({ paginate: Number(this.paginate), page: this.page, search: this.search, ...this.filter}).then(res => {
                    this.loading = false
                    this.totalCount = res.total
                    this.pageCount = res.last_page
                    this.page = res.current_page
                    this.paginate = String(res.per_page)
                }).catch(e => {
                    this.loading = false
                    this.alertMutation({
                        show: true,
                        text: 'Something went wrong.',
                        type: "error"
                    })
                })

            }
        },

        exportCourses(){
            this.exportLoading = true
            this.getEnrolleeCountExportAction({ search: this.search, ...this.filter }).then(res => {
                this.exportLoading = false
                this.alertMutation({
                    show: true,
                    text: 'Export is being processed in the background.',
                    type: "success"
                })
            }).catch(e => {
                console.log(e)
                this.exportLoading = false
                this.alertMutation({
                    show: true,
                    text: 'Something went wrong.',
                    type: "error"
                })
            })
        },

        resetFilters(){
            this.filter = {
                status: '',
                type_of_learning: '',
                date_from: '',
                date_to: ''
            }

            this.minDate = this.filter.date_from
            this.maxDate = this.filter.date_to
            this.page = 1
            this.getData()
        },
    },
    computed: {
        ...mapState('instructor', {
            enrollees: (state) => state.enrollees
        }),

        itemsPerPage(){
            return Number(this.paginate)
        },

        paginationPaginate(){
            return String(this.paginate)
        }
    },
    }
    </script>
