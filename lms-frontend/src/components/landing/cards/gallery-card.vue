<template>
    <v-card class="d-flex flex-column justify-space-between custom-border" width="250" elevation="2">
        <section class="pointer-cursor">
            <img
                width="247"
                height="132"
                :src="course.image ? course.image.url : require('@/assets/images/landing/background/default-course-bg.png')"
                v-on:error="require('@/assets/images/landing/background/default-course-bg.png')"
                @click="$router.push({name: 'Course Learn More', params: { uuid: course.uuid}, query: { timestamp: Date.now() }})"/>
            <section class="d-flex flex-column pointer-cursor" @click="$router.push({name: 'Course Learn More', params: { uuid: course.uuid}, query: { timestamp: Date.now() }})">
                <v-card-subtitle class="poppins fw600 pb-0 break-word">
                    
                    <div class="f10 poppins secondary-2--text text-uppercase ">
                        {{ course.type_of_learning }}
                    </div>
                    {{ course.title? course.title : course.title }}
                    <!-- {{ course.title.length > 45 ? `${course.title.slice(0, 46)}...` : course.title}} -->
                    <div class="poppins fw600 py-0 secondary-2--text f10">
                        {{ course.training_hours }} {{ course.training_hours > 1 ? 'hours' : 'hour' }}
                        <span v-if="course.modules_count > 0"> • {{ course.modules_count }} {{ course.modules_count > 1 ? 'modules' : 'module'}} </span>
                        <span v-if="course.zoom_meetings_count > 0"> • {{ course.zoom_meetings_count }} {{ course.zoom_meetings_count > 1 ? 'live sessions' : 'live session'}} </span>
                    </div>
                    <div v-if="course.available_until" class="primary-font fw500 f10 py-0">
                        <span v-if="course.available_until.start_date"> from {{ $dateFormat.mmDDyy(course.available_until.start_date) }} </span>
                        <span v-if="course.available_until.end_date"> until {{ $dateFormat.mmDDyy(course.available_until.end_date) }} </span>
                    </div>
                </v-card-subtitle>
            </section>
        </section>
        <section>
            <v-card-subtitle class="d-flex flex-right justify-end cursor-pointer pt-0"  @click="$router.push({name: 'Course Learn More', params: { uuid: course.uuid}, query: { timestamp: Date.now() }})">
                <span class="l-primary--text mx-1 fw600" v-if="course.is_free">FREE!</span>
                <span class="l-primary--text mx-1 fw600" v-if="!course.is_free">PHP {{ course.price ? course.price : 0 }}</span>
            </v-card-subtitle>
            <!-- <div v-if="course.available_until && $vuetify.breakpoint.xs" class="primary-font fw500 f12 py-0 pa-3 mt-1">
                <span v-if="course.available_until.start_date"> from <b class="primary-font">{{ $dateFormat.mmDDyy(course.available_until.start_date) }} </b></span>
                <span v-if="course.available_until.end_date"> until <b class="primary-font">{{ $dateFormat.mmDDyy(course.available_until.end_date) }} </b></span>
            </div> -->
            <v-row no-gutters :style="'flex: inherit;'" v-if="course.course_is_open_for_registration" class="f11">
                <v-col v-if="!cart_courses.find(i => i.uuid === course.uuid)" cols="6" :style="`background-color: ${getSecondary5}`" align="center" class="pa-2 seondary-2--text d-flex justify-center align-center pointer-cursor fw400 primary-font rounded-bl-l f10" @click="$router.push({name: 'Course Learn More', params: { uuid: course.uuid}, query: { timestamp: Date.now() }})">
                    Learn more
                </v-col>
                <v-col v-if="loading" cols="6" align="center" class="pa-2 white--text d-flex justify-center align-center pointer-cursor fw400 primary-font rounded-br-l">
                    <v-progress-circular
                        size="20"
                        indeterminate
                        color="l-primary"
                    ></v-progress-circular>
                </v-col>
                <v-col v-else-if="!cart_courses.find(i => i.uuid === course.uuid) && !loading" cols="6" align="center" class="brand_gradient_bg pa-2 white--text d-flex justify-center align-center pointer-cursor fw400 primary-font rounded-br-l f10" @click="addToCart(course)">
                    Add to my List
                </v-col>
                <v-col v-if="cart_courses.find(i => i.uuid === course.uuid)" cols="12" :style="`background-color: ${getPrimary}`" align="center" class="pa-2 white--text d-flex justify-center align-center pointer-cursor fw400 primary-font rounded-br-l f10" @click="addToCart(course)">
                    Added to my List
                </v-col>
            </v-row>
            <v-row no-gutters :style="'flex: inherit;'" v-if="!course.course_is_open_for_registration" class="f11">
                <v-col cols="12" :style="`background-color: ${getSecondary5}`" align="center" class="pa-2 seondary-2--text d-flex justify-center align-center pointer-cursor fw400 primary-font rounded-bl-l f10" @click="$router.push({name: 'Course Learn More', params: { uuid: course.uuid}, query: { timestamp: Date.now() }})">
                    Learn more
                </v-col>
            </v-row>
        </section>
    </v-card>
</template>

<style scoped>
.course_card {
    border: 2px solid var(--bgColor);
}
</style>

<script>
import {mapMutations, mapState} from 'vuex'

export default {
    name: 'gallery-card',
    props: ['course'],
    data:() => ({
        loading: false,
    }),
    methods: {
        ...mapMutations(['cartMutation', 'alertMutation']),

        addToCart(_course) {
            this.loading = true
            if(localStorage.getItem('cart')) {
                let _cart = JSON.parse(localStorage.getItem('cart'))
                if(_cart.findIndex(i => i.uuid === _course.uuid) === -1) {
                    localStorage.setItem('cart',JSON.stringify([..._cart, _course]))
                    this.cartMutation([..._cart, _course])
                    //this.alertMutation({text: 'Added to List!', show: true, type: 'success'})
                }
            } else {
                localStorage.setItem('cart', JSON.stringify([this.course]))
                this.cartMutation([this.course])
                //this.alertMutation({text: 'Added to List!', show: true, type: 'success'})
            }
            this.loading = false
        },
    },
    computed: {
        ...mapState({
            cart_courses: (state) => state.cart
        }),
        getPrimary(){
            return this.$vuetify.theme.currentTheme['l-primary']
        },
        getSecondary5(){
            return this.$vuetify.theme.currentTheme['secondary-5']
        },
    },
    
}
</script>