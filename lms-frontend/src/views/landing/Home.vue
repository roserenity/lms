<template>
    <section class="fade">
        <v-row class="primary-font fade pa-5 d-flex align-center justify-center">
            <v-col cols="12" md="3">
                <Login v-if="showLogin" @toRegister="showLogin = false" transition="fade-transition" class="my-10" :toCheckout="false"/> 
                <Register v-else-if="!showLogin" @toLogin="showLogin = true" transition="fade-transition"/> 
            </v-col>
            <v-col cols="12" md="5" v-if="advisories.length > 0">
                <Carousel :items="images" class="fade"/>
                <!-- <v-card tile class="pb-0 my-10 box-shadow-rd">
                    <v-img 
                        :src="training_calendar" 
                        :lazy-src="training_calendar"/>
                </v-card> -->
             </v-col>
        </v-row>
    </section>
</template>

<script>
import { mapActions, mapState } from "vuex";
import Login from "@/components/landing/forms/Login.vue";
import Register from "@/components/landing/forms/Register.vue";
import HomeCard from "@/components/landing/cards/home-card.vue";
import Carousel from "@/components/reusable/Carousel.vue";

export default {
    name: 'landing-home',
    components: {
        Login,
        Register,
        HomeCard,
        Carousel
    },
    data: () => ({
        showLogin: true,
        loading: false,
        announcement_src1: require('@/assets/images/landing/announcement1.png'),
        announcement_src2: require('@/assets/images/landing/announcement2.png'),
        announcement_src3: require('@/assets/images/landing/announcement3.png'),
        announcement_src4: require('@/assets/images/landing/announcement4.png'),
        announcement_src5: require('@/assets/images/landing/announcement5.png'),
        training_calendar: require('@/assets/images/landing/training-calendar.png'),
    }),
    mounted() {
        // this.loading = true
        // this.getHomepageCourses().then(() => {
        //     this.loading = false
        // }).catch(() => {
        //     this.loading = false
        // })

        this.getHomeAdvisories()
    },
    methods: {
        ...mapActions(['getHomepageCourses', 'getHomeAdvisories'])
    },
    computed: {
        ...mapState({
            courses: (state) => state.other_courses,
            advisories: (state) => state.advisories
        }),

        images(){
            let images = []

            this.advisories.forEach(item => {
                images.push(item.image.url)
            });

            return images
        }
    }
}
</script>