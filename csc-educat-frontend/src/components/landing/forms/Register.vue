<template>
    <v-card  class="d-flex align-center justify-center mb-10" :color="!loading ? 'transparent' : 'white'">
        <v-card class="pa-5 py-5 primary-font box-shadow" tile v-if="!loading">
            <span class="f18 fw600">No account ? Sign up for one now!</span>
            <v-divider class="l-primary my-2" width="70%" />
            <v-card flat dense>
                <v-tabs v-model="index" show-arrows dense>
                    <v-tabs-slider color="l-primary"></v-tabs-slider>
                    <v-tab
                        v-for="(item, i) in headers"
                        :key="item.created_at"
                        class="primary-font f12 fw600 secondary--text"
                        active-class="l-primary--text"
                        style="text-transform: inherit; opacity: 1;"
                        disabled
                    >
                        {{ item }}
                    </v-tab>
                </v-tabs>
                <v-sheet height="40vh" v-if="index === 0" class="overflow-y-auto scroller my-1 mb-5 px-2" id="signup_wrapper">
                    <v-form :ref="`custom_registration_page_0`" v-model="valid[0]">
                        <section class="d-flex flex-column my-3">
                            <FieldLabel :text="'First Name *'" />
                            <v-text-field 
                                v-model="formData.first_name"
                                type="text"
                                outlined
                                dense
                                hide-details="auto"
                                class="general-custom-field roboto f14 fw500 secondary-2--text"
                                @keyup="toUpper('first_name')"
                                :rules="[() => !!formData.first_name || 'This field is required']"
                            />
                        </section>
                        <section class="d-flex flex-column my-3">
                            <FieldLabel :text="'Middle Name'" />
                            <v-text-field 
                                v-model="formData.middle_name"
                                type="text"
                                outlined
                                dense
                                hide-details="auto"
                                class="general-custom-field roboto f14 fw500 secondary-2--text"
                                @keyup="toUpper('middle_name')"
                            />
                        </section>
                        <section class="d-flex flex-column my-3">
                            <FieldLabel :text="'Last Name *'" />
                            <v-text-field 
                                v-model="formData.last_name"
                                type="text"
                                outlined
                                dense
                                hide-details="auto"
                                class="general-custom-field roboto f14 fw500 secondary-2--text"
                                @keyup="toUpper('last_name')"
                                :rules="[() => !!formData.last_name || 'This field is required']"
                            />
                        </section>
                        <section class="d-flex flex-column my-3">
                            <FieldLabel :text="'Suffix  (e.g. Jr. II, Sr.) (Leave blank if not applicable)'" />
                            <v-text-field 
                                v-model="formData.suffix"
                                type="text"
                                outlined
                                dense
                                hide-details="auto"
                                class="general-custom-field roboto f14 fw500 secondary-2--text"
                                @keyup="toUpper('suffix')"
                            />
                        </section>
                        <section class="d-flex flex-column my-3">
                            <FieldLabel :text="'Email *'" />
                            <v-text-field 
                                v-model="formData.email"
                                type="email"
                                outlined
                                dense
                                hide-details="auto"
                                class="general-custom-field roboto f14 fw500 secondary-2--text"
                                :rules="[() => 
                                !!formData.email || 'This field is required',
                                v => /.+@.+\..+/.test(v) || 'E-mail must be valid',]"
                            />
                        </section>
                        <section class="d-flex flex-column my-3">
                            <FieldLabel :text="'Password *'" />
                            <v-text-field 
                                v-model="formData.password"
                                outlined
                                autocomplete="off"
                                dense
                                hide-details="auto"
                                class="general-custom-field roboto f14 fw500 secondary-2--text"
                                :type="!showpw?'password':'text'"
                                :append-icon="!showpw?'mdi-eye-outline':'mdi-eye-off-outline'"
                                @click:append="showpw=!showpw"
                                :rules="[() => 
                                !!formData.password || 'This field is required',
                                /^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*_-]).{8,}$/.test(formData.password) || 'Meet the conditions below:',]"
                            />
                            <section dense class="pt-2 f12 secondary-2--text text-left mb-2 fw500">
                                <PasswordRules :password="formData.password"/>
                            </section>
                        </section>
                        <section class="d-flex flex-column">
                            <FieldLabel :text="'Re-type Password *'" />
                            <v-text-field 
                                v-model="retyped"
                                autocomplete="off"
                                outlined
                                dense
                                class="general-custom-field roboto f14 fw500 secondary-2--text"
                                :type="!showretyped?'password':'text'"
                                :append-icon="!showretyped?'mdi-eye-outline':'mdi-eye-off-outline'"
                                @click:append="showretyped=!showretyped"
                                :rules="[() => formData.password === retyped || 'The password and re-typed password does not match']"
                            />
                        </section>
                    </v-form>
                </v-sheet>
                <v-sheet height="40vh" v-if="index > 0" class="overflow-y-auto scroller my-1 mb-5 px-2" id="signup_wrapper">
                    <v-form :ref="`custom_registration_page_${index}`" v-model="valid[index]">
                        <section v-for="(field, p) in contents[index-1].custom_registration_fields" :key="field.created_at" class="d-flex flex-column mb-3">
                            
                            <FieldLabel :text="field.is_required ? `${field.input_field_label} *` : field.input_field_label" />
                            <v-file-input
                                v-if="field.input_field_type === 'file'"
                                dense
                                show-size
                                small-chips
                                accept=".mp4,.pdf,.docx,.ppt,.mov,.webm"
                                v-model="formData.data[contents[index-1].title][field.input_field_name]"
                                @change="()=>{
                                    let _formData = new FormData()
                                    _formData.append('file', formData.data[contents[index-1].title][field.input_field_name])
                                    formData.data[contents[index-1].title][field.input_field_name] = _formData
                                }"
                            />
                            <v-select
                                v-else-if="field.input_field_type === 'dropdown' && field.is_required"
                                :items="field.data"
                                outlined
                                dense
                                hide-details="auto"
                                class="general-custom-field roboto f14 fw500 secondary-2--text"
                                v-model="formData.data[contents[index-1].title][field.input_field_name]"
                                :multiple="Boolean(field.is_multiple)"
                                :rules="[() => !!formData.data[contents[index-1].title][field.input_field_name] || 'This field is required']"
                            ></v-select>
                            <v-select
                                v-else-if="field.input_field_type === 'dropdown' && !field.is_required"
                                :items="field.data"
                                outlined
                                dense
                                hide-details="auto"
                                class="general-custom-field roboto f14 fw500 secondary-2--text"
                                v-model="formData.data[contents[index-1].title][field.input_field_name]"
                                :multiple="Boolean(field.is_multiple)"
                            ></v-select>
                            <v-text-field
                                v-else-if="field.input_field_type === 'password' && field.is_required"
                                outlined
                                dense
                                hide-details="auto"
                                :type="field.input_field_type"
                                autocomplete="off"
                                class="general-custom-field roboto f14 fw500 secondary-2--text"
                                v-model="formData.data[contents[index-1].title][field.input_field_name]"
                                :rules="[() => !!formData.data[contents[index-1].title][field.input_field_name] || 'This field is required']"
                            ></v-text-field>
                            <v-text-field
                                v-else-if="field.input_field_type === 'password' && !field.is_required"
                                outlined
                                dense
                                hide-details="auto"
                                :type="field.input_field_type"
                                autocomplete="off"
                                class="general-custom-field roboto f14 fw500 secondary-2--text"
                                v-model="formData.data[contents[index-1].title][field.input_field_name]"
                            ></v-text-field>
                            <v-text-field 
                                v-else-if="field.is_required"
                                :type="field.input_field_type"
                                outlined
                                dense
                                hide-details="auto"
                                class="general-custom-field roboto f14 fw500 secondary-2--text"
                                v-model="formData.data[contents[index-1].title][field.input_field_name]"
                                :rules="[() => !!formData.data[contents[index-1].title][field.input_field_name] || 'This field is required']"
                            />
                            <v-text-field 
                                v-else
                                :type="field.input_field_type"
                                outlined
                                dense
                                hide-details="auto"
                                class="general-custom-field roboto f14 fw500 secondary-2--text"
                                v-model="formData.data[contents[index-1].title][field.input_field_name]"
                            />
                        </section>
                    </v-form>
                </v-sheet>
                <section v-if="index === 0" class="d-flex flex-row align-center">
                    <v-checkbox
                        dense
                        v-model="checked1"/>
                    <div class="fw400 f10">
                        I have read the Aster Academy <span class="l-primary--text pointer-cursor" @click="privacy = true"><b>Data Privacy Statement</b></span> and express my consent for Aster Academy to collect and process the data herein for the purpose of this course.
                    </div>
                </section>

                <section v-if="index === 0" class="d-flex flex-row align-center fw400 mb-1">
                    <v-checkbox
                        dense
                        v-model="checked2"/>
                    <div class="fw400 f10">
                        I understand that my personal information is protected by R.A. 10173 or the Data Privacy Act of 2012.
                    </div>
                </section>
                
                <v-btn
                    color="l-primary"
                    class="f12 white--text"
                    style="border-radius: 10px"
                    @click="()=>{
                        scrollToTop()

                        if($refs[`custom_registration_page_${index}`].validate()){
                            index ++
                        }
                    }"
                    v-if="headers.length > 1 && index !== headers.length -1"
                    dense
                >
                    Continue
                </v-btn>
                <v-btn 
                    v-if="headers.length > 1 && index !== 0" 
                    class="f12 white--text mr-1" 
                    color="l-primary"
                    style="border-radius: 10px"
                    outlined  @click="index !== 0 && index--" dense>
                    Back
                </v-btn>
                <v-btn 
                    :disabled="!checked1 || !checked2" color="l-primary" class="f12 white--text" style="border-radius: 10px" :loading="loadingRegister"
                    @click="()=>{
                        if($refs[`custom_registration_page_${index}`].validate()){
                            this.registerUser()
                        }
                    }"
                    v-if="index === headers.length -1"
                    dense >
                    Register
                </v-btn>
            </v-card>

            <v-alert type="error" text v-if="errors.email || errors.password || errors.status"
            class="f12 mt-5" 
            :class="$vuetify.breakpoint.mobile? 'mb-7':'mb-5'" dense>
                <div class="f12 roboto" v-for="(item, i) in errors.email" :key="i">
                {{item}}
                </div>
                <div class="f12 roboto" v-for="(item, i) in errors.password" :key="i">
                {{item}}
                </div>
                <div class="f12 roboto" v-if="errors.status">
                {{this.errors.status}}
                </div>
            </v-alert>
            <!-- <v-alert dense v-else-if="successDialog" type="success" text class="f12 mt-5" transition="scale-transition" dismissible>
                Registration Success! Email Verification was sent to your inbox. Please verify your email.
            </v-alert> -->

            <div class="text-center f12 mt-3 pa-0">
                <b>Already have an account ? <span @click="$emit('toLogin')" class="pointer-cursor l-primary--text">Login</span> </b>
            </div>
            <PrivacyDialogVue :privacy="privacy" @close="privacy = false"/>
        </v-card>
        <v-progress-linear
            v-else
            indeterminate
            color="l-primary"
            rounded
            height="8"
            class="mx-auto"
        ></v-progress-linear>
        <EmailVertificationDialog v-if="successDialog" :dialog="successDialog" @close="successDialog = false" @resend="resend" :loading="loadingResend" :msg="msg"/>
    </v-card >
