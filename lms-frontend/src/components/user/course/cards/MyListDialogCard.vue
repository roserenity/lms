<template>
    <v-card class="pa-2 my-1 fade custom-border" outlined width="100%"  @click="$emit('select')" :style="'background-color: transparent'">
        <section class="d-flex align-start flex-row cursor-pointer">
            <v-img
                stretch
                max-width="50px"
                max-height="50px"
                class="mr-1 cursor-pointer"
                :src="course.image ? course.image.url : require('@/assets/images/landing/background/default-course-bg-square.png')"
                :lazy-src="course.image ? course.image.url : require('@/assets/images/landing/background/default-course-bg-square.png')"
                v-on:error="require('@/assets/images/landing/background/default-course-bg-square.png')"/>
            
            <v-list-item-content width="80%">
                <v-list-item-title>
                    {{ course.title }}
                </v-list-item-title>
                <span :class="[landing ? 'l-primary--text' : 'primary--text']" class="f10 fw600" v-if="course.is_free">FREE!</span>
                <span :class="[landing ? 'l-primary--text' : 'primary--text']" class="f10 fw600" v-if="!course.is_free">PHP {{ course.price ? course.price : 0 }}</span>
            </v-list-item-content>
            <v-btn icon small dense @click.stop @click="$emit('remove')" size="10">
                <v-icon small> mdi-close </v-icon>
            </v-btn>
        </section>
    </v-card>
</template>

<script>
export default {
    props: ['course', 'landing']
}
</script>