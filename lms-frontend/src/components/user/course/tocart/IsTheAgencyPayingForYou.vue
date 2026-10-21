<template>
    <v-card class="overflow-x-hidden pa-5">
        <section class="d-flex flex-wrap justify-space-between align-center mb-5">
            <section class="primary-font primary--text fw600 f17">
                Is the agency paying this for you?
            </section>
            <section class="text-right">
                <v-btn icon @click="$emit('close')">
                    <v-icon small>
                        mdi-close
                    </v-icon>
                </v-btn>
            </section>
        </section>
        <v-sheet v-if="course" class="py-3 d-flex justify-center flex-column align-center">
            <v-img 
                :src="course.image ? course.image.url : require('@/assets/images/course-photo.png')"
                v-on:error="require('@/assets/images/course-photo.png')"
                class="rounded-lg"
                width="150"
                height="100"
            />
            <b class="mt-2 f16 text-center">{{ course.title ? course.title : 'Untitled' }}</b>
            <section v-if="course.available_until" class="fw500 mx-3 f12">
                <span v-if="course.available_until.start_date"> from {{ $dateFormat.mmDDyy(course.available_until.start_date) }} </span>
                <span v-if="course.available_until.end_date"> until {{ $dateFormat.mmDDyy(course.available_until.end_date) }} </span>
            </section>
            <section v-if="course.is_free" class="f16 fw600 mx-3 f14">
                <span class="secondary-2--text" >COST : </span>
                FREE!
                <span class="mb-3" v-if="!course.is_free">COST : </span>
            </section>
            <section v-if="!course.is_free" class="f16 fw600 mx-3">
                PHP {{ course.price ? course.price : 0 }}
            </section>
        </v-sheet>
        <v-row v-if="course" dense>
            <v-col>
                <v-btn dense block text class="poppins" @click="$emit('no')">
                    NO
                </v-btn>
            </v-col>
            <v-col>
                <v-btn primary block dense color="primary" class="poppins" @click="$emit('yes')">
                    YES
                </v-btn>
            </v-col>
        </v-row>
    </v-card>
</template>

<script>
export default {
    props: {
        course: {
            default: null,
            type: Object || null
        }
    }
}
</script>