</template>

<script>
import { api } from '@/services/axios'
import { mapActions, mapMutations, mapState } from 'vuex';
import PrivacyDialogVue from '@/components/reusable/PrivacyDialog.vue';
import FieldLabel from '@/components/reusable/forms/FieldLabel.vue'
import PasswordRules from '@/components/reusable/forms/PasswordRules.vue'
import EmailVertificationDialog from '@/views/EmailVertificationDialog.vue';

export default {
    name: "RegisterView",
    components: {
        PrivacyDialogVue,
        FieldLabel,
        PasswordRules,
        EmailVertificationDialog
    },
    data: () => ({
        formData: {
            first_name: null,
            last_name: null,
            middle_name: null,
            suffix: null,
            email: null,
            password: "",
            data: {},
            role: "USER"
        },
        retyped: null,
        showpw: false,
        showretyped: false,
        loading: false,
        loadingRegister: false,
        headers: ["Account Info"],
        contents: [],
        index: 0,
        valid: [],
        loading: false,
        successDialog: false,
        checked1: false,
        checked2: false,
        privacy: false,
        loadingResend: false,
        msg: {
            show: false,
            text: '',
            type: ''
        },
    }),
    created() {
        this.errorsMutation([])
        this.loading = true
        api.get("/tenants/custom-registration/pages").then(res => {
            res.data.data.forEach(page => {
                this.headers.push(page.title);
                this.contents.push(page);
                let _fields = {};
                page.custom_registration_fields.forEach(field => {
                    _fields[field.input_field_name] = null;
                });
                this.formData.data[page.title] = _fields;
            });
            this.loading = false
        });
    },
    methods: {
        ...mapActions(['registerUserAction', 'loginUserAction', 'resendverificationMailAction']),
        ...mapMutations(['signInMutation', 'signInToken', 'errorsMutation']),
        ...mapActions('usr', ['addToCart']),

        scrollToTop() {
            let signup_wrapper = document.getElementById('signup_wrapper')
            signup_wrapper.scrollTop = 0
        },

        registerUser() {
            this.loadingRegister = true
            this.registerUserAction({form: this.formData}).then(res => {
                localStorage.setItem('access_payload_register', res.access_token),
                setTimeout(() => {
                    this.login()
                }, 1000)
            }).catch((err) => {
                if(this.errors.email || this.errors.password || this.errors.status) {
                    this.index = 0
                }
                this.loadingRegister = false
            })
        },

        resend(){
            this.loadingResend = true
            this.msg = {
                show: false,
                text: '',
                type: ''
            }
            
            this.resendverificationMailAction({ 
                token: JSON.parse(localStorage.getItem('access_payload')).token
            }).then(res => {
                this.loadingResend = false
                this.msg = {
                    show: true,
                    text: res,
                    type: "success"
                }
            }).catch(e => {
                this.loadingResend = false
                this.msg = {
                    show: true,
                    text: "Something went wrong",
                    type: "error"
                }
            })
        },

        login() {
            this.loadingRegister = true;
            this.loginUserAction({form: this.formData, token: localStorage.getItem('access_payload_register')}).then(res => {
                this.signInToken(res)
                if (localStorage.getItem("cart")) {
                    if (JSON.parse(localStorage.getItem("cart")).length > 0) {
                        let _cart = JSON.parse(localStorage.getItem("cart"));
                        let _course_ids = [];
                        _cart.forEach(_item => {
                            _course_ids.push(_item.id);
                        });

                        this.addToCart({ course_id: _course_ids }).then(() => {
                            this.loadingRegister = false
                            localStorage.removeItem("cart");
                            this.resetForm()
                            this.retyped = ''
                            this.successDialog = true;
                            this.index = 0
                            this.dialog = true
                            this.checked1 = false
                            this.checked2 = false
                        })
                    } else {
                        this.loadingRegister = false
                        localStorage.removeItem("cart");
                        this.successDialog = true;
                        this.index = 0
                        this.resetForm()
                        this.retyped = ''
                        this.dialog = true
                        this.checked1 = false
                        this.checked2 = false
                    }
                } else {
                    this.loadingRegister = false
                    this.successDialog = true;
                    this.index = 0
                    this.resetForm()
                    this.retyped = ''
                    this.dialog = true
                    this.checked1 = false
                    this.checked2 = false
                }
            }).catch(() => {
                this.loadingRegister = false
            })
        },

        resetForm(){
            this.formData.first_name = ''
            this.formData.last_name = ''
            this.formData.middle_name = ''
            this.formData.password = ''
            this.formData.suffix = ''
            this.formData.email = ''

            this.contents.forEach(tbl => {
                tbl.custom_registration_fields.forEach(field => {
                    console.log(`${tbl.title} : ${field.input_field_name}`)
                    this.formData.data[tbl.title][field.input_field_name] = ''
                })
            })
        },
        // async registerUser() {
        //     this.errors = [];
        //     this.loadingRegister = true;
        //     await api.post("/register", this.formData).then(res => {
        //         this.loadingRegister = false;
        //         var win = document.getElementById("ifr").contentWindow;
        //         win.postMessage({ key: "access_payload_register", value: res.data.data.access_token, action: "set" }, process.env.VUE_APP_TENANT_DOMAIN);
        //         this.successDialog = true;
        //         this.registerSuccess = false;
        //         this.login(res.data.data.access_token);
        //         this.$refs.form.reset();
        //         this.formData.data = null
        //         this.checked1 = false
        //         this.checked2 = false
        //     }).catch((err) => {
        //         console.log(err.response);
        //         this.errors = err.response.data.errors;
        //         if (this.errors.email || this.errors.password || this.errors.status) {
        //             this.index = 1;
        //         }
        //         this.loadingRegister = false;
        //     });
        // },
        // async login(token) {
        //     const config = {
        //         headers: { Authorization: `Bearer ${token}` }
        //     };
        //     await api.post("/login", this.formData, config).then(res => {
        //         let role = setRole(res.data.data);
        //         let keyObject = { token: res.data.data.access_token, ...role };
        //         localStorage.setItem("access_payload", JSON.stringify(keyObject));
        //         // var win = document.getElementById('ifr').contentWindow;
        //         // win.postMessage({key: 'access_payload', value: JSON.stringify(keyObject), action: 'set'}, process.env.VUE_APP_TENANT_DOMAIN);
        //         if (localStorage.getItem("cart")) {
        //             if (localStorage.getItem("cart").length > 0) {
        //                 console.log(localStorage.getItem("cart").length)
        //                 let _cart = JSON.parse(localStorage.getItem("cart"));
        //                 let _course_ids = [];
        //                 _cart.forEach(_item => {
        //                     _course_ids.push(_item.id);
        //                 });
        //                 api.post("/user/add-to-cart", { course_id: _course_ids }).then(() => {
        //                     this.success = true;
        //                     localStorage.removeItem("access_payload");
        //                     localStorage.removeItem("cart");
        //                     setTimeout(() => {
        //                         window.open(`${process.env.VUE_APP_TENANT_DOMAIN}login`, "_self");
        //                     }, 2000);
        //                 });
        //             }
        //             else {
        //                 this.success = true;
        //                 localStorage.removeItem("access_payload");
        //                 localStorage.removeItem("cart");
        //                 setTimeout(() => {
        //                     window.open(`${process.env.VUE_APP_TENANT_DOMAIN}login`, "_self");
        //                 }, 2000);
        //             }
        //         } else {
        //             this.success = true;
        //             localStorage.removeItem("access_payload");
        //             setTimeout(() => {
        //                 window.open(`${process.env.VUE_APP_TENANT_DOMAIN}login`, "_self");
        //             }, 2000);
        //         }
        //     });
        // },
        toUpper(index) {
            let name = this.formData[index].split(" ");
            let new_name = [];
            name.forEach(item => {
                new_name.push(item.charAt(0).toUpperCase() + item.slice(1));
            });
            return this.formData[index] = new_name.join(" ");
        },
    },

    computed: {
        ...mapState({
            errors: (state) => state.errors
        })
    }
}
</script>