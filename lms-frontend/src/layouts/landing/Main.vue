<template>
        <v-app id="home" width="100vw">
            <NavBar/>
            <ConnectionDialog />
            <v-main :style="mainStyle">
                <v-alert
                    type="success"
                    elevation="6"
                    style="position: fixed; z-index: 10; margin-top: 30px; right: 10vw;"
                    v-if="alert.show"
                    class="primary-font f14"
                    transition="scale-transition"
                >
                    {{ alert.text }}
                </v-alert>
                <router-view/>
            </v-main>
            <Footer/>
        </v-app>

</template>

<script>
import { mapState, mapMutations, mapActions} from 'vuex'
import ConnectionDialog from '@/components/reusable/ConnectionDialog.vue';
import NavBar from './NavBar.vue';
import Footer from './Footer.vue'

export default {
    name: 'layout-main',
    components: {
        NavBar,
        Footer,
        ConnectionDialog
    },
    data() {
        return {
            background_url: '',
            default_url: require('@/assets/images/landing/campus-building.jpg'),
        }
    },
    computed: {
        ...mapState({
            alert: (state) => state.alert
        }),

        mainId(){
            return ['Contact Us', 'Home'].includes(this.$route.name) ? 'brand_bg' : ''
        },

        mainStyle() {
            if (['Home', 'Contact Us'].includes(this.$route.name) && this.background_url) {
                
                return {
                    backgroundImage: `linear-gradient(to bottom, rgba(0,0,0,0.5), rgba(0,0,0,0)), url(${this.background_url})`,
                    backgroundPosition: 'center',
                    backgroundSize: '100% 100%',
                };
            } else if  (['Home', 'Contact Us'].includes(this.$route.name)) {
                return {
                    backgroundImage: `linear-gradient(to bottom, rgba(0,0,0,.5), rgba(0,0,0,0)), url(${this.default_url})`,
                    backgroundPosition: `center`,
                    backgroundSize: `100% 100% !important`,
                };
            }
        }
    },
    methods:  {
        ...mapMutations(['alertMutation', 'connectionMutation']),
        ...mapActions(['getHomeBackgroundsAction']),

        getBackgrounds() {
            this.getHomeBackgroundsAction().then((res) => {
                if (res && res.image) {
                    this.background_url = res.image.url;
                }
            })
        },
        
        showConnectionDialog(){
            this.connectionMutation({ show: true })
        },
    
        closeConnectionDialog(){
            this.connectionMutation({ show: false })
        },
    },

    watch: {
        alert(val) {
            if(val.show) {
                setTimeout(() => {
                    this.alertMutation({ text: '', show: false})
                }, 3000)
            }
        }
    },

    mounted() {
        this.getBackgrounds()
    },
    
    created() {
        this.getBackgrounds()
        window.addEventListener('offline', this.showConnectionDialog);
        window.addEventListener('online', this.closeConnectionDialog);
    }
}
</script>