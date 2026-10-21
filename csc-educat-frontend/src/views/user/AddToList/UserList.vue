<template>
    <section>
        
        <RegistrationReminder
            :dialog="dialog"
            :course="selected"
            @close="dialog = false"
        />
        <!-- <NominationConfirmationDialog
            :dialog="dialog"
            :course="selected"
            @close="dialog = false"
            @success="(e) => {
                successTitle = e.successTitle
                successText = e.successText
                successDialog = true
            }"
        /> -->
        
        <v-card id="top" class="pa-5 mb-3 custom-border border">
            <span class="poppins fw600 mb-10">
                <div class="d-flex flex-wrap justify-space-between fixed poppins f18 fw600 pb-0 mx-1 secondary-1--text mb-4">
                    <v-badge
                        :content="cart.length"
                        :color="cart.length === 0 ? 'transparent' : 'primary'"
                        offset-x="-5"
                        offset-y="18"
                        class="ma-2"
                    >
                        Courses in List
                    </v-badge>

                    <v-btn color="primary" class="ma-2" small href="https://docs.google.com/document/d/1iIpKkaAFY6O9PBFF_yuc1e4FQ4xm-L2fqlFppHYdXm4/edit?usp=sharing" target="_blank"> 
                       Download Commitment Letter Template
                    </v-btn>

                </div>
                <v-divider />
                <LinearProgress v-if="loading1"/>    
                <v-sheet height="auto" max-height="60vh" v-if="cart.length>0" class="d-flex flex-column overflow-y-auto scroller px-1">
                    <v-card v-if="cart.length > 0" v-for="(item, i) in cart" :key="item.uuid" class="my-2 d-flex flex-row justify-space-between custom-border" elevation="2">
                        <section>
                            <v-img
                                alt="course image"
                                width="80"
                                height="100%"
                                container
                                :src="item.image ? item.image.url : require('@/assets/images/landing/background/default-course-bg.png')"
                                :lazy-src="require('@/assets/images/landing/background/default-course-bg.png')"
                                v-on:error="item.image = null"
                                class="rounded-l cursor-pointer"
                                @click="$router.push({name: 'User Other Course Details', params: { uuid: item.uuid}, query: { timestamp: Date.now() }})"/>
                        </section>
                        <section class="d-flex flex-column flex-grow-1 cursor-pointer mb-2">
                            <div @click="$router.push({name: 'User Other Course Details', params: { uuid: item.uuid}, query: { timestamp: Date.now() }})" class=" f12 poppins fw600 truncate d-inline-block mx-2 my-2  break-word">
                                {{ item.title ? item.title : 'Untitled'}}
                            </div>
                            <!-- <div @click="$router.push({name: 'User Other Course Details', params: { uuid: item.uuid}, query: { timestamp: Date.now() }})" class=" f14 poppins fw600 truncate d-inline-block mx-2 my-2 mb-2" style="width: 37vw; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                {{ item.title ? item.title : 'Untitled'}}
                            </div> -->
                            <div @click="$router.push({name: 'User Other Course Details', params: { uuid: item.uuid}, query: { timestamp: Date.now() }})" v-if="item.available_until" class="primary-font fw500 fw500 f12 py-0 mx-2">
                                <span v-if="item.available_until.start_date"> from {{ $dateFormat.mmDDyy(item.available_until.start_date) }} </span>
                                <span v-if="item.available_until.end_date"> until {{ $dateFormat.mmDDyy(item.available_until.end_date) }} </span>
                            </div>
                            <div @click="$router.push({name: 'User Other Course Details', params: { uuid: item.uuid}, query: { timestamp: Date.now() }})" class="f12 fw600 primary--text mx-2" v-if="item.is_free">FREE!</div>
                            <div @click="$router.push({name: 'User Other Course Details', params: { uuid: item.uuid}, query: { timestamp: Date.now() }})" class="f12 fw600 primary--text mx-2" v-if="!item.is_free">PHP {{ item.price ? item.price : 0 }}</div>
                            <section class="d-flex flex-row align-center justify-end mt-1">
                                <v-alert color="success" class="f11" text dense v-if="item.user_is_enrolled">
                                    ENROLLED
                                </v-alert>
                                <v-alert color="secondary-2" class="f11" text dense v-else-if="!item.course_is_open_for_registration">
                                    Registration already closed
                                </v-alert>
                                <v-btn dense 
                                    v-else-if="item.course_is_open_for_registration && user_courses.findIndex(_course => _course.uuid === item.uuid) === -1"
                                    color="primary"
                                    small
                                    class="poppins f09 float-right" 
                                    @click="selected = item, dialog = true"
                                    :loading="checkoutLoading[i]"
                                    >{{ (item.is_free || item.price <= 0) ? 'Proceed to Enroll' : 'Proceed to Payment'}}</v-btn>
                            </section>
                        </section>
                        <v-btn icon dense small @click="removeFromList(item.id)" class="secondary-2--text poppins pa-0 my-0 text-capitalize f10">
                            <v-icon > mdi-close-circle </v-icon>
                        </v-btn>
                    </v-card>
                </v-sheet>
                <section class="" v-else-if="!loading1 && cart.length === 0">
                    <span class="f10 secondary-2--text mx-1"><i>Your list is empty</i></span>
                </section>
            </span>
        </v-card>
        <SuccessDialog 
            :dialog="successDialog" 
            :title="successTitle" 
            :message="successText"
            :icon="true" />
    </section>
    
