<template>
    <v-card width="250" class="custom-border d-flex flex-row pointer-cursor">
        <section>
            <v-img 
                alt="completed course bg"
                :src="course.course.image ? course.course.image.url : require('@/assets/images/default-course-bg.png')"
                :lazy-src="course.course.image ? course.course.image.url : require('@/assets/images/default-course-bg.png')"
                v-on:error="course.course.image = null"
                height="100"
                width="50"
                class="rounded-l-lg"
            /> 
        </section>

        <section class="d-flex justify-center flex-column pa-2 flex-grow-1">
            <div class="fw600">
                {{ course.course.title ? (course.course.title.length > 20 ? `${course.course.title.slice(0, 17)}...` : course.course.title) : 'Untitled'}}
            </div>
            <!-- <div class="d-flex justify-space-between f11">
                <span class=" py-0 secondary-2--text text-uppercase">
                    STARTED
                </span>
                {{  $dateFormat.mmDDyy(course.course.completed_course.created_at) }}
            </div> -->
            <div class="d-flex justify-space-between f11">
                <span class=" py-0 secondary-2--text ftext-uppercase">
                    COMPLETED
                </span>
                {{  $dateFormat.mmDDyy(course.created_at) }}
            </div>
            <!-- <div class="d-flex justify-space-between f11" v-if="course.course.training_hours">
                <span class=" py-0 secondary-2--text text-uppercase">
                    TRAINING HOURS
                </span>
                <v-spacer />
                <span>
                    {{ course.course.training_hours }} hrs
                </span>
            </div> -->
        </section>
    </v-card>
</template>

<script>
import { defineComponent } from '@vue/composition-api'

export default defineComponent({
    props: {
        course: Object
    }
})
</script>
