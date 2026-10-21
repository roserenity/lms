<template>
    <section>
        <Breadcrumb
            :links="[
            {
                label: `<  ${course_title} `,
                link: true,
                route: (prevRoute && prevRoute.name === 'Instructor Grading Course') ? { name: 'Instructor Grading Course' , params: { id: $route.params.course_id }, query: { ...prevRoute.query}} : { name: 'Instructor Grading Course' , params: { id: $route.params.course_id }, query: { search: '', page: 1, paginate: 10 }}
            },
            {
                label: `${assessment_title}`,
                link: false,
                route: null,
            },
            ]"
        />
        
        <v-row no-gutters dense>
            <v-col cols="12" md="9">
                <Tabs :value.sync="tab" :tabs="grading_assessment_summary_tabs"/>
            </v-col>
            <v-col cols="12" md="3" class="d-flex flex-row align-center">
                <FilterMenu
                        :initialFilter="filter"
                        @resetFilters="resetFilters()"
                        @applyFilters="() => {
                            $refs.assessmentIndividualRef.page = 1,
                            $refs.assessmentIndividualRef.getData()
                        }"
                    >
                    <section>
                        <div>
                            <FormLabel class="mb-1" :label="'SUBMISSION DATE (YYYY-MM-DD)'" />
                        </div>
                        <label class="caption">FROM:</label>
                        <FormDatePicker 
                            :val="filter.submitted_from"
                            :max="maxDate"
                            class_="col-12 mb-3"
                            @apply="(e) => {
                                filter.submitted_from = e
                                minDate = e
                            }"/>
                        <label class="caption">TO:</label>
                        <FormDatePicker 
                            :val="filter.submitted_to"
                            :min="minDate"
                            class_="col-12 mb-3"
                            @apply="(e) => {
                                filter.submitted_to = e
                                maxDate = e
                            }"/>
                    </section>
                </FilterMenu>
                <ButtonExport
                    :loading="exportLoading"
                    class="my-3"
                    @click="() =>{
                        tab === 0 ? exportList() : exportResponses()
                    }"
                    :disabled="loading || total === 0"
                />
            </v-col>
        </v-row>

        <v-tabs-items v-model="tab" style="background-color: transparent">
            <v-tab-item>
                <AssessmentIndividual ref="assessmentIndividualRef" 
                    class="mt-2" 
                    @setCourseTitle="(e) => course_title = e"
                    @setAssessmentTitle="(e) => assessment_title = e"
                    @setLoading="(e) => loading = e"
                    @setTotal="(e) => total = e"
                    :submitted_to="filter.submitted_to"
                    :submitted_from="filter.submitted_from"/>
            </v-tab-item>
            <v-tab-item>
                <AssessmentSummary
                    :submitted_to="filter.submitted_to"
                    :submitted_from="filter.submitted_from"/>
            </v-tab-item>
        </v-tabs-items>
    </section>
</template>

<script>
import { mapActions, mapMutations } from 'vuex'
import Breadcrumb from "@/layouts/teacher/InstructorBreadcrumbs.vue";
import { grading_assessment_summary_tabs } from '@/constants/tabs/assessment';
import AssessmentIndividual from "@/components/teacher/assessment/AssessmentIndividual"
import AssessmentSummary from "@/components/teacher/assessment/AssessmentSummary"

export default {
    components: {
        Breadcrumb,
        AssessmentIndividual,
        AssessmentSummary,
    },
    data: () => ({
        tab: 0,
        grading_assessment_summary_tabs,
        course_title: '',
        assessment_title: '',
        exportLoading: false,
        modelFrom: false,
        modelTo: false,
        filter: {
            submitted_from: '',
            submitted_to: ''
        },
        prevRoute: null,
        minDate: '',
        maxDate: '',
        loading: false,
        total: 0
    }),
    methods: {
        ...mapActions('instructor', ['exportAssessmentResponses', 'exportAssessmentList']),
        ...mapMutations(['alertMutation']),

        resetFilters(){
            this.filter = {
                submitted_from: '',
                submitted_to: ''
            }

            this.minDate = ''
            this.maxDate = ''
        },

        exportList(){
            this.exportLoading = true
            this.exportAssessmentList({ assessment_id: this.$route.params.id, ...this.filter, search: this.$route.query.search}).then(res => {
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
                    text: 'Something went wrong in exporting',
                    type: "error"
                })
            })
        },

        exportResponses(){
            this.exportLoading = true
            this.exportAssessmentResponses({ assessment_id: this.$route.params.id, ...this.filter}).then(res => {
                this.alertMutation({
                    show: true,
                    text: 'Export is being processed in the background.',
                    type: "success"
                })
                this.exportLoading = false
            }).catch(e => {
                this.exportLoading = false
                this.alertMutation({
                    show: true,
                    text: 'Something went wrong in exporting',
                    type: "error"
                })
            })
        }
    },

    beforeRouteEnter(to, from, next) {
        next(vm => {
            vm.prevRoute = from          
        })
    },
}

</script>