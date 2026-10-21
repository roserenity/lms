<template>
    <section id="reporting">

        <v-row dense class="mb-3">
            <v-col cols="12" :md="4" v-for="(item, i) in items" :key="i">
                <TopCard :item="item" :loading="cardLoading"/>
            </v-col>
        </v-row>

        <section v-if="mode === 1">
            <ReportingEnrolledGraph />
            <ReportingTable @updateMode="(e) => updateMode(e)"/>
        </section>

        <section v-if="mode === 2">
            <ReportingCourseContent :course="selected_course" @updateMode="(e) => updateMode(e)" :total_enrollment="selected_course ? selected_course.enrollments_count : 0"/>
            <ReportingCourseGraph :course="selected_course" :total_enrollment="selected_course ? selected_course.enrollments_count : 0"/> 
        </section>

    </section>
</template>

<script>
import { mapActions } from 'vuex';
import TopCard from '@/components/reusable/TopCard.vue'
import ReportingEnrolledGraph from '@/components/teacher/reporting/ReportingEnrolledGraph.vue';
import ReportingTable from '@/components/teacher/reporting/ReportingTable.vue';
import ReportingCourseContent from '@/components/teacher/reporting/ReportingCourseContent.vue';
import ReportingCourseGraph from '@/components/teacher/reporting/ReportingCourseGraph.vue';

export default ({
    components: {
        TopCard,
        ReportingTable,
        ReportingEnrolledGraph,
        ReportingCourseContent,
        ReportingCourseGraph
    },
    data: () => ({
        items: [{
                id: 'total_enrollees_count',
                title: 'Total Enrollments',
                count: '-',
                color: '#2A338F33', 
                outline: '#2A338F'
            },{
                id: 'total_enrollees_unique_count',
                title: 'Total Unique Enrollees',
                count: '-',
                color: '#EC421C33', 
                outline: '#EC421C'
            },
            {
                id: 'courses_count',
                title: 'Total Courses Owned & Shared',
                count: '-',
                color: '#EC1C2D33',
                outline: '#EC1C2D'
            },
            {
                id: 'completers_count',
                title: 'Total Number of Completers',
                count: '-',
                color: '#EF3F1733',
                outline: '#EF3F17'
            },
            {
                id: 'completers_unique_count',
                title: 'Total Number of Unique Completers',
                count: '-',
                color: '#EF891733',
                outline: '#EF8917'
            },
            {
                id: 'certificates_count',
                title: 'Total Number of Certificates Generated',
                count: '-',
                color: '#525BBC33',
                outline: '#525BBC'
            },
        ],
        cardLoading: false,
        table_items: [],
        mode: 1,
        selected_course: null
    }),

    methods: {
        ...mapActions('instructor', ['getInstructorReportingStats']),

        updateMode(e) {
            let { mode, selected_course } = e
            this.mode = mode
            this.selected_course = selected_course
        },

        getCardData() {
            if(!this.cardLoading) {
                this.cardLoading = true
                this.getInstructorReportingStats({ course_id: this.selected_course ? this.selected_course.id : null }).then(res => {
                    for (const [key, value] of Object.entries(res)) {
                        this.items[this.items.findIndex(item  => item.id === key)].count = value
                    }
                    this.cardLoading = false
                }).catch(() => {
                    this.cardLoading = false
                })
            }
        },

    },

    mounted(){
        // stats card
        this.getCardData()
    },

    watch: {
        mode(val) {
            if(val === 1) {
                this.items = [{
                        id: 'total_enrolled_users_count',
                        title: 'Total Enrolled Users',
                        count: '-',
                        color: '#2A338F33', 
                        outline: '#2A338F'
                    },
                    {
                        id: 'courses_count',
                        title: 'Total Courses Owned & Shared',
                        count: '-',
                        color: '#EC1C2D33',
                        outline: '#EC1C2D'
                    },
                    {
                        id: 'completers_count',
                        title: 'Total Number of Completers',
                        count: '-',
                        color: '#EF891733',
                        outline: '#EF8917'
                    },
                ],
                this.getCardData()
            } else {
                this.items = [{
                        id: 'total_enrolled_users_count',
                        title: 'Total Enrolled Users',
                        count: '-',
                        color: '#2A338F33', 
                        outline: '#2A338F'
                    },
                    {
                        id: 'total_transactions_count',
                        title: 'Total No. of Transactions',
                        count: '-',
                        color: '#EC1C2D33',
                        outline: '#EC1C2D'
                    },
                    {
                        id: 'total_completers_count',
                        title: 'Total No. of Completers',
                        count: '-',
                        color: '#EF891733',
                        outline: '#EF8917'
                    },
                    {
                        id: 'total_certificates_count',
                        title: 'Total No. of Generated Certificates',
                        count: '-',
                        color: '#525BB733',
                        outline: '#525BB7'
                    },
                ],
                this.getCardData()
            }
        }
    }
})
</script>