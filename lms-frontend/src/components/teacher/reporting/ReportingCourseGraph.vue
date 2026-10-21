<template>
    <section v-if="course">
        <v-skeleton-loader
            v-if="loading"
            :height="500"
            type="image"
            class="mb-1"
        ></v-skeleton-loader>
        <v-card v-else class="custom-border border pa-5 mb-5 ">
            <ApexHorizontalStackedGraph :series="chartData" :filename="'Assessment Data'" :categories="categories"/>
        </v-card>
    </section>
</template>

<script>
import { mapActions, mapMutations } from 'vuex'
import { instructor_reporting_assessments_tbl } from "@/constants/tblheaders/reporting"
import ApexHorizontalStackedGraph from "@/components/reusable/graphs/ApexHorizontalStackedGraph.vue"

export default {
    components: {
        ApexHorizontalStackedGraph
    },

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
        loading: false,
        items: [],
        chartData: [{
            name: 'Assessment Data',
            data: []
        }],
        categories: []
    }),

    methods: {
        ...mapActions('instructor', [
            'getInstructorAssessmentsReportingStats',
        ]),

        ...mapMutations(['alertMutation']),

        getAssessmentData(){
            if(!this.loading){
                this.loading = true
                this.getInstructorAssessmentsReportingStats({course_id: this.course.id}).then(res =>{
                    let passed_count = []
                    let failed_count = []
                    let unanswered_count = []

                    res.forEach(item => {
                        let title_split = []
                        item.title.split(' ').forEach((word, index) => {
                            if (index === 0) {
                                title_split.push(word);
                            } else {
                                let prev = title_split[title_split.length - 1].length;
                                let curr = word.length;
                                
                                if ((prev + curr + 1) <= 32) {
                                    title_split[title_split.length - 1] = `${title_split[title_split.length - 1]} ${word}`;
                                } else {
                                    title_split.push(word);
                                }
                            }
                        });

                        this.categories.push(title_split)
                        passed_count.push(item.passed_count)
                        failed_count.push(item.failed_count)
                        unanswered_count.push(this.total_enrollment - item.answered_count)
                    })

                    this.chartData = [
                        {
                            name: 'PASSED',
                            data: passed_count,
                            color: '#EA8245'
                        },
                        {
                            name: 'FAILED',
                            data: failed_count,
                            color: '#3A3B71'
                        },
                        {
                            name: 'UNANSWERED',
                            data: unanswered_count,
                            color: '#D33148'
                        },
                    ]

                    this.loading = false
                }).catch((e) => {
                    this.loading = false
                    this.alertMutation({
                        show: true,
                        text: 'Something went wrong',
                        type: "error"
                    })
                })
            }
        },
    },

    mounted(){
        this.getAssessmentData()
    }
}
</script>