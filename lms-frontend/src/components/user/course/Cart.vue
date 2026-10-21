<template>
    <v-card tile max-height="400" width="300" >
        <v-toolbar
                dense 
                class="fixed label f12 fw600 secondary-2--text"
                :elevation="offsetTop === 0 ? '0' : '3'"
            >
                My List
        </v-toolbar>
        <v-divider />
        <LinearProgress v-if="loading" />
        <v-list id="cart-scroll-list" width="300" height="250" dense style="overflow-y: auto;" v-scroll.self="onScroll">
            <v-list-item v-for="item in cartlist" :key="item.uuid">
                <MyListDialogCard 
                    :course="item" 
                    @select="$router.push({name: 'User Other Course Details', params: { uuid: item.uuid}, query: { timestamp: Date.now() }})"
                    @remove="removeFromList(item.id)"
                    :landing="false"/>
            </v-list-item>
            <v-container class="d-flex flex-column text-center" v-if="cartlist.length === 0 && !loading">
                <span class="f12 secondary-2--text mx-1"><i>Your list is empty</i></span>
            </v-container>
        </v-list>
    </v-card>
</template>

<script>
import {mapActions, mapState, mapMutations} from 'vuex'
import MyListDialogCard from "@/components/user/course/cards/MyListDialogCard"

export default {
    name: 'cart-menu',
    components: {
        MyListDialogCard
    },
    computed: {
        ...mapState('usr', {
            cart: (state) => state.cart
        })
    },
    watch: {
        cart(val){
            if((val.length != this.cartlist.length) && !this.loading) {
                this.page = 1
                this.getCartItemsAction({page: this.page}).then(res => {
                    this.total = res.total

                    if(this.cartlist.length !== res.total) {
                        this.page += 1
                    }
                    this.loading = false
                    this.cartlist = res.data
                }).catch(() => {
                    this.loading = false
                })
            }
        }
    },
    data: () => ({
        offsetTop: 0,
        loading: false,
        page: 1,
        total: -1,
        cartlist: []
    }),
    methods: {
        ...mapMutations(['alertMutation']),
        ...mapMutations('usr', ['cartMutation']),
        ...mapActions('usr', ['removeFromCartAction', 'getCartItemsAction', 'getAllCartItemsAction']),

        onScroll (e) {
            this.offsetTop = e.target.scrollTop
        },

        removeFromList(id){
            if(!this.loading) {
                this.loading = true
                this.removeFromCartAction({course_id: id}).then(() => {
                    this.getAllCartItemsAction().then(res => {
                        this.cartlist = res
                        this.loading = false
                        this.page = int(this.cartlist.length / 5)
                    }).catch(() => {
                        this.loading = false
                    })
                }).catch(() => {
                    this.loading = false
                    this.alertMutation({
                        show: true,
                        text: 'Something went wrong in removing the course to list',
                        type: "error"
                    })
                })
            }
        },

        getCartItems(){

            if(!this.loading && (this.total != this.cartlist.length)) {
                this.loading = true
                this.getCartItemsAction({page: this.page}).then(res => {
                    this.total = res.total
                    if(this.cartlist.length !== res.total) {
                        this.page += 1
                    }
                    this.loading = false
                    this.cartlist = [ ...this.cartlist, ...res.data ]
                }).catch(() => {
                    this.loading = false
                })
            }
        }
    },

    mounted() {
        localStorage.removeItem('cart')
        this.getCartItems()

        const masonry = document.querySelector('#cart-scroll-list');
        masonry.addEventListener('scroll', () => {
            if (masonry.scrollHeight - masonry.scrollTop >= masonry.clientHeight) {
                this.getCartItems();
            }
        })
    },
}
</script>