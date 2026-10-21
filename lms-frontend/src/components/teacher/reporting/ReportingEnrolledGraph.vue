<template>
    <section>
        <v-row dense>
            <v-col
                cols="12"
                sm="11"
                class="my-3"
            >
                <h3 class="poppins secondary-1--text fade">
                    Total Enrollees
                </h3>
                <FormLabel v-if="filter.enrolled_from || filter.enrolled_to" :label="`Enrollees ${filter.enrolled_from ? `from ${$dateFormat.mmDDyy(filter.enrolled_from)}` : ''} ${filter.enrolled_to ? `to ${$dateFormat.mmDDyy(filter.enrolled_to)}` : ''}`" class="mb-3"/>
            </v-col>
            <v-col
                cols="12"
                sm="1"
                class="d-flex flex-row justify-end"
            >
                <FilterMenu
                    :initialFilter="filter"
                    @resetFilters="clearFilters"
                    @applyFilters="getBarGraphData"
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
                        
                        <div>
                            <FormLabel class="mb-1" :label="'ENROLLMENT DATE (YYYY-MM-DD)'" />
                        </div>
                        <label class="caption">FROM:</label>
                        <FormDatePicker 
                            :val="filter.enrolled_from"
                            :max="maxDate2"
                            class_="col-11"
                            @apply="(e) => {
                                filter.enrolled_from = e
                                minDate2 = e
                            }"/>

                        <label class="caption">TO:</label>
                        <FormDatePicker 
                            :val="filter.enrolled_to"
                            :min="minDate2"
                            class_="col-11"
                            @apply="(e) => {
                                filter.enrolled_to = e
                                maxDate2 = e
                            }"/>`
                    </v-sheet>
                </FilterMenu>
            </v-col>
        </v-row>
        <v-skeleton-loader
            v-if="loading"
            :height="500"
            type="image"
            class="mb-1"
        ></v-skeleton-loader>
        <v-card  v-else class="custom-border border pa-5 mb-5">
            <ApexHorizontalBarGraph :series="chartData" :filename="'Enrollments'" :loading="loading"/>
        </v-card>
    </section>
</template>

<script>
import { mapActions } from 'vuex';
import ApexHorizontalBarGraph from "@/components/reusable/graphs/ApexHorizontalBarGraph.vue"

export default ({
    components: {
        ApexHorizontalBarGraph
    },
    
    data: () => ({
        loading: false,
        menu: false,
        filter: {
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
        minDate: '',
        maxDate: '',
        minDate2: '',
        maxDate2: '',
        dates: [],
        chartData: [{
            name: 'Enrollee Count',
            data: []
        }],
    }),

    mounted(){
        this.dates = ['', '']
        this.getBarGraphData()
    },

    methods: {
        ...mapActions('instructor', ['getInstructorAllCoursesReportingStatsAction']),

        clearFilters(){
            this.filter = {
                status: '',
                type_of_learning: '',
                date_from: ``,
                date_to: ``,
                enrolled_from: '',
                enrolled_to: ''
            }

            this.minDate = ''
            this.maxDate = ''
            this.minDate2 = ''
            this.maxDate2 = ''

            this.getBarGraphData()
        },

        getBarGraphData(){
            if(!this.loading) {
                this.loading = true
                this.getInstructorAllCoursesReportingStatsAction({ date_from: this.dates[0], date_to: this.dates[1] ? this.dates[1] : this.dates[0], ...this.filter }).then(res => {
                    let courses = {}
                    let _data = []
                    res.forEach(item => {
                        courses[item.title ? item.title : 'Untitled'] = item.enrollments_count

                        let title_split = []
                        item.title.split(' ').forEach((word, index) => {
                            if (index === 0) {
                                title_split.push(word);
                            } else {
                                let prev = title_split[title_split.length - 1].length;
                                let curr = word.length;
                                
                                if ((prev + curr + 1) <= 20) {
                                    title_split[title_split.length - 1] = `${title_split[title_split.length - 1]} ${word}`;
                                } else {
                                    title_split.push(word);
                                }
                            }
                        });

                        _data.push({
                            x: title_split,
                            y: item.enrollments_count,
                            enrollees: item.enrollments_count,
                            status: item.status + 'ED',
                            type_of_learning: item.type_of_learning,
                            availability_dates: `${(item.available_until?.start_date) ? `from ${this.$dateFormat.mmDDyy(item.available_until.start_date)} ` : ''}${item.available_until?.end_date ? `until ${this.$dateFormat.mmDDyy(item.available_until.end_date)}` : ''}`
                        })

                        this.chartData = [{
                            name: 'Enrollee Count',
                            data: [..._data]
                        }]
                    })
                    this.loading = false
                }).catch(() => {
                    this.loading = false
                })
            }
        },
    },
    
    watch: {
        menu(val) {
            if(!val) {
                this.getBarGraphData()
            }
        }
    }
    
})

</script>