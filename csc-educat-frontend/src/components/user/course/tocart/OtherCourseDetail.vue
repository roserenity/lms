<template>
    <v-container fluid>
        <RegistrationReminder
            :dialog="dialog"
            :course="course"
            @close="dialog = false"
        />
        <!-- <NominationConfirmationDialog
            :dialog="dialog"
            :course="course"
            @close="dialog = false"
            @success="(e) => {
                successTitle = e.successTitle
                successText = e.successText
                successDialog = true
            }"
        /> -->
        <section class="my-3">
            <v-row dense no-gutters>
                <v-col cols="12" md="8">
                    <section class="d-flex flex-column" style="min-width: 0">
                        <v-card-title class="d-flex flex-column align-start primary-font fw600 break-word mb-4">
                            <v-btn
                                elevation="0"
                                x-small
                                color="success"
                                class="mb-1"
                                dense
                                v-if="course.user_is_enrolled"
                            >
                                ENROLLED
                            </v-btn>
                            {{course.title}}
                        </v-card-title>
                        <v-card-subtitle v-if="course.is_free" class="f12 fw500">
                            <span class="secondary-2--text" >COST : </span>
                            FREE!
                        </v-card-subtitle>
                        <v-card-subtitle v-else-if="!course.is_free" class="f12 fw500">
                            <span class="secondary-2--text" >COST : </span>
                            PHP {{ course.price ? course.price : 0 }}
                        </v-card-subtitle>
                        <v-card-subtitle class="f12 fw500 pt-0">
                            <span class="secondary-2--text" >TRAINING HOURS : </span>
                            {{ course.training_hours }} {{ course.training_hours > 1 ? 'hours' : 'hour' }}
                        </v-card-subtitle>
                        <v-card-subtitle v-if="course.available_until" class="fw500 pt-0 f12">
                            <span class="secondary-2--text" >AVAILABILITY: </span>
                            <span v-if="course.available_until.start_date"> from {{ $dateFormat.mmDDyy(course.available_until.start_date) }} </span>
                            <span v-if="course.available_until.end_date"> until {{ $dateFormat.mmDDyy(course.available_until.end_date) }} </span>
                        </v-card-subtitle>
                        <v-card-subtitle class="fw500 pt-0 f12">
                            <span class="secondary-2--text" >FACILITATOR: </span>
                            <span class="f12 text-capitalize" v-if="!course.facilitator">
                                {{`${course.uploader.first_name ? course.uploader.first_name : 'Instructor'} ${course.uploader.last_name ? course.uploader.last_name : 'Account'} ${course.uploader.suffix ? course.uploader.suffix : ''}`}}
                            </span>
                            <span class="f12 text-capitalize" v-else>
                                {{ course.facilitator }}
                            </span>
                        </v-card-subtitle>
                    </section>
                </v-col>
                <v-col cols="12" md="4" v-if="!course.user_is_enrolled">
                    <v-card-subtitle v-if="user_courses.findIndex(_course => _course.uuid === course.uuid) === -1">
                        <!-- <span class="primary--text mb-3 mx-1 fw600" v-if="course.is_free">FREE!</span>
                        <span class="primary--text mb-3 mx-1 fw600" v-if="!course.is_free">PHP {{ course.price ? course.price : 0 }}</span> -->
                        <v-btn color="primary"
                            class="f12 poppins mb-2 mr-3"
                            block
                            dense
                            v-if="cart.find(i => i.uuid === course.uuid)">
                            Added to my List
                        </v-btn>
                        <v-btn 
                            class="brand_gradient_bg pa-3 white--text f12 poppins mb-2 mr-3"
                            block
                            dense
                            :loading="loading1"
                            v-else
                            @click="addToList(course)">
                            Add to my List
                        </v-btn> 
                        <v-btn color="primary"
                            class="f12 poppins mb-2 mr-3"
                            block
                            dense
                            outlined
                            :loading="loading"
                            v-if="course.course_is_open_for_registration"
                            @click="() => dialog = true">
                            {{ (course.is_free || course.price <= 0) ? 'Proceed to enroll' : 'Proceed to payment'}}
                        </v-btn>
                    </v-card-subtitle>
                    <v-card-subtitle v-else>
                        <v-btn color="primary"
                            class="f12 poppins mb-2 mr-3"
                            dense
                            block
                            @click="$router.push({name: 'User Course Details', params: {id: course.uuid}})">
                            Go to Course
                        </v-btn>
                    </v-card-subtitle>
                </v-col>
            </v-row>
            <v-card-subtitle v-html="course.description ? $dompurifier(course.description) : ''" class="my-3"></v-card-subtitle>
        </section>
        <SuccessDialog :dialog="successDialog" :title="successTitle" :message="successText" :icon="true" />
    </v-container>
</template>

<script>
import {mapActions, mapMutations, mapState} from 'vuex'
import NominationDialog from './NominationDialog.vue'
import NominationConfirmationDialog from './NominationConfirmationDialog.vue';
import SuccessDialog from '@/constants/reusable/SuccessDialog.vue';
import RegistrationReminder from './RegistrationReminder.vue';


