<template>
    <v-card class="pa-5 py-10 box-shadow mr-3" tile>
        <span class="f18 fw600">Welcome back!</span>
        <v-divider class="l-primary my-2 mb-5" width="70%" />
        <v-form ref="form" class="mb-16" :class="$vuetify.breakpoint.mobile ? '': ''">
            <alert 
              v-if="errs && (errs.email || errs.password || errs.status)"
              :show = "errs && (errs.email || errs.password || errs.status)"
              :text = "errorMessage"
              type = "error"
            />
            <v-alert type="success" text v-if="success" 
            :class="$vuetify.breakpoint.mobile? 'mb-n7':'mb-5'">
                Login success! Redirecting you to LMS ...
            </v-alert>
            <v-text-field outlined dense placeholder="Email Address" 
                id="email_address"
                type="email"
                v-model="form.email"
                class="login-custom-field my-5 f14 roboto fw500 secondary-1--text" 
                hide-details
                required
                :rules="[!!form.email.trim()]"
                append-icon="mdi-email-outline"
                color="l-primary"
                autocomplete="off"
            />
                
            <v-text-field outlined dense placeholder="Password" 
                id="pass"
                :type="!show?'password':'text'"
                v-model="form.password"
                class="login-custom-field f14 roboto fw500 secondary-1--text" 
                hide-details
                required
                :rules="[!!form.password.trim()]"
                :append-icon="!show?'mdi-eye-outline':'mdi-eye-off-outline'"
                @click:append="show=!show"
                color="l-primary"
                autocomplete="off"
                />
            <div class="d-flex justify-end align-center my-5 primary-font">
                <!-- <router-link class="text-decoration-none"> -->
                    <b @click="$router.push({ name: 'Forgot Password'})" class="pointer-cursor mt-1 mx-1 l-primary--text f14">
                    Forgot Password
                    </b>
                <!-- </router-link> -->
            </div>
            <v-btn 
                large 
                block 
                color="brand_gradient_bg white--text" 
                style="border-radius: 10px" 
                @click="login"
                id="login_btn"
                >
                <v-progress-circular
                    v-if="logging"
                    indeterminate
                    width="5"
                    color="#fff"/>
                    <div class="primary-font" v-else>
                        Login
                    </div>
            </v-btn>
        </v-form>
        <div class="primary-font text-center f14 fw500">
            <b>No account yet? <span @click="$emit('toRegister')" class="l-primary--text pointer-cursor">Sign up</span></b>
        </div>
        <v-dialog v-model="dialog" persistent max-width="550" transition="scale-transition">
            <v-card>
                <v-card-text >
                    <div class="pa-5 d-flex flex-column align-center" max-height="500">
                        <v-img 
                            :src="require('@/assets/images/login-loading.png')"
                            width="320"
                            contain
                            eager
                        />
                        <v-progress-linear
                            indeterminate
                            color="l-primary"
                            rounded
                            height="4"
                            class="my-3"
                            style="width: 320px;"
                        />
                        
                        <h3 class="text-center secondary-1--text mb-2">
                            Hang tight! We’re getting you set up.
                        </h3>
                        <div class="text-center f12 secondary-1--text" style="width: 320px;">
                            Just a moment! We're getting everything ready in the background to make sure things go smoothly. We appreciate your patience!
                        </div>
                    </div>
                </v-card-text>
            </v-card>
        </v-dialog>
    </v-card>
</template>

<script>
import { mapActions, mapState } from 'vuex';
export default {
    name: 'LoginView',
    props: ['toCheckout'],
    data: () => ({
        form: { email: '', password: '' },
        show: false,
        logging: false,
        success: false,
        images: {},
        errs: [],
        dialog: false,
    }),
    mounted() {
        this.getCustomizationAction().then(res => {
            this.images = res
        })
    },
    methods: {
        ...mapActions(['loginAction', 'getCustomizationAction']),
        ...mapActions('usr', ['addToCart']),

        login() {
            this.errs = []
            if(this.$refs.form.validate()){
                this.logging=true
                // this.dialog = true
                // setTimeout(() => {
                    this.loginAction(this.form).then(res => {
                        if(res.email_verified_at) {
                            if( res.status === 'active') {
                                if(res.role === 'USER') {
                                    if(localStorage.getItem('cart')) {
                                        if(JSON.parse(localStorage.getItem('cart')).length > 0) {
                                            let _cart = JSON.parse(localStorage.getItem('cart'))
                                            let _course_ids = []
                                            _cart.forEach(_item => {
                                                _course_ids.push(_item.id)
                                            })
                                            this.addToCart({ course_id: _course_ids }).then(() => {
                                                localStorage.removeItem('cart')
                                                setTimeout(() => {
                                                    this.$router.push({ name: 'User Dashboard'})
                                                }, 2000)
                                            })
                                        } else {
                                            localStorage.removeItem('cart')
                                            this.$router.push({ name: 'User Dashboard'})
                                        }
                                    } else this.$router.push({ name: 'User Dashboard'})
                                }
                            }
                        } else {
                            this.dialog = false
                            this.logging = false
                            this.$router.push({ name: 'Unverified'})
                        }
                    }).catch(e => {
                        this.dialog = false
                        this.errs = e
                        this.logging = false
                    })
                // }, 3000)
            } else {
                this.errs = []
                if(!(!!this.form.email.trim())) {
                    this.errs = { email: ['Email and Password is required']}
                } else if(!(!!this.form.password.trim())) {
                    this.errs = { password: ['Email and Password is required']}
                }
            }
        }
    },
    computed: {
        ...mapState({
            errors: (state) => state.errors,
        }),

        errorMessage(){
        if(this.errs.email) {
                if(this.errs.email[0] === 'Invalid email or password. Please check your credentials and try again.') {
                return 'No such email or password'
                } else return this.errs.email[0]
        } else if (this.errs.password) {
                if(this.errs.password[0] === 'Invalid email or password. Please check your credentials and try again.') {
                return 'No such email or password'
                } else return this.errs.password[0]
        } else if (this.errs.status) {
                return this.errs.status[0]
        } else return 'Something went wrong. Please try again later.'
        }
    }
}
</script>