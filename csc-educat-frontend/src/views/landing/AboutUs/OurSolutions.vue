<template>
    <v-container class="fade">
        <v-row class="primary-font mb-10 mt-3">
            <v-col cols="12" md="6" class="py-3 f14 d-flex flex-column justify-center">
                <span class="f20 fw600">Our Solutions</span>
                <v-divider class="l-primary my-5" width="70%" />
                <p>
                    Our services include seminars and workshops, consulting and technical assistance, and customized learning solutions. To sustain them, we also publish research and host learning events for learning professionals and leaders. Here is a brief description of each –
                </p>          
            </v-col>
            <v-col cols="12" md="6" class="d-flex align-center justify-center">
                <v-img :src="require('@/assets/images/landing/solutions.png')" contain/>
            </v-col>
        </v-row>
        <v-row justify="center" class="mb-10 ma-1">
            <v-col cols="12" md="5" v-for="(item, i) in solutions" :key="i" :style="'border: thin solid rgba(0,0,0,.12);'" class="d-flex flex-row align-start pa-5 ma-2">
                <!-- <v-card outlined class="d-flex flex-row align-start pa-5 ma-3"> -->
                    <v-avatar class="primary-fade mr-2">
                        <v-icon 
                            tile
                            color="l-primary"
                            v-text="item.icon">
                        </v-icon>
                    </v-avatar>
                    <section class="primary-font">
                        <div class="l-primary--text f20 fw600">
                            {{ item.title }}
                        </div>
                        <div class="mt-5 f14">
                            {{ item.text }}
                        </div>
                    </section>
                <!-- </v-card> -->
            </v-col>
        </v-row>
    </v-container>
</template>

<style>
.primary-fade {
    background-color: rgba(224, 88, 53, 0.1);
}
</style>

<script>
export default ({
    data: () => ({
        solutions: [
            {
                icon: 'mdi-school',
                title: 'Scholarships Management',
                text: 'We manage scholarship programs end to end – communication, screening, tracking and evaluation – so that recipients keep learning and sponsors see a return on their investment.'
            },
            {
                icon: 'mdi-cogs',
                title: 'Consulting Services, Technical Assistance and Customized Solutions',
                text: 'We offer consulting services built on partnership with each client, addressing their highest-priority people and organization development needs, including transition plans, success measures and sustainability.'
            },
            {
                icon: 'mdi-account-tie',
                title: 'Symposiums and Conventions for HR Professionals and Leaders',
                text: 'We host learning forums that promote the sharing of best practices in workplace learning and performance, build partnerships among talent stakeholders and turn ideas into action plans.'
            },
            {
                icon: 'mdi-certificate',
                title: 'Program Accreditation and/or Trainer Certification',
                text: 'We manage accreditation for learning service providers and certification for trainers, including curriculum review, service standards and trainer development plans.'
            },
            {
                icon: 'mdi-note-edit-outline',
                title: 'Research, White Paper and Best Practices on Learning and Development',
                text: 'We conduct and publish evidence-based studies on workplace learning and performance, and keep a repository of action research to encourage the sharing of best practices in talent development.'
            },
        ]
    })
})
</script>
