<template>
    <div>
        <v-row class="mb-2">
            <v-col cols="4" md="4" sm="6" v-for="(item, i) in items" :key="i">
                <TopCard :item="item" :loading="cardLoading"/>
            </v-col>
        </v-row>
        <section class=" d-flex flex-row align-center ml-auto ">
            <FilterMenu
                :initialFilter="filter"
                @resetFilters="resetFilters"
                @applyFilters="() => { page = 1, updateQuery() }"
                :width="'500'"
                >
                <v-sheet max-height="300" class="overflow-y-auto scroller">
                    <section class="my-3">
                        <label class="caption">ACTIVITIES</label>
                        <v-select 
                            outlined 
                            dense 
                            :items="activities"
                            item-value="id"
                            item-text="text"
                            hide-details="auto"
                            multiple
                            v-model="filter.log_name"
                        >
                        </v-select>
                    </section>
                </v-sheet>
            </FilterMenu>
            <ButtonExport 
                @click="exportLogs"
                :loading="exportLoading"
                :disabled="loading || exportLoading || activitylogs.length === 0"
                class="ma-1"/>
        </section>
        <section v-if="totalCount !== 0" class="text-right">
            <FormLabel :label="`${totalCount} result/s`"/>
        </section>
        <v-data-table
            :headers="activity_log_tbl"
            :items="activitylogs"
            :loading="loading"
            class="elevation-1 poppins f10"
            hide-default-footer
            :items-per-page.sync="itemsPerPage"
            >
                <template v-slot:item.created_at="{ item }" class="text-capitalize">
                    {{$dateFormat.mmDDyy(item.created_at)}}
                </template>
                <template v-slot:item.desc="{ item }" class="text-capitalize">
                    <span v-if="item.log_name === 'user_enrollment' && item.target?.id === item.causer?.id">
                        {{ item.target?.full_name }} ({{ item.target?.email }}) enrolled in {{  item?.course?.title }}
                    </span>
                    <span v-if="item.log_name === 'user_enrollment' && item.target?.id !== item.causer?.id">
                        {{ item?.causer?.full_name}} ({{ item.causer?.email }}) enrolled {{ item.target?.full_name }} ({{ item.target?.email }}) in {{  item?.course?.title }}
                    </span>
                    <span v-if="item.log_name === 'user_certificate_gen'">
                        {{ item?.causer?.full_name}} generated a certificate in {{  item?.course?.title }}
                    </span>
                    <span v-if="item.log_name === 'user_course_activity'">
                        {{ item?.causer?.full_name}} ({{ item.causer?.email }}) continued the {{  item?.course?.title }} course
                    </span>
                    <span v-if="item.log_name === 'user_course_complete'">
                        {{ item?.causer?.full_name}} ({{ item.causer?.email }}) completed the {{  item?.course?.title }} course
                    </span>
                    <div v-if="item.log_name === 'advisory_update'" class="my-5">
                        <span v-if="item.event === 'created'">
                            {{ item?.causer?.full_name}} ({{ item.causer?.email }}) uploaded an advisory
                        </span>
                        <span v-if="item.event === 'deleted'">
                            {{ item?.causer?.full_name}} ({{ item.causer?.email }}) deleted an advisory
                        </span>

                        <PhotoCard :image="item.advisory.image" class="ma-1" :trash="false"/>
                    </div>
                    <span v-if="item.log_name === 'user_deletion'">
                        {{ item?.causer?.full_name}} deleted {{  item.target?.full_name }} ({{ item.target?.email }}) account
                    </span>
                    <span v-if="item.log_name === 'course_checkout'">
                        {{ item?.causer?.full_name}} ({{ item.causer?.email }}) checked out {{  item?.course?.title }}

                        <div class="mt-3 text-uppercase">
                            Reference Id: {{ item.properties.invoice_data.reference_id }}
                        </div>
                        <div class="text-uppercase">
                            Type of Payment: {{ item.properties.invoice_data.type_of_payment ? item.properties.invoice_data.type_of_payment.replaceAll('_', ' ') : '' }}
                        </div>
                    </span>
                    <span v-if="item.log_name === 'user_transaction' && item.event === 'update' && (item?.causer?.id === item.target?.id)">
                        {{  item.target?.full_name }} ({{ item.target?.email }}) transaction was set as {{ payment_status.find(status => status.value === item.properties.status ).text }} for the course {{  item?.course?.title }}
                    </span>
                    <span v-else-if="item.log_name === 'user_transaction' && item.event === 'update'">
                        {{ item?.causer?.full_name}} set {{  item.target?.full_name }} ({{ item.target?.email }}) transaction as {{ payment_status.find(status => status.value === item.properties.status ).text }} for the course {{  item?.course?.title }}
                    </span>
                    <span v-else-if="item.log_name === 'user_transaction' && item.event === 'created'">
                        {{ item?.causer?.full_name}} ({{ item.causer?.email }}) initiated a transaction for the course {{  item?.course?.title }}
                    </span>
                </template>
            </v-data-table>
            <FormPagination 
                :pageCount="pageCount" 
                :page="page"
                :paginate="paginationPaginate"
                @page="(e) => { page = e, getData() }" 
                @paginate="(e) => { paginate = e, page = 1, getData() }"/>
    </div>
