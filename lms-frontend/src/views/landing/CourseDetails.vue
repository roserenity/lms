<template>
    <v-container class="pb-10 mt-3 fade">
        <v-progress-linear
            v-if="loading"
            indeterminate
            color="l-primary"
            rounded
            height="4"
            class="mx-auto"
        ></v-progress-linear>
        <section
            class="d-flex flex-column align-center justify-center mt-10 fade"
            v-if="error"
        >
            <v-sheet width="350" class="d-flex flex-column align-center justify-center">
                <v-img 
                    max-width="230" 
                    :src="require('@/assets/default/empty_published.png')" />
                <h3 class="text-center poppins secondary-1--text fw600 my-5 text-center">
                    Oops!
                </h3>
                <div class="text-center poppins secondary-1--text f12">
                    It seems like this course is no longer available for access. 
                    Please reach out to your instructor for more information or explore other available courses.
                </div>
            </v-sheet>
        </section>
        <section v-if="!error">
            <v-row no-gutters v-if="!loading" id="top">
                <v-col cols="12" lg="7" v-if="course">
                    <img 
                        :src="course.image ? course.image.url : require('@/assets/images/landing/background/course-photo.png')" 
                        v-on:error="require('@/assets/images/landing/background/course-photo.png')"
                        class="rounded-lg"
                        height="300"
                        width="100%"
                    />
                    <CourseDetails :course="course"/>
                </v-col>
                <v-spacer />
                <v-col cols="12" lg="4" v-if="course">
                    <CourseOutline :modules="course.modules" :loading="loading"/>
                </v-col>
            </v-row>


            <div class="primary-font f18 fw600 mt-5 mb-2 fade" v-if="courses.length > 0 && !loading">
                More Courses
                <v-slide-group
                    show-arrows
                >
                    <v-slide-item v-for="course in courses" :key="course.id">
                        <GalleryCards class="ma-1" :course="course" />
                    </v-slide-item>
                </v-slide-group>
            </div>
        </section>

    </v-container>
</template>

<style scoped>
.v-progress-linear {
    width: 180px !important;
    margin-top: 25vh !important;
}
</style>

<script>

import { mapActions, mapState } from "vuex"
import CourseDetails from '@/components/landing/courses/course-details.vue';
import GalleryCards from "@/components/landing/cards/gallery-card.vue";
import CourseOutline from "@/components/reusable/course/CourseOutline.vue"

export default {
    name: 'landing-home',
    components: {
        CourseDetails,
        GalleryCards,
        CourseOutline
    },
    data: () => ({
        loading: false,
        error: false
    }),
    mounted() {
        this.loading = true
        this.getTenantCourseAction(this.$route.params.uuid).then(() => {
            this.loading = false
        }).catch(() => {
            this.error = true
            this.loading = false
        })

        this.otherAllCoursesAction({})
    }, 
    computed: {
        ...mapState({
            course: (state) => state.course,
            courses: (state) => state.courses,
        }),
    },
    methods: {
        ...mapActions(['getTenantCourseAction', 'otherAllCoursesAction']),

        setAltImg(event) { 
            event.target.src = "../assets/images/background/course-photo.png" 
        } 
    },
    watch: {
        '$route'( val ){
            this.$vuetify.goTo('#top')
            this.loading = true
            this.getTenantCourseAction(val.params.uuid).then(() => {
                this.loading = false
            }).catch(() => {
                this.error = true
                this.loading = false
            })
        }
    }
}
</script>