<template>
    <v-container id="top" class="mb-10 mt-3">
        <v-dialog max-width="500" v-model="dialog">
            <section>
                <Login v-if="showLogin" @toRegister="showLogin = false" transition="fade-transition" :toCheckout="false"/>
                <Register v-else-if="!showLogin" @toLogin="showLogin = true" transition="fade-transition"/> 
            </section>
        </v-dialog>
        <span class="primary-font fw600 mb-10">
             
            <v-badge
                :content="cart_courses.length"
                class="f22 mt-5"
                :color="cart_courses.length === 0 ? 'transparent' : 'l-primary'"
                :offset-x="-4"
            >
                My List
            </v-badge>
            <v-row no-gutters>
                <v-col cols="12" md="8">
                    <v-list dense v-if="cart_courses.length > 0">
                        <v-list-item v-for="item in cart_courses" :key="item.uuid" class="d-flex my-2 px-3 flex-row align-start">
                                <v-img
                                    width="80"
                                    class="rounded mr-2 align-self-start pointer-cursor"
                                    :src="item.image ? item.image.url : require('@/assets/images/default-course-bg.png')"
                                    :error="require('@/assets/images/default-course-bg.png')"
                                    @click="$router.push({name: 'Course Learn More', params: { uuid: item.uuid}, query: { timestamp: Date.now() }})"/>
                                <v-container class="d-flex flex-column pointer-cursor"  style="min-width: 0" @click="$router.push({name: 'Course Learn More', params: { uuid: item.uuid}, query: { timestamp: Date.now() }})">
                                    <!-- <div class=" f16 poppins fw600 truncate d-inline-block" style="width: 37vw; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        {{ item.title ? item.title : 'Untitled'}}
                                    </div> -->
                                    <div class=" f14 poppins fw600 truncate d-inline-block break-word">
                                        {{ item.title ? item.title : 'Untitled'}}
                                    </div>
                                    <section v-if="item.available_until" class="f11 fw500">
                                        <span v-if="item.available_until.start_date"> from <b class="primary-font">{{ $dateFormat.mmDDyy(item.available_until.start_date) }}</b> </span>
                                        <span v-if="item.available_until.end_date"> until <b class="primary-font">{{ $dateFormat.mmDDyy(item.available_until.end_date) }} </b></span>
                                    </section>
                                    
                                    <span class="l-primary--text f14" v-if="item.is_free">FREE!</span>
                                    <span class="l-primary--text f14" v-if="!item.is_free">PHP {{ item.price ? item.price : 0 }}</span>

                                </v-container>
                                <v-spacer />
                                <v-btn icon dense @click="removeFromCart(item.uuid)" class="primary-font pa-0 text-capitalize" small>
                                    <v-icon> mdi-close </v-icon>
                                </v-btn>
                        </v-list-item>
                    </v-list>
                    <div v-else class="primary-font secondary-4--text pa-5">
                        Your list is empty
                    </div>
                </v-col>
                <v-spacer />
                <v-col cols="12" md="3">
                    <v-sheet class="d-flex flex-column pa-3 mt-5" elevation="4">
                        <div class="primary-font f14 fw400">TOTAL</div>
                        <div class="primary-font f18 fw600">Php {{ getPrice }}</div>
                        <v-divider class="my-2"/>
                        <v-btn
                            color="l-primary"
                            class=" f11 primary-font mt-2 white--text"
                            dense
                            block
                            :disabled="cart_courses.length === 0"
                            @click="dialog = true">
                            {{ getPrice <= 0 ? 'Proceed to enroll' : 'Proceed to payment'}}
                        </v-btn>
                    </v-sheet>
                </v-col>
            </v-row>
        </span>
        <div class="primary-font f18 fw600 mt-5 mb-2" v-if="courses.length > 0">
            <span class="primary-font f20 fw600">
                Add More Courses
            </span>
            <v-slide-group
                show-arrows
                v-if="!$vuetify.breakpoint.xs"
                class="mx-3"
            >
                <v-slide-item v-for="course in courses" :key="course.id">
                    <GalleryCards class="ma-1" :course="course" />
                </v-slide-item>
            </v-slide-group>
            <section v-else class="d-flex flex-wrap my-10" :class="$vuetify.breakpoint.smAndDown && 'justify-center'">
                <GalleryCards v-for="(course, i) in courses" :key="i" :course="course" class="ma-1"/>
            </section>
        </div>

    </v-container>
</template>

<style scoped>
.v-progress-linear {
    width: 180px !important;
    margin-top: 25vh !important;
}
</style>

<script>

import { mapActions, mapState, mapMutations } from "vuex"
import GalleryCards from "@/components/landing/cards/gallery-card.vue";
import Login from "@/components/landing/forms/Login.vue";
import Register from "@/components/landing/forms/Register.vue";

export default {
    name: 'landing-home',
    components: {
        GalleryCards,
        Login,
        Register
    },
    data: () => ({
        loading: false,
        dialog: false,
        showLogin: false
    }),
    mounted() {
        this.loading = true
    }, 
    computed: {
        ...mapState({
            cart_courses: (state) => state.cart,
            courses(state) {
                // return [...state.free_courses, ...state.paid_courses].filter(item => item.uuid !== this.$route.params.uuid)
                return [...state.free_courses, ...state.paid_courses]
            }
        }),

        getPrice() {
            if(this.cart_courses.length === 0) {
                return 0
            } else {
                let price = 0
                this.cart_courses.forEach(i => {
                    if(!i.is_free) {
                        price += parseInt(i.price ? i.price : 0)
                    }
                })

                return price
            }
        }
    },
    methods: {
        ...mapActions(['getTenantCourseAction']),
        ...mapMutations(['cartMutation']),

        truncateDescription(desc, length) {
            return desc.length > length ? `${desc.slice(0, length-4)}...` : desc
        },

        removeFromCart(uuid){
            let filtered = this.cart_courses.filter(i => i.uuid !== uuid)
            this.cartMutation(filtered)
            if(filtered.length > 0) {
                localStorage.setItem('cart', JSON.stringify(filtered))
            } else {
                localStorage.removeItem('cart')
            }
        },

            // if(!localStorage.getItem('access_payload')) {
            //     this.$router.push({ name: 'User Register'})
                //window.open(`${process.env.VUE_APP_TENANT_DOMAIN}register`)
            //}

            // if(!localStorage.getItem('access_payload')) {
            //     var win = document.getElementById('ifr').contentWindow;
            //     win.postMessage({key: 'toLanding', value: true, action: 'set'}, process.env.VUE_APP_TENANT_DOMAIN);
            //     window.open(`${process.env.VUE_APP_TENANT_DOMAIN}/signup`)
            // }
    },
}
</script>