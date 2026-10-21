<template>
    <v-container class="primary-font f20 fw600 d-flex flex-column mb-10 mt-3">
        
        <div class="f16">Course Offerings</div>
        <div class="mb-3">
            <span class="l-primary--text">Free!</span> Trainings
        </div>
        <SearchBar
            :placeholder="'Search course'" 
            :value.sync="search" 
            :landing="true"
            :outlined="true"
            @search="getFreeCourses" 
            @clear="() => { search = '', getFreeCourses() }"
            class="mb-3 ml-auto"/>
        <LinearProgress v-if="loading" />
        <v-divider class="l-primary" width="50%" />
        <div v-if="free_courses.length === 0 && !loading" class="fw600 f14 secondary--text my-3"><i>No Courses Available</i></div>
        <!-- <section class="d-flex flex-wrap my-10" :class="$vuetify.breakpoint.smAndDown && 'justify-center'">
            <galleryCard v-for="(course, i) in filtered" :key="i" :course="course" class="ma-1"/>
        </section> -->

        <section v-if="free_courses.length>0" class="d-flex my-10" :class="[$vuetify.breakpoint.mobile ? 'flex-column justify-center' : 'flex-wrap']">
            <galleryCard v-for="(course, i) in free_courses" :key="i" :course="course" class="ma-1" v-if="!$vuetify.breakpoint.mobile"/>
            <listCard v-for="(course, i) in free_courses" :key="i" :course="course" class="ma-3" v-if="$vuetify.breakpoint.mobile"/>
        </section>
    </v-container>
</template>

<script>
import { mapActions, mapState, mapMutations } from "vuex";
import galleryCard from "@/components/landing/cards/gallery-card.vue";
import listCard from "@/components/landing/cards/list-card.vue"

export default {
    components: {
        galleryCard,
        listCard
    },
    mounted() {
        this.getFreeCourses()
    },
    data: () => ({
        search: '',
        loading: false
    }),
    computed: {
        ...mapState({
            free_courses: (state) => state.free_courses,
        }),
    },
    methods: {
        ...mapActions(['getTenantFreeCoursesAction']),

        ...mapMutations(['alertMutation']),

        getFreeCourses(){
            if(!this.loading){
                this.loading = true
                this.getTenantFreeCoursesAction({ search: this.search, status: "PUBLISH", available: 1 }).then(() => {
                    this.loading = false
                }).catch(() => {
                    this.loading = false
                    this.alertMutation({
                        show: true,
                        text: 'Something went wrong.',
                        type: "error"
                    })
                })
            }
        }
    }
}
</script>