<template>
    <section>
        <section class="d-flex flex-wrap justify-space-between">
            <SearchBar 
                :placeholder="'Search course'" 
                :value.sync="search" 
                @clear="clearSearch"
                @search="onSearch"
                class="mb-2"/>
            
            <section class=" d-flex flex-row align-center ml-auto ">
                <FilterMenu
                    :initialFilter="filter"
                    @resetFilters="resetFilters"
                    @applyFilters="() => { page = 1, updateQuery() }"
                    >
                    <v-sheet max-height="300" class="overflow-y-auto scroller">
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
                            <FormLabel class="mb-1" :label="'COURSE CREATION DATE (YYYY-MM-DD)'" />
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
                <ButtonExport 
                    @click="exportCourses"
                    :loading="exportLoading"
                    :disabled="loading || exportLoading || enrollees.length === 0"
                    class="ma-1"/>
            </section>
        </section>
        <section class="mb-3 text-right">
            <FormLabel :label="`${totalCount} result/s`" v-if="totalCount !== 0"/>
            <FormLabel :label="`-`" v-else/>
        </section>
        <v-data-table
            :loading="loading"
            :headers="courseEnrollment"
            :items="enrollees"
            hide-default-footer
            :items-per-page.sync="itemsPerPage"
            class="text--center custom-border poppins f14">
            <template v-slot:item.status="{ item }" >
                <span v-if="item">{{ item.status }}ED</span>
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
            <template v-slot:item.uploader="{ item }" >
                {{ item.uploader.uploader_name }}
            </template>
            <template v-slot:item.action="{ item }" >
                <v-btn dense icon color="primary" @click="$router.push({ name: 'Admin User Enrollment', params: { course_uuid: item.uuid }, query : { search: '', page: 1, paginate: 10, date_from: '', date_to: '', enrolled_by: '', mode_of_payment: '', type_of_payment: '', invoice_status: '', enrolled_by: '', enrollment_status: '', receipt_pick_up_option: '' }})">
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
import { courseEnrollment } from '@/constants/tblheaders/enrollment';
import searchAndPaginateMixin from '@/mixins/searchAndPaginateMixin';

export default {
    mixins: [searchAndPaginateMixin],
    data: () => ({
        loading: false,
        exportLoading: false,
        courseEnrollment,
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
        ...mapActions('admin', ['getEnrolleeCountAction', 'getEnrolleeCountExportAction']),
        ...mapMutations(['alertMutation']),
        ...mapMutations('admin', ['enrolleesMutation']),

        resetFilters(){
            this.filter = {
                status: '',
                type_of_learning: '',
                created_from: `${new Date().getFullYear()}-01-01`,
                created_to: `${new Date().toISOString().substr(0, 10)}`
            }

            this.minDate = this.filter.created_from
            this.maxDate = this.filter.created_to
            this.page = 1
            this.updateQuery()
        },
    
        getData(){
            if(!this.loading) {
                this.loading = true

                this.getEnrolleeCountAction({ ...this.filter, paginate: Number(this.paginate), page: this.page, search: this.search }).then(res => {
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
            if(!this.exportLoading) {
                this.exportLoading = true
                this.getEnrolleeCountExportAction({ ...this.filter, search: this.search }).then(res => {
                    this.exportLoading = false
                    this.alertMutation({
                        show: true,
                        text: 'Export is being processed in the background.',
                        type: "success"
                    })
                }).catch(e => {
                    this.exportLoading = false
                    this.alertMutation({
                        show: true,
                        text: 'Something went wrong.',
                        type: "error"
                    })
                })
            }
        }
    },
    computed: {
        ...mapState('admin', {
            enrollees: (state) => state.enrollees
        }),

        itemsPerPage(){
            return Number(this.paginate)
        },

        paginationPaginate(){
            return String(this.paginate)
        }
    },
    created() {
        this.enrolleesMutation([])
    
        this.minDate = this.filter.created_from
        this.maxDate = this.filter.created_to

        this.getData()
    },
}
</script>
