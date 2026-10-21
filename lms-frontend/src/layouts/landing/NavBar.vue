<template>
    <section class="d-flex flex-column" width="100vw" >
        <v-app-bar app flat elevate-on-scroll height="100" width="100vw" color="white" class="page-border custom-border">
            <v-container fluid class="d-flex flex-row align-center justify-space-between mt-7 py-3" >
                <section class="d-flex flex-row align-center pointer-cursor primary-font f14 fw500" @click="$router.push({ name: 'Home'})">
                    <v-img
                        contain
                        max-width="100"
                        class="mr-3"
                        :src="require('@/assets/images/landing/logo/logo.png')"
                        :lazy-src="require('@/assets/images/landing/logo/logo.png')"/>
                    <span  class="hidden-md-and-down d-text-no-wrap">
                        Aster Academy - Learning Management System
                    </span>
                </section>
                <v-spacer class="hidden-sm-and-down"/>
                <section class="hidden-sm-and-down">
                    <v-list class="d-flex flex-row primary-font f14 fw500 l-primary--text" rounded dense app >
                        <v-list-item class="d-flex justify-center text-center" link to="/">
                            Home
                        </v-list-item>
                        <v-list-item 
                            v-for="item in navigation_links"
                            :key="item.text"
                            class="d-flex justify-center text-center" link :to="item.route">
                            <span v-if="item.text === 'Free Trainings'"><span class="l-primary--text">FREE!&nbsp;</span> Training</span>
                            <span v-else> {{ item.text }} </span>
                        </v-list-item>
                    </v-list>
                </section>
                <section class="d-flex flex-row align-center">
                    <v-menu offset-y :close-on-content-click="closeMenu">
                        <template v-slot:activator="{ on, attrs }">
                            <v-badge
                                overlap
                                bordered
                                class="secondary-font"
                                :color="cart_courses.length === 0 ? 'transparent' : 'l-primary'"
                                :value="cart_courses.length"
                                :content="cart_courses.length"
                                offset-x="18">
                                <v-btn color="danger" icon
                                v-bind="attrs"
                                v-on="on">
                                    <v-icon>
                                        mdi-text-box-outline
                                    </v-icon>
                                </v-btn>
                            </v-badge>
                        </template>
                        <Cart @close="closeMenu = true"/>
                    </v-menu>
                    <v-btn 
                        icon
                        @click="drawer = !drawer"
                        class="hidden-md-and-up"
                        color="#282f81">
                        <v-icon>
                            mdi-dots-grid
                        </v-icon>
                    </v-btn>
                    <section v-if="$route.name !== 'Home'" class="hidden-sm-and-down">
                        <v-btn color="l-primary"
                            class="mx-1 f12 primary-font white--text"
                            @click="dialog = true"
                            dense>
                            Login
                        </v-btn>
                    </section>
                </section>
            </v-container>
        </v-app-bar>
        <v-navigation-drawer
                v-if="$vuetify.breakpoint.mobile || $vuetify.breakpoint.sm"
                v-model="drawer"
                temporary
                right
                app
                id="nav"
                height="100vh"
                class="hidden-md-and-up"
            >

            <v-list class="primary-font l-primary--text mt-5 fw500 f14" >
                <v-list-item class="d-flex flex-row px-8" link to="/">
                    Home
                </v-list-item>
                <v-list-item 
                    v-for="item in navigation_links"
                    :key="item.text"
                    class="d-flex flex-row px-8" link :to="item.route">
                    <span v-if="item.text === 'Free Trainings'"><span class="l-primary--text">FREE!&nbsp;</span> Training</span>
                    <span v-else> {{ item.text }} </span>
                </v-list-item>
                <v-list-item class="px-8">
                    <v-btn color="l-primary"
                    class="mx-1 f12 primary-font white--text"
                    block
                    @click="dialog = true"
                    dense>
                    Login
                </v-btn>
                </v-list-item>
            </v-list>
        </v-navigation-drawer>
        <v-dialog max-width="500" v-model="dialog">
            <section class="mb-3">
                <Login v-if="showLogin" @toRegister="showLogin = false" transition="fade-transition" :toCheckout="false"/>
                <Register v-else-if="!showLogin" @toLogin="showLogin = true" transition="fade-transition"/> 
            </section>
        </v-dialog>
    </section>
</template>

<style scoped>

#nav {
    border-left: 10px solid #282f81;
}

.page-border {
    background-image: url('../../assets/images/landing/background/landing-background.png') !important;
    background-size: 100vw 20px !important;
}
</style>

<script>
import {mapState, mapMutations, mapActions} from 'vuex'
import Cart from '@/components/landing/cart.vue';
import { navigation_links } from '@/constants/navlinks/navigation-links'
import Login from '@/components/landing/forms/Login.vue';
import Register from '@/components/landing/forms/Register.vue';

export default {
    data: () => ({
        navigation_links,
        cart_length: 0,
        closeMenu: false,
        drawer: false,
        dialog: false,
        showLogin: true
    }),
    components: {
        Cart,
        Login,
        Register
    },
    methods: {

        ...mapMutations(['cartMutation']),
    },
    mounted() {
        if(JSON.parse(localStorage.getItem('cart'))) {
            let cart = []
            let _cart = JSON.parse(localStorage.getItem('cart'))
            _cart.forEach(_item => {
                if(this.courses.findIndex(i => i.uuid === _item.uuid)) {
                    cart.push(_item)
                }
            })
            localStorage.setItem('cart', JSON.stringify(cart))
            // this.cart = JSON.parse(localStorage.getItem('cart'))
            this.cartMutation(cart)
        }
    },
    computed: {
        ...mapState({
            user: (state) => state.user,
            cart_courses: (state) => state.cart,
            courses(state) {
                // return [...state.free_courses, ...state.paid_courses].filter(item => item.uuid !== this.$route.params.uuid)
                return [...state.free_courses, ...state.paid_courses]
            }
        })
    },
}
</script>
