<template>
    <v-container >
        <v-dialog max-width="500" v-model="dialog">
            <section class="mb-3">
                <Login v-if="showLogin" @toRegister="showLogin = false" transition="fade-transition" :toCheckout="false"/>
                <Register v-else-if="!showLogin" @toLogin="showLogin = true" transition="fade-transition"/> 
            </section>
        </v-dialog>
        <section class="my-3">
            <v-row dense no-gutters>
                <v-col cols="12" md="9" class="d-flex flex-column">
                    <v-card-title class="fw600 primary-font break-word mb-4">
                        {{course.title}}
                    </v-card-title>
                    <v-card-subtitle v-if="course.is_free" class="fw500 f12">
                        <span class="secondary-2--text" >COST : </span>
                        FREE!
                        <span class="mb-3" v-if="!course.is_free">COST : </span>
                    </v-card-subtitle>
                    <v-card-subtitle v-else-if="!course.is_free" class="fw500 f12">
                        <span class="secondary-2--text" >COST : </span>
                        PHP {{ course.price ? course.price : 0 }}
                    </v-card-subtitle>
                    <v-card-subtitle class="fw500 pt-0 f12">
                        <span class="secondary-2--text" >TRAINING HOURS : </span>
                        {{ course.training_hours }} {{ course.training_hours > 1 ? 'hours' : 'hour' }}
                    </v-card-subtitle>
                    <v-card-subtitle v-if="course.available_until" class="fw500 pt-0 f12">
                        <span class="secondary-2--text" >AVAILABILITY: </span>
                        <span v-if="course.available_until.start_date"> from <b class="primary-font">{{ $dateFormat.mmDDyy(course.available_until.start_date) }}</b> </span>
                        <span v-if="course.available_until.end_date"> until <b class="primary-font">{{ $dateFormat.mmDDyy(course.available_until.end_date) }} </b></span>
                    </v-card-subtitle>
                    <v-card-subtitle class="fw500 pt-0 f12">
                        <span class="secondary-2--text" >FACILITATOR: </span>
                        <span class="f12 text-capitalize" v-if="course.facilitator === null || course.facilitator.length === 0">
                            {{`${course.uploader.first_name ? course.uploader.first_name : 'Instructor'} ${course.uploader.last_name ? course.uploader.last_name : 'Account'} ${course.uploader.suffix ? course.uploader.suffix : ''}`}}
                        </span>
                        <span class="f12 text-capitalize" v-else>
                            {{ course.facilitator }}
                        </span>
                    </v-card-subtitle>
                </v-col>
                <v-col cols="12" md="3">
                    <section class="text-right mt-3" v-if="course.course_is_open_for_registration">
                        <v-btn
                            color="l-primary"
                            block
                            class="mx-1 f11 primary-font mb-2 white--text"
                            @click="register(course)"
                            dense>
                            Register Now
                        </v-btn>
                        <v-btn color="l-primary"
                            class="mx-1 f11 primary-font mb-2"
                            block
                            dense
                            @click="courseAddToCart(course)"
                            outlined
                            v-if="!cart.find(i => i.uuid === course.uuid)">
                            Add to my List
                        </v-btn>
                        <v-btn color="l-primary"
                            class="mx-1 f11 primary-font mb-2"
                            block
                            dense
                            outlined
                            v-if="cart.find(i => i.uuid === course.uuid)">
                            Added to my List
                        </v-btn>
                    </section>
                </v-col>
            </v-row>
            <v-card-subtitle v-html="course.description ? $dompurifier(course.description) : ''" class="my-3"></v-card-subtitle>
        </section>
    </v-container>
</template>

<script>
import { mapState, mapMutations, mapActions } from 'vuex'
import Login from '../forms/Login.vue'
import Register from '../forms/Register.vue'

export default {
    name: 'course-details',
    props: ['course'],
    components: {
        Login,
        Register
    },
    computed: {
        ...mapState({
            cart: (state) => state.cart
        }),
    },
    methods: {
        ...mapMutations(['cartMutation', 'alertMutation']),
        ...mapActions('usr', ['addToCart']),

        courseAddToCart(_course) {
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
            }
        },

        register(_course){
            this.courseAddToCart(_course)
            this.dialog = true
        },
    },
    data: () => ({
        loading: false,
        dialog: false,
        showLogin: false
    }),
}
</script>