export default {
    name: 'course-details',
    props: ['course'],
    data:() => ({
        loading: false,
        loading1: false,
        dialog: false,
        successDialog: false,
        nomination: null,
        successTitle: '',
        successText: ''
    }),
    computed: {
        ...mapState('usr', {
            cart: (state) => state.cart,
            user_courses: (state) => state.courses
        }),
    },

    components: {
        NominationDialog,
        SuccessDialog,
        NominationConfirmationDialog,
        RegistrationReminder
    },

    watch: {
        successDialog(val) {
            if(val) {
                setTimeout(() => {
                    this.successDialog = false 
                }, 7000)
            }
        }
    },

    methods: {
        ...mapMutations(['alertMutation']),
        ...mapMutations('usr', ['cartMutation']),
        ...mapActions(['otherCoursesAction']),
        ...mapActions('usr', ['addToCart', 'getCoursesAction', 'getAllCartItemsAction']),

        save(file) {
            this.nomination = file
            this.proceedToPayment()
        },

        
        getCart() {
            this.getAllCartItemsAction()
        },

        addToList(_course) {
            if(localStorage.getItem('cart')) {
                let _cart = JSON.parse(localStorage.getItem('cart'))
                if(_cart.findIndex(i => i.uuid === _course.uuid) === -1) {
                    localStorage.setItem('cart',JSON.stringify([..._cart, _course]))
                    this.cartMutation([...this.cart, _course])
                }
            } else {
                localStorage.setItem('cart', JSON.stringify([this.course]))
                this.cartMutation([this.course])
            }
            
            this.loading1 = true
            this.addToCart({course_id: [_course.id]}).then(() => {
                this.getAllCartItemsAction().then(() => {
                    this.loading1 = false
                    this.alertMutation({
                        show: true,
                        text: 'Added to List!',
                        type: "success"
                    })
                }).catch(res => {
                    this.loading1 = false
                })
            }).catch(() => {
                this.alertMutation({
                    show: true,
                    text: 'Something went wrong',
                    type: "error"
                })
            })
        },

        proceedToPayment(){
            this.dialog = false
            this.loading = true
            let _form = new FormData()
            _form.append('course_id', this.course.id)

            if(this.nomination) {
                _form.append('nomination_form', this.nomination)
            }

            const config = { 
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'multipart/form-data',
                }
            }

            this.$api.post(`/user/checkout`, _form, config).then(res => {
                if (res.data.hasOwnProperty('mode_of_payment')) {
                    this.loading = false
                    this.alertMutation({
                        show: true,
                        text: 'Enrollment Success! Redirecting you to dashboard...',
                        type: "success"
                    })
                    setTimeout(() => {
                        location.href = '/dashboard'
                        // this.getCoursesAction().then(() => {
                        //     this.otherCoursesAction().then(() => {
                        //         this.getCartItems().then(() => {
                        //             this.$router.push('/')
                        //         })
                        //     })
                        // })
                    }, 1000)
                } else {
                    if(this.nomination) {
                        this.$router.push({ name: 'User Transaction History' })
                    } else {

                    //     var scriptPattern = /<script\b[^>]*>([\s\S]*?)<\/script>/gi;

                    // // Find all matches
                    //     var scriptTags = res.data.match(scriptPattern);

                    //     // Print or do something else with the script tags

                    //     var scriptContent = scriptTags[1].replace(/<script\b[^>]*>/i, "").replace(/<\/script>/i, "").replace(/\n|\r|\t/g, '');
                    //     /*     console.log("content: ", scriptContent); */
                    //     const vars = scriptContent.split("var")
                    //     let newVars = vars.filter(item => item.trim() !== "");
                    //     /*     console.log("vars: ", newVars);
                    //     */
                    //     let paymentJsonString = newVars[0].match(/JSON\.parse\('([^']+)'\)/)[1];
                    //     let paymentDetails = JSON.parse(paymentJsonString);
                    //     /* console.log(paymentDetails) */
                    //     let postUrl = newVars[1].match(/post_url\s*=\s*"([^"]+)"/)[1];

                    //     var form = document.createElement('form');
                    //     /* console.log(form) */
                    //     form.setAttribute('action', postUrl);
                    //     form.setAttribute('method', 'POST');
                    //     form.setAttribute('id', 'form_submit');
                        
                    //     for (var key in paymentDetails) {
                    //         /* console.log(key) */
                    //         if (paymentDetails.hasOwnProperty(key)) {
                    //             var input = document.createElement('input');
                    //             input.setAttribute('type', 'hidden');
                    //             input.setAttribute('name', key);
                    //             input.setAttribute('value', paymentDetails[key]);
                    //             form.appendChild(input);
                    //         }
                    //     }

                    //     var newWindow = window.open('', '_blank');
                    //     newWindow.document.body.appendChild(form);
                    //     //document.body.appendChild(form);
                    //     try {
                    //         form.submit();
                    //     } catch (error) {
                    //         console.log(error)
                    //     }

                        this.$paymentHandler(res.data)
                        
                        /* var newDoc = document.implementation.createHTMLDocument();
                        var parsedContent = document.createElement('div');

                        var parser = new DOMParser();
                        var xmlDoc = parser.parseFromString(res.data, 'text/html');

                        parsedContent.appendChild(xmlDoc.documentElement);
                        newDoc.body.appendChild(parsedContent);
                        
                        var newTab = window.open();
                        newTab.document.write(newDoc.documentElement.outerHTML); */

                        this.loading = false
                        location.href = '/dashboard'
                    }
                }
            }).catch(e => {
                if(e.response?.status === 404) {
                    this.loading = false
                    this.alertMutation({
                        show: true,
                        text: 'Checkout is not available at this moment',
                        type: "error"
                    })
                    // this.errorPrompt = true
                    // this.checkoutLoading = false
                } else if(e.response?.status === 409) { 
                    this.loading = false
                    this.alertMutation({
                        show: true,
                        text: e.response.data.message,
                        type: "error"
                    })
                } else {
                    this.loading = false
                    this.alertMutation({
                        show: true,
                        text: 'Something went wrong.',
                        type: "error"
                    })
                }
            }).finally(() => {
                this.loading = false
            })
            
        }
    }
}
</script>