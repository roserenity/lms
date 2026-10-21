<template>
    <v-container class="fade">
        <v-row class="primary-font mb-10 mt-3">
            <v-col cols="12" md="6" class="py-3 f14 d-flex flex-column justify-center">
                <span class="f20 fw600">The Vision is the Challenge</span>
                <v-divider class="l-primary my-5" width="70%" />
                <p v-for="(paragraph, i) in paragraphs" :key="i">
                    {{ paragraph }}
                </p>              
            </v-col>
            <v-col cols="12" md="6" class="d-flex align-center justify-center">
                <v-img :src="require('@/assets/images/landing/vision.png')" contain/>
            </v-col>
        </v-row>
    </v-container>
</template>

<script>
export default {
    name: 'TheVision',
    data: () => ({
        paragraphs: [
            'Supporting a large and varied community of learners is a real challenge. Our learners work in organizations of every size, in different roles and at different stages of their careers, and each of them needs learning that fits.',
            'To meet that challenge, Aster Academy focuses on a small number of priority capabilities and builds them well. Many organizations already have strong internal training teams, so we aim to align and connect those efforts rather than duplicate them.',
            'We complement rather than compete. A shared development agenda opens the door to better research, stronger partnerships and more opportunities for everyone involved in workplace learning.',
        ]
    })
}
</script>