</template>

<script>
import searchAndPaginateMixin from '@/mixins/searchAndPaginateMixin';
import { activity_log_tbl } from '@/constants/tblheaders/users';
import { mapActions, mapMutations, mapState } from 'vuex';
import TopCard from '@/components/reusable/TopCard.vue';
import PhotoCard from '@/components/reusable/cards/PhotoCard.vue';
import { payment_status } from '@/constants/payment';

export default {
    components: {
        TopCard,
        PhotoCard
    },
    mixins: [searchAndPaginateMixin],
    data: () => ({
        activity_log_tbl,
        loading: false,
        filterLoading: false,
        cardLoading: false,
        exportLoading: false,
        pageCount: 1,
        totalCount: 0,
        prevRoute: null,
        otherFilters: [],
        filter: {
            log_name: ['']
        },
        activities: [
            { id: "user_enrollment", text: "User Enrolled" },
            { id: "user_deletion", text: "User Deletion" },
            { id: "user_course_activity", text: "Course Accessed" },
            { id: "user_certificate_gen", text: "Certificate Generated" },
            { id: "user_course_complete", text: "Course Completed" },
            { id: "advisory_update", text: "Advisory Update" },
            { id: "course_checkout", text: "Course Checked Out" },
            { id: "user_transaction", text: "User Transaction" },
        ],
        items: [
            {
                title: 'Total Completions',
                count: '-',
                color: '#2A338F33', 
                outline: '#2A338F'
            },
            {
                title: 'Total Certificates Generated',
                count: '-',
                color: '#EC1C2D33',
                outline: '#EC1C2D'
            }
        ],
        payment_status
    }),

    mounted() {
      this.getStatsData()  
    },
    
    computed: {
        ...mapState('admin', {
            activitylogs: (state) => state.activitylogs
        }),

        paginationPaginate(){
            return String(this.paginate)
        },

        itemsPerPage(){
            return Number(this.paginate)
        },
    },

    methods: {
        ...mapMutations(['alertMutation']),
        ...mapActions('admin', ['getActivityLogsAction', 'getActivityLogsStatsAction', 'exportActivityLogsAction']),

        getStatsData(){
            this.cardLoading = true
            this.getActivityLogsStatsAction().then(res => {
                if(res) {
                    this.items = [
                        {
                            title: 'Total Completions',
                            count: res.user_course_complete_count,
                            color: '#2A338F33', 
                            outline: '#2A338F'
                        },
                        {
                            title: 'Total Certificates Generated',
                            count: res.user_certificate_gen_count,
                            color: '#EC1C2D33',
                            outline: '#EC1C2D'
                        }
                    ]
                }
            }).finally(() => {
                this.cardLoading = false
            })
        },

        getData(){
            this.loading = true
            this.getActivityLogsAction({
                page: this.page, 
                paginate: Number(this.paginate),
                search: this.search,
                ...this.filter
            }).then((res) => {
                this.loading = false
                this.pageCount = res.last_page
                this.totalCount = res.total
            }).catch(() => {
                this.loading = false
                this.pageCount = 0
                this.totalCount = 0
                this.alertMutation({
                    show: true,
                    text: 'Something went wrong',
                    type: "error"
                })
            })
        },
  
        beforeRouteEnter(to, from, next) {
            next(vm => {
                vm.prevRoute = from 
            })
        },

        resetFilters(){
            this.filter.log_name = ['user_enrollment', 'user_course_activity', 'user_certificate_gen', 'user_course_complete', 'advisory_update', 'course_checkout', 'user_transaction']
        },

        exportLogs() {
            this.exportLoading = true
            this.exportActivityLogsAction(this.filter).then(() => {
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
                    text: 'Something went wrong in exporting',
                    type: "error"
                })
            })
        },
    }
}
</script>