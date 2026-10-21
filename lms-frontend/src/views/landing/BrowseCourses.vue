<template>
    <v-container class="l-primary-font f20 fw600 d-flex flex-column mb-10 mt-3 fade">
        <h3>
            Other Course Offerings
        </h3>
        <v-divider class="l-primary my-3 " :width="!$vuetify.breakpoint.mobile ? '50%' : '100%'"/>
        <div class="d-flex flex-wrap justify-space-between">
            <SearchBar 
                :outlined="true" 
                :placeholder="'Search course'" 
                :class="'mb-3'"
                :disabled="loadingDates || loadingCourses" 
                :value.sync="search" 
                :landing="true"
                @search="() =>{ page = 1, updateQuery() }" 
                @clear="() => { search = '', page = 1, updateQuery() }"/>

            <FilterMenu
                :initialFilter="filter"
                @resetFilters="clearFilters"
                :class="'mb-3'"
                @applyFilters="() => { page = 1, updateQuery(), getData() }"
            >
                <v-sheet max-height="350" class="overflow-y-auto scroller">
                    <FormSelectObject
                        class="mt-3"
                        :label="'TYPE OF LEARNING'"
                        :items="type_of_learning"
                        :value.sync="filter.type_of_learning"
                    />
                    <FormSelectObject
                        class="mt-3"
                        :label="'TRAINING CREDITS'"
                        :items="training_credits"
                        :value.sync="filter.training_credits"
                    />
                </v-sheet>
            </FilterMenu>
        </div>

        <section class="d-flex flex-wrap" v-if="loadingDates">
            <v-skeleton-loader
                v-for="item in [1, 2, 3]"
                :key="item"
                width=70
                height=20
                type="image"
                class="my-1 border mr-2"
            ></v-skeleton-loader>
        </section>

        <v-chip-group v-if="!loadingDates" @change="selectedDateChanged" column v-model="filter.date" active-class="l-primary white--text">
            <v-chip dense value="all" class="fade f16 fw500" :class="filter.date !== 'all' && 'secondary-2--text'">ALL</v-chip>
            <v-chip v-for="item in dates" :key="item" dense :value="item" class="fade f16 fw500" :class="filter.date !== item && 'secondary-2--text'">{{ $dateFormat.MMYY(item) }}</v-chip>
        </v-chip-group>

        <LinearProgress v-if="loadingCourses" :landing="true" class="mt-3 mb-1"/>

        <div v-if="other_courses.length === 0 && !loadingCourses && !loadingDates && search === '' && filter.date === 'all'" class="fw600 f14 secondary--text my-3"><i>No Courses Available this time. Come back soon!</i></div>

        <div v-if="other_courses.length === 0 && !loadingCourses && !loadingDates" class="fw600 f14 secondary--text my-3"><i>No Courses Found.</i></div>

        <div v-if="other_courses.length>0 && !loadingCourses" class="d-flex flex-wrap mt-3 fade" :class="[$vuetify.breakpoint.mobile && 'justify-center']">
            <galleryCard 
                v-if="!$vuetify.breakpoint.mobile"
                v-for="course in other_courses"
                :key="course.id"
                :course="course" 
                class="ma-2" />
            <listCard 
                v-if="$vuetify.breakpoint.mobile"
                v-for="course in other_courses"
                :key="course.id"
                :course="course" 
                class="ma-2" />
        </div>

        <FormPagination 
            :wrap="false"
            :pageCount="pageCount" 
            :page="page"
            :paginate="paginationPaginate"
            @page="onPageChange" 
            @paginate="onPaginateChange"/>
        
    </v-container>
</template>

<script>
import { mapActions, mapState, mapMutations } from "vuex";
import galleryCard from "@/components/landing/cards/gallery-card.vue";
import listCard from "@/components/landing/cards/list-card.vue"
import searchAndPaginateMixin from '@/mixins/searchAndPaginateMixin';
import { type_of_learning } from "@/constants/typeoflearning";

export default {
    mixins: [searchAndPaginateMixin],
    data:() => ({
        search: '',
        loadingCourses: false,
        loadingDates: false,
        loadingTrainingCredits: false,
        pageCount: 1,
        dates: [],
        training_credits: [
            { text: 'All', value: null }
        ],
        type_of_learning,
        filter: {
            date: null,
            type_of_learning: '',
            training_credits: null
        }
    }),
    components: {
        galleryCard,
        listCard
    },
    mounted() {
        this.getDateFilters()
        this.otherCoursesMutation([])
        this.loadingTrainingCredits = false,
        this.getTrainingCreditsAction().then(res => {
            let training_credits = res.map(item => ({
                value: item.id,
                text: item.name
            }));

            this.training_credits.push(...training_credits)
        }).finally(() => {
            this.loadingTrainingCredits = false
        })
    },
    computed: {
        ...mapState({
            other_courses: (state) =>  state.other_courses,
        }),

        paginationPaginate(){
            return String(this.paginate)
        }
    },
    methods: {
        ...mapActions(['getCourseAvailabilityDatesAction', 'getGroupedOtherCoursesAction', 'getTrainingCreditsAction']),
        ...mapMutations(['otherCoursesMutation']),

        clearFilters(){
            this.filter = {
                date: null,
                type_of_learning: '',
                training_credits: null
            }
            this.page =  1
            this.getData()
        },

        getDateFilters(){
            if(!this.loadingDates){
                this.dates = []
                this.loadingDates = true
                this.getCourseAvailabilityDatesAction().then((res) => {
                    this.dates = res
                    this.loadingDates = false
                }).catch(() => {
                    this.loadingDates = false
                })
            }
        },

        getData(){
            if(!this.loadingCourses && !this.loadingDates){
                this.loadingCourses = true
                this.getGroupedOtherCoursesAction({ search: this.search, ...this.filter, paginate: Number(this.paginate), page: this.page }).then(res => {
                    this.loadingCourses = false
                    this.paginate = String(res.per_page)
                    this.page = res.current_page
                    this.pageCount = res.last_page
                    this.totalCount = res.total
                }).catch(() => {
                    this.loadingCourses = false
                })
            }
        },

        selectedDateChanged(val){
            this.page = 1
            this.filter.date = val
            this.updateQuery()
        }
    },
}
</script>