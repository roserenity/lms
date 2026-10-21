<template>
    <v-footer color="white" class="pa-0">
        <!-- <section :style="'width: 100%'" v-if="$route.name !== 'How It Works' && $route.name !== 'Forgot Password'">
            <v-container fluid class="d-flex flex-row align-center justify-end">
                <b>
                    <span class="primary-font f16 fw600" @click="$router.push({name: 'How It Works'})">New to LMS? See how it works!</span>
                    <v-btn icon @click="$router.push({name: 'How It Works'})">
                        <v-icon small>
                            mdi-chevron-right
                        </v-icon>
                    </v-btn>
                </b>
            </v-container>
        </section> -->
        <v-container fluid class="d-flex align-center f14 mx-3" :class="$vuetify.breakpoint.smAndDown ? 'flex-column' : 'flex-row'">
            <section class="d-flex" :class="$vuetify.breakpoint.smAndDown ? 'flex-column' : 'flex-row align-center'">
                <v-img
                    contain
                    max-width="100"
                    :src="require('@/assets/images/landing/logo/logo.png')"
                    :lazy-src="require('@/assets/images/landing/logo/logo.png')"/>
                <v-divider vertical class="mx-3 hidden-sm-and-down"/>
                <section class="d-flex flex-column">
                    <span class="f14 fw500 primary-font my-1">
                        Learning that moves careers forward
                    </span>
                    <span class="f14 fw500 primary-font my-1">
                        © Aster Academy. All rights reserved.
                    </span>
                </section>
            </section>
            <v-spacer />
            <section class="d-flex flex-row align-center primary-font f12 fw500" :class="$vuetify.breakpoint.smAndDown ? 'justify-end ml-auto' : 'ml-5'">
                <span>Powered by: &nbsp;</span>
                <v-img
                    contain
                    max-width="90" 
                    height="60"
                    :src="require('@/assets/images/landing/logo/developer.png')"
                    :lazy-src="require('@/assets/images/landing/logo/developer.png')"
                    class="mr-3"/>
            </section>
        </v-container>
        <div class="page-border"></div>
    </v-footer>
</template>

<style scoped>
#navigation-banner {
    background-image: url('../../assets/images/landing/background/navigator-background.png');
    background-repeat: no-repeat;
    background-position: center;
    background-size: cover;
}
.page-border {
    height: 20px !important;
    background-image: url('../../assets/images/landing/background/landing-background.png') !important;
    background-size: cover !important;
    width: 100vw !important;
}
</style>

<script>

export default {
    name: 'footer-main',
}
</script>