<template>
    <v-container class="fade">
        <v-row class="primary-font mb-10 mt-3">
            <v-col cols="12" md="6" class="py-3 f14 d-flex flex-column justify-center">
                <span class="f20 fw600">The Institute</span>
                <v-divider class="l-primary my-5" width="70%" />
                <p v-for="(paragraph, i) in paragraphs" :key="i">
                    {{ paragraph }}
                </p>
            </v-col>
            <v-col cols="12" md="6" class="d-flex align-center justify-center">
                <v-img :src="require('@/assets/images/landing/about-photo.png')" contain/>
            </v-col>
        </v-row>
    </v-container>
</template>

<script>
    export default {
        data: () => ({
            paragraphs: [
                'Aster Academy exists to make practical, high-quality learning available to every professional who wants to grow. We design and deliver programs that help individuals and organizations build the skills they need today and prepare for what comes next.',
                'We provide competency-based training in leadership, people management and foundational workplace skills, together with consulting support for organizations building their own learning programs.',
                'We also partner with subject-matter experts, universities and industry practitioners so that new ideas and proven practices reach our learners quickly.',
                'Finally, we bring these efforts together through research, knowledge management and learning technology, so that every course, cohort and certificate is part of one coherent learning journey.'
            ]
        })
    }
</script>