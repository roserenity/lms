<template>
    <section>
        <v-card class="custom-border border pa-7 mb-5">
            <section class="d-flex justify-end">
                <v-btn text @click="$emit('updateMode', { mode: 1, selected_course: null })" class=" f14 primary--text ml-auto">
                    <v-icon small>
                        mdi-chevron-left
                    </v-icon>
                    BACK
                </v-btn>
            </section>

            <h3 class="poppins secondary-1--text mb-3" v-if="course">
                {{ course.title }}
            </h3>

            
            <Tabs :value.sync="tab" :tabs="items"/>
            <v-tabs-items v-model="tab" style="background-color: transparent">
                <v-tab-item>
                    <section class="d-flex flex-wrap justify-space-between align-end mt-5">
                        <SearchBar :placeholder="'Search assessment'" @clear="() => { search = '', page = 1, getAssessmentData()}" :value.sync="search" @search="() => { page = 1, getAssessmentData() }" class="mb-3" :outlined="true" />
                        <ButtonExport 
                            @click="exportTable"
                            :loading="exportLoading"
                            :disabled="loading || table_items.length === 0"
                            class="poppins mb-3 ml-auto"/>
                    </section>
                    <section v-if="totalCount !== 0" class="text-right mb-3">
                        <FormLabel :label="`${totalCount} result/s`"/>
                    </section>
                    <v-data-table
                        :headers="instructor_reporting_assessments_tbl"
                        :items="table_items"
                        :loading="loading"
                        class="poppins"
                        :page.sync="page"
                        :items-per-page.sync="itemsPerPage"
                        hide-default-footer>

                        <template v-slot:item.passed_count="{ item }">
                            <span>{{ item.passed_count }} {{ calculatePercentage(item.passed_count, total_enrollment) }} </span>
                        </template>

                        
                        <template v-slot:item.failed_count="{ item }">
                            <span>{{ item.failed_count }} {{ calculatePercentage(item.failed_count, total_enrollment) }}</span>
                        </template>

                        <template v-slot:item.unanswered_count="{ item }">
                            <span>{{ total_enrollment - item.answered_count }}</span>
                        </template>
                    </v-data-table>
                    
                    <FormPagination 
                        :pageCount="pageCount" 
                        :page="page"
                        :paginate="paginate"
                        @page="(e) => {page = e, getAssessmentData()}" 
                        @paginate="(e) => {paginate = e, page = 1, getAssessmentData()}"/>

                </v-tab-item>
            </v-tabs-items>
        </v-card>
    </section>
</template>

<script>
import { mapActions, mapMutations } from 'vuex'
import { instructor_reporting_assessments_tbl } from "@/constants/tblheaders/reporting"

export default {
    props: {
        course: {
            type: Object,
            default: null
        },
        total_enrollment: {
            type: Number,
            default: 0
        }
    },

    data: () => ({
        tab: 0,
        items: ['ASSESSMENTS'],
        loading: false,
        exportLoading: false,
        totalCount: 0,
        pageCount: 1,
        paginate: '10',
        page: 1,
        search: '',
        table_items: [],
        instructor_reporting_assessments_tbl
    }),

    computed: {

        itemsPerPage(){
            return Number(this.paginate)
        },

    },

    methods: {
        ...mapActions('instructor', [
            'getInstructorAssessmentsReportingStats',
            'exportInstructorAssessmentsSummaryAction'
        ]),

        ...mapMutations(['alertMutation']),

        getAssessmentData(){
            if(!this.loading){
                this.loading = true
                this.getInstructorAssessmentsReportingStats({course_id: this.course.id, page: this.page, paginate: this.itemsPerPage, search: this.search}).then(res =>{
                    this.table_items = res.data
                    this.page = res.current_page
                    this.paginate = String(res.per_page)
                    this.pageCount = res.last_page
                    this.totalCount = res.total
                    this.loading = false
                }).catch(() => {
                    this.loading = false
                    this.alertMutation({
                        show: true,
                        text: 'Something went wrong',
                        type: "error"
                    })
                })
            }
        },

        calculatePercentage(part, total) {
            if (total !== 0) {
                return `(${((part / total) * 100).toFixed(2)}%)`;
            }
        },

        exportTable() {
            if(!this.exportLoading) {
                this.exportInstructorAssessmentsSummaryAction({ course_id: this.course.id, search: this.search }).then(res => {
                    this.exportLoading = false
                    this.$exportToCSV(`${this.course.title} - Assessments`, res)
                }).catch(() => {
                    this.exportLoading = false
                    this.alertMutation({
                        show: true,
                        text: 'Something went wrong in exporting',
                        type: "error"
                    })
                })
            }
        }
    },

    mounted(){
        this.getAssessmentData()
    }
}
</script>