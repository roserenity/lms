<template>
    <div>
        <SkeletonLoaderCard height="400" v-if="loading"/>
        <v-card class="custom-border border pb-1 fade" v-if="!loading">
            <v-card-title class="d-flex flex-row align-center">
                <v-icon color="primary" class="pr-2">mdi-book-check</v-icon>
                <div class="f18 primary-font secondary-1--text fw600">Completed Courses</div>
            </v-card-title>
            <v-divider></v-divider>
            <v-progress-linear
                indeterminate
                color="primary"
                rounded
                height="3px"
                v-if="loading"
            ></v-progress-linear>
            <v-sheet height="auto" max-height="50vh" class="d-flex overflow-y-auto scroller flex-wrap pa-5 fade">
                <CoursesCard v-for="(course, i) in completed_courses" :key="i" :course="course" class="ma-3"/>
            </v-sheet>
            <!-- <FormPagination 
                :pageCount="pageCount" 
                :page="page"
                :paginate="paginate"
                @page="(e) => {page = e, getCertificates()}" 
                @paginate="(e) => {paginate = e, getCertificates()}"/> -->
        </v-card>
    </div>
</template>


<script>
import { defineComponent } from '@vue/composition-api'
import CoursesCard from '@/components/user/profile/CoursesCard.vue'
import SkeletonLoaderCard from '@/components/reusable/SkeletonLoaderCard.vue'
import { mapActions, mapState } from 'vuex'

export default defineComponent({
    components: {
        CoursesCard,
        SkeletonLoaderCard
    },
    computed: {
        ...mapState('usr', {
            completed_courses: (state) => state.completed_courses
        })
    },
    data: () => ({
        pageCount: 1,
        page: 1,
        paginate: '10',
        loading: false
    }),
    methods: {
        ...mapActions('usr', ['getCompletedCoursesAction']),

        getCompletedCourses(){
            this.loading = true
            this.getCompletedCoursesAction({
                page: this.page,
                paginate: Number(this.paginate)
            }).finally(() => {
                this.loading = false
            })
        }
    },
    mounted(){
        this.getCompletedCourses()
    }
})
</script>
