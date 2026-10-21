<template>
    <v-card tile max-height="400" >
        <v-dialog max-width="500" v-model="dialog">
            <section class="mb-3">
                <Login v-if="showLogin" @toRegister="showLogin = false" transition="fade-transition" :toCheckout="true"/>
                <Register v-else-if="!showLogin" @toLogin="showLogin = true" transition="fade-transition"/> 
            </section>
        </v-dialog>
        <v-toolbar
                dense 
                class="fixed label f12 fw600 primary-font secondary-2--text "
                :elevation="offsetTop === 0 ? '0' : '3'"
            >
                My List
        </v-toolbar>
        <v-list width="310" height="180" dense style="overflow-y: auto;" v-scroll.self="onScroll">
            <v-list-item v-for="item in courses" :key="item.uuid" class="d-flex align-start flex-row">
                <MyListDialogCard 
                    :course="item" 
                    @select="$router.push({name: 'Course Learn More', params: { uuid: item.uuid}, query: { timestamp: Date.now() }})"
                    @remove="removeFromCart(item.uuid)"
                    :landing="true"/>
            </v-list-item>
            
        
            <v-container class="d-flex flex-column text-center pa-10" v-if="courses.length === 0">
                <span class="f12">Your list is empty</span>
                <span v-if="$route.name != 'Browse Courses'" class="secondary--text primary-font f12 fw600 pointer-cursor"  @click="$router.push({name: 'Browse Courses'})">Browse Courses</span>
            </v-container>
        </v-list>
        
        <v-card-subtitle class="fixed primary-font l-primary--text f12 fw600 text-right py-2" v-if="courses.length > 0">
            Total: Php {{ getPrice }}
        </v-card-subtitle>

        <v-card-title class="d-flex flex-column py-0" v-if="courses.length > 0 && $route.name !== 'User Cart'">
            <v-btn
                outlined
                color="l-primary"
                class=" f11 primary-font mb-2"
                dense
                block
                @click="dialog = true">
                Go to Checkout
            </v-btn>
            <v-btn
                text
                class=" f11 primary-font mb-2"
                dense
                @click="()=>{
                    this.$emit('close')
                    $router.push({name: 'User Cart'})
                }"
                block>
                Go to List
            </v-btn>
        </v-card-title>
    </v-card>
</template>

<script>
import {mapState, mapMutations} from 'vuex'
import Login from './forms/Login.vue'
import Register from './forms/Register.vue'
import MyListDialogCard from "@/components/user/course/cards/MyListDialogCard"

export default {
    name: 'cart-menu',
    components: {
        Login,
        Register,
        MyListDialogCard
    },
    data: () => ({
        loading: false,
        dialog: false,
        showLogin: false,
        offsetTop: 0,
    }),
    computed: {
        ...mapState({
            courses: (state) => state.cart
        }),

        getPrice() {
            if(this.courses.length === 0) {
                return 0
            } else {
                let price = 0
                this.courses.forEach(i => {
                    if(!i.is_free) {
                        price += parseInt(i.price ? i.price : 0)
                    }
                })
                return price
            }
        }
    },
    methods: {
        ...mapMutations(['cartMutation']),

        truncateDescription(desc, length) {
            return desc.length > length ? `${desc.slice(0, length-4)}...` : desc
        },

        onScroll (e) {
            this.offsetTop = e.target.scrollTop
        },

        removeFromCart(uuid){
            let filtered = this.courses.filter(i => i.uuid !== uuid)
            this.cartMutation(filtered)
            if(filtered.length > 0) {
                localStorage.setItem('cart', JSON.stringify(filtered))
            } else {
                localStorage.removeItem('cart')
            }
            
        }
    }
}
</script>