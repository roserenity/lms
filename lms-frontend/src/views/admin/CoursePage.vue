<template>
    <v-card id="top" class="custom-border border pa-5">
        <section
            class="d-flex flex-column align-center justify-center my-10 fade"
            v-if="error"
        >
            <v-sheet width="350" class="d-flex flex-column align-center justify-center">
                <v-img 
                    max-width="230" 
                    :src="require('@/assets/default/empty_published.png')" />
                <h3 class="text-center poppins secondary-1--text fw600 my-5 text-center">
                    Oops!
                </h3>
                <div class="text-center poppins secondary-1--text f12 mb-3">
                    It seems like this course is no longer available for access. 
                    Please reach out to your instructor for more information or explore other available courses.
                </div>
            </v-sheet>
        </section>
        <v-row no-gutters v-if="!loading && !error" dense>
          <v-col cols="12" lg="7">
            <img 
                alt="course image"
                :src="course.image ? course.image.url : require('@/assets/images/course-photo.png')" 
                v-on:error="course.image = null"
                class="rounded-lg"
                height="300"
                width="100%"
                />
            <CourseDetails :course="course"/>
          </v-col>
          <v-spacer />
          <v-col cols="12" lg="4">
            <v-card-text>
                <CourseOutline :modules="course.modules" :loading="loading"/>
            </v-card-text>
          </v-col>
        </v-row>
        <LinearProgress  v-if="loading" />

    </v-card>
</template>

<script>

import { mapActions, mapState } from "vuex"
import CourseOutline from "@/components/reusable/course/CourseOutline.vue"
import CourseDetails from "./CourseDetails.vue";

export default {
    components: {
        CourseDetails,
        CourseOutline
    },
    data: () => ({
        loading: false,
        error: false,
    }),
    mounted() {
        this.$vuetify.goTo("#top")
        this.getCourse()
    }, 
    computed: {
        ...mapState({
            course: (state) => state.course,
        }),
    },
    methods: {
        ...mapActions(['getOtherCourseAction']),

        getCourse(){
            this.loading = true
            this.getOtherCourseAction(this.$route.params.uuid).then(() => {
                this.loading = false
            }).catch(() => {
                this.loading=false
                this.error = true
            })
        }
    },
}
</script>