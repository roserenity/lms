<template>
    <section>
        <v-card class="custom-border" elevation="2">
            <section class="d-flex flex-row">
                <section class="pointer-cursor" @click="$router.push({name: 'User Other Course Details', params: { uuid: course.uuid}, query: { timestamp: Date.now() }})">
                    <v-img
                        class="rounded-t-l"
                        width="80"
                        :src="course.image ? course.image.url : require('@/assets/images/landing/background/default-course-bg.png')"
                        :lazy-src="require('@/assets/images/landing/background/default-course-bg.png')"
                        v-on:error="require('@/assets/images/landing/background/default-course-bg.png')"/>
                </section>
                <section class="ma-2 d-flex flex-column cursor-pointer" @click="$router.push({name: 'User Other Course Details', params: { uuid: course.uuid}, query: { timestamp: Date.now() }})">
                    <section class="poppins fw600 pb-0 break-word">
                        
                        <span class="success--text f10 px-3 ml-auto fw600" v-if="course.user_is_enrolled" style="background-color: #7BC14533">
                            ENROLLED
                        </span>
                        
                        <div class="f10 poppins secondary-2--text text-uppercase ">
                            {{ course.type_of_learning }}
                        </div>
                        {{ course.title ? course.title : 'Untitled'}}
                        
                        <div class="poppins fw600 text-left f10" v-if="course.course_code">
                            {{ course.course_code }}
                        </div>
                        <!-- {{ course.title.length >25 ? `${course.title.slice(0, 26)}...` : course.title}} -->
                        <!-- <div class="poppins fw600 py-0 secondary-2--text f11">
                            {{ course.training_hours }} {{ course.training_hours > 1 ? 'hours' : 'hour' }} • {{ course.modules_count ? course.modules_count : 0 }} {{ course.modules_count > 1 ? 'modules' : 'module'}} 
                        </div> -->
                        <div v-if="course.available_until" class="primary-font fw500 f10 py-0">
                            <span v-if="course.available_until.start_date"> from {{ $dateFormat.mmDDyy(course.available_until.start_date) }} </span>
                            <span v-if="course.available_until.end_date"> until {{ $dateFormat.mmDDyy(course.available_until.end_date) }} </span>
                        </div>
                    </section>
                    <section class="d-flex flex-right cursor-pointer py-0">
                        <span class="l-primary--text fw600" v-if="course.is_free">FREE!</span>
                        <span class="l-primary--text fw600" v-if="!course.is_free">PHP {{ course.price ? course.price : 0 }}</span>
                    </section>
                </section>
            </section>
            <section  v-if="!course.user_is_enrolled">
                <v-row no-gutters height="14" :style="'flex: inherit;'" v-if="course.course_is_open_for_registration" class="f10">
                    <v-col v-if="!cart.find(i => i.uuid === course.uuid)" cols="6" :style="`background-color: ${getSecondary5}`" align="center" class="pa-2 seondary-2--text d-flex justify-center align-center pointer-cursor fw400 primary-font rounded-bl-l" @click="$router.push({name: 'User Other Course Details', params: { uuid: course.uuid}, query: { timestamp: Date.now() }})">
                        Learn more
                    </v-col>
                    <v-col v-if="loading" cols="6" align="center" class="pa-2 white--text d-flex justify-center align-center pointer-cursor fw400 primary-font rounded-br-l">
                        <v-progress-circular
                            size="20"
                            indeterminate
                            color="l-primary"
                        ></v-progress-circular>
                    </v-col>
                    <v-col v-else-if="!cart.find(i => i.uuid === course.uuid) && !loading" cols="6" align="center" class=" brand_gradient_bg pa-2 white--text d-flex justify-center align-center pointer-cursor fw400 primary-font rounded-br-l" @click="addToList(course)">
                        Add to my List
                    </v-col>
                    <v-col v-if="cart.find(i => i.uuid === course.uuid)" cols="12" :style="`background-color: ${getPrimary}`" align="center" class="pa-2 white--text d-flex justify-center align-center pointer-cursor fw400 primary-font rounded-br-l">
                        Added to my List
                    </v-col>
                </v-row>
                <v-row no-gutters :style="'flex: inherit;'" v-if="!course.course_is_open_for_registration" class="f10">
                    <v-col cols="12" :style="`background-color: ${getSecondary5}`" align="center" class="pa-2 seondary-2--text d-flex justify-center align-center pointer-cursor fw400 primary-font rounded-bl-l" @click="$router.push({name: 'User Other Course Details', params: { uuid: course.uuid}, query: { timestamp: Date.now() }})">
                        Learn more
                    </v-col>
                </v-row>
            </section>
        </v-card>
    </section>
</template>

<script>
import { mapActions, mapMutations, mapState } from 'vuex'

export default {
    name: 'gallery-card',
    props: ['course'],
    data: () => ({
        loading: false
    }),

    computed: {
        ...mapState('usr', {
            cart: (state) => state.cart,
            user_courses: (state) => state.courses
        }),
        getPrimary(){
            return this.$vuetify.theme.currentTheme.primary
        },
        getSecondary5(){
            return this.$vuetify.theme.currentTheme['secondary-5']
        },
    },

    methods: {
        ...mapMutations(['alertMutation']),
        ...mapMutations('usr', ['cartMutation']),
        ...mapActions('usr', ['addToCart']),

        addToList(_course) {

            if(localStorage.getItem('cart')) {
                let _cart = JSON.parse(localStorage.getItem('cart'))
                if(_cart.findIndex(i => i.uuid === _course.uuid) === -1) {
                    localStorage.setItem('cart',JSON.stringify([..._cart, _course]))
                }
            } else {
                localStorage.setItem('cart', JSON.stringify([this.course]))
            }

            if(this.cart.findIndex(i => i.uuid === _course.uuid) === -1) {
                this.loading = true
                this.addToCart({course_id: [_course.id]}).then(() => {
                    this.loading = false
                    this.alertMutation({
                        show: true,
                        text: 'Added to List!',
                        type: "success"
                    })
                }).catch(res => {
                    this.loading = false
                    this.alertMutation({
                        show: true,
                        text: 'Something went wrong in adding the course to list',
                        type: "error"
                    })
                })
            }
        }
    }
}
</script>