</template>

<style scoped>
</style>

<script>
import { mapActions, mapState, mapMutations } from "vuex"
import NominationConfirmationDialog from "@/components/user/course/tocart/NominationConfirmationDialog.vue"
import SuccessDialog from '@/constants/reusable/SuccessDialog.vue';
import RegistrationReminder from "@/components/user/course/tocart/RegistrationReminder.vue";

export default {
    components: {
        NominationConfirmationDialog,
        SuccessDialog,
        RegistrationReminder
    },
    data: () => ({
        dialog: false,
        successDialog: false,
        loading: false,
        loading1: false,
        selected: null,
        removeLoading: false,
        checkoutLoading: {},
        errorPrompt: false,
        nomination: null,
        nominationLoading: false,
        successTitle: '',
        successText: '',
    }),
    mounted() {
        //this.loading = true
        
        this.getCart()
    }, 
    computed: {
        ...mapState({
            course: (state) => state.course,
        }),
        ...mapState('usr', {
            cart: (state) => state.cart,
            user_courses: (state) => state.courses,
            courses: (state) => state.courses,
            classes: (state) => state.classes
        }),
    },
    methods: {
        ...mapMutations(['alertMutation']),
        ...mapMutations('usr',['cartMutation']),
        ...mapActions('usr', [ 'removeFromCartAction', 'getAllCartItemsAction']),

        getCart() {
            this.loading1 = true
            this.getAllCartItemsAction().finally(() => {
                this.loading1 = false
            })
        },

        removeFromList(id){
            localStorage.removeItem('cart')

            this.loading1 = true
            this.removeFromCartAction({course_id: id}).then(() => {
                this.getAllCartItemsAction().then(() => {
                    this.loading1 = false
                }).catch(() => {
                    this.loading1 = false
                })
            }).catch(() => {
                this.loading1 = false
            })
        },

        save(file) {
            this.nomination = file
            this.dialog = false
            this.proceedToPayment()
        },

        proceedToPayment(){
            let _index = this.cart.findIndex(_course => _course.id === this.selected.id)
            this.checkoutLoading[_index] = true
            let _form = new FormData()

            _form.append('course_id', this.selected.id)

            if(this.nomination) {
                _form.append('nomination_form', this.nomination)
                this.nominationLoading = true
            }

            const config = { 
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'multipart/form-data',
                }
            }

            this.$api.post(`/user/checkout`, _form, config).then(res => {
                if (res.data.hasOwnProperty('mode_of_payment')) {
                    this.checkoutLoading[_index] = false
                    this.alertMutation({
                        show: true,
                        text: 'Enrollment Success!',
                        type: "success"
                    })
                    this.getCart()
                } else {
                    if(this.nomination) {
                        this.$router.push({ name: 'User Transaction History' })
                    } else {

                        // var scriptPattern = /<script\b[^>]*>([\s\S]*?)<\/script>/gi;

                        // // Find all matches
                        // var scriptTags = res.data.match(scriptPattern);

                        // // Print or do something else with the script tags

                        // var scriptContent = scriptTags[1].replace(/<script\b[^>]*>/i, "").replace(/<\/script>/i, "").replace(/\n|\r|\t/g, '');
                        // // console.log("content: ", scriptContent);
                        // const vars = scriptContent.split("var")
                        // let newVars = vars.filter(item => item.trim() !== "");
                        // // console.log("vars: ", newVars);
                        // let jsonStringStartIndex = newVars[0].indexOf('{');
                        // let jsonStringEndIndex = newVars[0].lastIndexOf('}');
                        // let paymentJsonString = newVars[0].substring(jsonStringStartIndex, jsonStringEndIndex + 1);
                        // let paymentDetails = JSON.parse(paymentJsonString);
                        // // console.log(paymentDetails)
                        // let postUrl = newVars[1].match(/post_url\s*=\s*"([^"]+)"/)[1];

                        // var form = document.createElement('form');
                        // /* console.log(form) */
                        // form.setAttribute('action', postUrl);
                        // form.setAttribute('method', 'POST');
                        // form.setAttribute('id', 'form_submit');
                        
                        // for (var key in paymentDetails) {
                        //     /* console.log(key) */
                        //     if (paymentDetails.hasOwnProperty(key)) {
                        //         var input = document.createElement('input');
                        //         input.setAttribute('type', 'hidden');
                        //         input.setAttribute('name', key);
                        //         input.setAttribute('value', paymentDetails[key]);
                        //         form.appendChild(input);
                        //     }
                        // }

                        // var newWindow = window.open('', '_blank');
                        // newWindow.document.body.appendChild(form);
                        // //document.body.appendChild(form);
                        // try {
                        //     form.submit();
                        // } catch (error) {
                        //     console.log(error)
                        // }
                        this.$paymentHandler(res.data)

                      // Regular expression to match script tags
                      

                     /*    scriptTags.forEach(function(scriptTag) {
                            console.log("tag: ", scriptTag);

                            var scriptContent = scriptTag.replace(/<script\b[^>]*>/i, "").replace(/<\/script>/i, "");
                            console.log("content: ", scriptContent); 
                        }); */


          /*               const newTab = window.open('');
                        newTab.document.write(res.data);

                        // Append the script to the head
                        newTab.document.addEventListener('DOMContentLoaded', function() {
                            const head = newTab.document.querySelector('head');
                            
                            var jqueryScript = newTab.document.createElement('script');
                            jqueryScript.src = "https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js";
                            head.insertAdjacentHTML('beforeend', jqueryScript);
                        }); */
                       /*  var xmlDoc = parser.parseFromString(res.data, 'text/html');

                        parsedContent.appendChild(xmlDoc.documentElement);
                        newDoc.body.appendChild(parsedContent);

                        var newTab = window.open();

                        var jqueryScript = newTab.document.createElement('script');
                        jqueryScript.src = ;
                        newTab.document.head.appendChild(jqueryScript);

                        var metaTag = newTab.document.createElement('meta');
                        metaTag.setAttribute('http-equiv', 'Cross-Origin-Resource-Policy');
                        metaTag.setAttribute('content', 'cross-origin');
                        newTab.document.head.appendChild(metaTag);

                        newTab.document.write(newDoc.documentElement.outerHTML); */

                        this.checkoutLoading[_index] = false;
                        location.href = '/dashboard';
                    }
                    
                }

                this.nominationLoading = false
            }).catch(e => {
                console.log(e)
                this.nominationLoading = false
                this.dialog = false
                if(e.response?.status === 404) {
                    this.checkoutLoading = {
                        ...this.checkoutLoading,
                        _index: false
                    }
                    this.alertMutation({
                        show: true,
                        text: 'Checkout is not available at this moment',
                        type: "error"
                    })
                } else if(e.response?.status === 409) { 
                    this.checkoutLoading[_index] = false
                    this.alertMutation({
                        show: true,
                        text: e.response.data.message,
                        type: "error"
                    })
                } else {
                    this.checkoutLoading[_index] = false
                    this.alertMutation({
                        show: true,
                        text: 'Something went wrong in enrolling to course',
                        type: "error"
                    })
                }

                this.checkoutLoading = {
                    ...this.checkoutLoading,
                    _index: false
                }
            }).finally(() => {
                this.loading = false
            })
            
        }
    },
    watch: {
        successDialog(val) {
            if(val) {
                setTimeout(() => {
                    this.successDialog = false 
                }, 7000)
            }
        }
    }
}
</script>