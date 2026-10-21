<template>
    <v-card class="custom-border" elevation="2" width="100%">
        <section class="d-flex flex-row justify-space-between">
            <section class="pointer-cursor" @click="$router.push({name: 'Course Learn More', params: { uuid: course.uuid}, query: { timestamp: Date.now() }})">
                <v-img
                    width="80"
                    height="100%"
                    :src="course.image ? course.image.url : require('@/assets/images/landing/background/default-course-bg.png')"
                    v-on:error="require('@/assets/images/landing/background/default-course-bg.png')"/>
            </section>
            <section class="d-flex flex-column flex-grow-1 cursor-pointer">
                <v-card-subtitle class="poppins fw600 pb-0"  @click="$router.push({name: 'Course Learn More', params: { uuid: course.uuid}, query: { timestamp: Date.now() }})">
                     
                    <div class="f10 poppins secondary-2--text text-uppercase ">
                        {{ course.type_of_learning }}
                    </div>
                    {{ course.title ? course.title : 'Untitled' }}
                    <!-- {{ course.title.length > 45 ? `${course.title.slice(0, 26)}...` : course.title}} -->
                    <div class="poppins fw600 py-0 secondary-2--text f11">
                        {{ course.training_hours }} {{ course.training_hours > 1 ? 'hours' : 'hour' }} • {{ course.modules_count ? course.modules_count : 0 }} {{ course.modules_count > 1 ? 'modules' : 'module'}} 
                    </div>
                    <div v-if="course.available_until" class="primary-font fw500 f11 py-0">
                        <span v-if="course.available_until.start_date"> from {{ $dateFormat.mmDDyy(course.available_until.start_date) }} </span>
                        <span v-if="course.available_until.end_date"> until {{ $dateFormat.mmDDyy(course.available_until.end_date) }} </span>
                    </div>
                </v-card-subtitle>
                <v-card-subtitle class="d-flex flex-right cursor-pointer py-0 mb-2"  @click="$router.push({name: 'Course Learn More', params: { uuid: course.uuid}, query: { timestamp: Date.now() }})">
                    <span class="l-primary--text fw600" v-if="course.is_free">FREE!</span>
                    <span class="l-primary--text fw600" v-if="!course.is_free">PHP {{ course.price ? course.price : 0 }}</span>
                </v-card-subtitle>

                <v-row no-gutters height="14" :style="'flex: inherit;'" v-if="course.course_is_open_for_registration" class="f10">
                    <v-col v-if="!cart_courses.find(i => i.uuid === course.uuid)" cols="6" :style="`background-color: ${getSecondary5}`" align="center" class="pa-2 seondary-2--text d-flex justify-center align-center pointer-cursor fw400 primary-font rounded-bl-l" @click="$router.push({name: 'Course Learn More', params: { uuid: course.uuid}, query: { timestamp: Date.now() }})">
                        Learn more
                    </v-col>
                    <v-col v-if="loading" cols="6" align="center" class="pa-2 white--text d-flex justify-center align-center pointer-cursor fw400 primary-font rounded-br-l">
                        <v-progress-circular
                            size="20"
                            indeterminate
                            color="l-primary"
                        ></v-progress-circular>
                    </v-col>
                    <v-col v-else-if="!cart_courses.find(i => i.uuid === course.uuid) && !loading" cols="6" align="center" class="brand_gradient_bg pa-2 white--text d-flex justify-center align-center pointer-cursor fw400 primary-font rounded-br-l" @click="addToCart(course)">
                        Add to my List
                    </v-col>
                    <v-col v-if="cart_courses.find(i => i.uuid === course.uuid)" cols="12" :style="`background-color: ${getPrimary}`" align="center" class="pa-2 white--text d-flex justify-center align-center pointer-cursor fw400 primary-font rounded-br-l">
                        Added to my List
                    </v-col>
                </v-row>
                <v-row no-gutters :style="'flex: inherit;'" v-if="!course.course_is_open_for_registration" class="f10">
                    <v-col cols="12" :style="`background-color: ${getSecondary5}`" align="center" class="pa-2 seondary-2--text d-flex justify-center align-center pointer-cursor fw400 primary-font rounded-bl-l" @click="$router.push({name: 'Course Learn More', params: { uuid: course.uuid}, query: { timestamp: Date.now() }})">
                        Learn more
                    </v-col>
                </v-row>
            </section>
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