<template>
  <v-app >
    <EmailVertificationDialog v-if="dialog" :dialog="dialog" @close="dialog = false" @resend="resend" :loading="loadingResend" :msg="msg"/>
    <v-row no-gutters dense height="100vh">
      <v-col cols="12" lg="8" class="d-flex align-stretch justify-center">
        <v-sheet max-width="380" class="p-0 primary-font d-flex flex-column align-center justify-center">
          <v-container  class="my-auto" v-if="!loading">
            <div class=" d-flex justify-center">
              <div class="">
                  <v-img 
                  id="horizontal_logo"
                  width="160" 
                  :src="customization.hasOwnProperty('login_logo') ? customization.login_logo : `${asset_path}/logo.png`"
                  v-on:error="`${asset_path}/logo.png`"
                  contain
                  class="ma-2"
                  transition="scale-transition"
                />
              </div>
            </div>
            <h3 class="primary-font primary--text my-3">
              Registration
            </h3>
            <FormLabel :label="'Complete the details below to create your account.'" />

            <v-card flat dense class="mb-5">
                <Tabs id="module-evaluation" :value.sync="tab" :tabs="headers"/>
                <v-sheet height="30vh" v-if="index === 0" class="overflow-y-auto scroller mb-5 px-2" id="signup_wrapper">
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
                            <FieldLabel :text="'Suffix  (e.g. Jr. II, Sr.)'" />
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
                                autocomplete="off"
                                outlined
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
                <v-sheet height="30vh" v-if="index > 0" class="overflow-y-auto scroller my-1 mb-5 px-2" id="signup_wrapper">
                    <v-form :ref="`custom_registration_page_${index}`" v-model="valid[index]">
                        <section v-for="(field, p) in contents[index-1].custom_registration_fields" :key="p" class="d-flex flex-column mb-3">
                            
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
                                autocomplete="off"
                                outlined
                                dense
                                hide-details="auto"
                                :type="field.input_field_type"
                                class="general-custom-field roboto f14 fw500 secondary-2--text"
                                v-model="formData.data[contents[index-1].title][field.input_field_name]"
                                :rules="[() => !!formData.data[contents[index-1].title][field.input_field_name] || 'This field is required']"
                            ></v-text-field>
                            <v-text-field
                                v-else-if="field.input_field_type === 'password' && !field.is_required"
                                autocomplete="off"
                                outlined
                                dense
                                hide-details="auto"
                                :type="field.input_field_type"
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
                        I have read Aster Academy's <span class="primary--text pointer-cursor" @click="privacy = true"><b>Data Privacy Statement</b></span> and express my consent for Aster Academy to collect and process the data herein for the purpose of this course.
                    </div>
                </section>

                <section v-if="index === 0" class="d-flex flex-row align-center fw400 mb-3">
                    <v-checkbox
                        dense
                        v-model="checked2"/>
                    <div class="fw400 f10">
                        I understand that my personal information is protected by R.A. 10173 or the Data Privacy Act of 2012.
                    </div>
                </section>
                
                <v-btn
                    color="primary"
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
                    color="primary"
                    style="border-radius: 10px"
                    outlined  @click="index !== 0 && index--" dense>
                    Back
                </v-btn>
                <v-btn 
                    :disabled="!checked1 || !checked2" color="primary" class="f12 white--text" style="border-radius: 10px" :loading="loadingRegister"
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
            
            <div class="primary-font text-center secondary--text f12">
              Already have an account ?
              <router-link to="/login" class="text-decoration-none">
                  <span class="primary--text">Login</span> 
              </router-link>
            </div>
          </v-container>  
          <circular v-else/>
          <AuthFooter v-if="!loading"/>
        </v-sheet>
             
      </v-col>
      <v-col cols="12" lg="4" class="hidden-md-and-down">
        <v-carousel v-if="Object.keys(customization).filter(_key => _key.includes('side_carousel')).length > 0" hide-delimiters cycle :show-arrows="false" height="100vh">
          <v-carousel-item :key="index" v-for="(item, index) in Object.keys(customization).filter(_key => _key.includes('side_carousel'))">
            <v-sheet
              color="transparent"
              height="100vh"
              class="d-flex align-center justify-center"
            >
            <v-img
                height="100vh"
                :src="customization[item]"
              />
            </v-sheet>
          </v-carousel-item>
        </v-carousel>
        <v-sheet v-else
          :color="customization.hasOwnProperty('sidebar_color') ? (customization.sidebar_color ? customization.sidebar_color : 'primary') : 'primary'" height="100vh" class="d-flex justify-center align-center">
            <v-img 
              contain
              max-width="250"
              id="vertical_logo"
              v-if="customization.hasOwnProperty('sidebar_logo')"
              :src="customization.sidebar_logo"
              v-on:error="delete customization.sidebar_logo"
              transition="scale-transition"
            />
            <v-icon v-else size="160" color="#fff">mdi-account-tie-outline</v-icon>
        </v-sheet>
      </v-col>
    </v-row>
    <PrivacyDialogVue :privacy="privacy" @close="privacy = false"/>
  </v-app>
</template>

<script>
import { mapMutations, mapState, mapActions } from 'vuex'
import AuthFooter from '../components/AuthFooter.vue'
import EmailVertificationDialog from './EmailVertificationDialog.vue'
import PrivacyDialogVue from '@/components/reusable/PrivacyDialog.vue';
import FieldLabel from '@/components/reusable/forms/FieldLabel.vue'
import PasswordRules from '@/components/reusable/forms/PasswordRules.vue'

export default {
  components: {
    AuthFooter,
    EmailVertificationDialog,
    PrivacyDialogVue,
    FieldLabel,
    PasswordRules
  },
  data: () => ({
    valid: [],
    loading: false,
    index: 1,
    headers: ['Account Info'],
    contents: [],
    formData: {
      first_name: null,
      last_name: null, 
      middle_name: null,
      suffix: null,
      email: null,
      password: null,
      data: {},
      role: 'USER'
    },
    retyped: null,
    data: [],
    showpw: false,
    showretyped: false,
    loadingRegister: false,
    loadingResend: false,
    dialog: false,
    registerSuccess: false,
    asset_path: `${process.env.VUE_APP_ASSET_PATH}`,
    landing_path: `${process.env.VUE_APP_LANDING_DOMAIN}`,
    images: {},
    msg: {
      show: false,
      text: '',
      type: ''
    },
    index: 0,
    checked1: false,
    checked2: false,
    privacy: false
  }),
  mounted() {
    this.loading = true
    this.getCustomizationAction().then(res => {
      this.images = res
      this.$api.get('/tenants/custom-registration/pages').then(res=>{
        res.data.data.forEach(page => {
          this.headers.push(page.title)
          this.contents.push(page)
          
          let _fields = {}
          page.custom_registration_fields.forEach(field => {
              _fields[field.input_field_name] = null
          })

          this.formData.data[page.title] = _fields

        })
        this.loading = false
      })
    })
  },
  methods: {
    ...mapMutations(['tenantMutation', 'customizationMutation', 'signInToken']),
    ...mapActions(['getCustomizationAction', 'registerUserAction', 'resendverificationMailAction', 'loginUserAction']),
    ...mapActions('usr', ['addToCart']),

    scrollToTop() {
        let signup_wrapper = document.getElementById('signup_wrapper')
        signup_wrapper.scrollTop = 0
    },

    registerUser(){
      this.loadingRegister = true
      this.registerUserAction({form: this.formData}).then(res => {
        localStorage.setItem('access_payload_register', res.access_token)
        setTimeout(() => {
          this.login()
        }, 1000)
      }).catch((err) => {
        this.errors = err
        if(this.errors.email || this.errors.password || this.errors.status) {
          this.index = 0
        }
        this.loadingRegister = false
      })
    },

    login() {
      this.loginUserAction({form: this.formData, token: localStorage.getItem('access_payload_register')}).then(res => {
        this.signInToken(res)
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
                          this.success()
                        }, 2000)
                    })
                } else {
                  localStorage.removeItem('cart')
                  this.success()
                }
            } else {
              this.success()
            }
        }
      }).catch(() => {
        this.loadingRegister = false
      })
    },

    success() {
      this.retyped = null
      this.loadingRegister = false
      this.dialog = true
      this.registerSuccess = false
      this.resetForm()
      this.index = 0
    },

    toUpper(index) {
      let name = this.formData[index].split(" ");
      let new_name = [];
      name.forEach(item => {
          new_name.push(item.charAt(0).toUpperCase() + item.slice(1));
      });
      return this.formData[index] = new_name.join(" ");
    },

    resend(){
      this.loadingResend = true
      this.msg = {
        show: false,
        text: '',
        type: ''
      }
      
      this.resendverificationMailAction({ 
        token: JSON.parse(localStorage.getItem('access_payload')).token, 
      }).then(res => {
        this.loadingResend = false
        this.msg = {
          show: true,
          text: res,
          type: "success"
        }
      }).catch(e => {
        this.loadingResend = false
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
                this.formData.data[tbl.title][field.input_field_name] = ''
            })
        })
    },
  },
  computed: {
    ...mapState({
      tenant: (state) => state.tenant,
      customization: (state) => state.customization,
      errors: (state) => state.errors
    }),
  }
}
</script>

<!-- <style>
.v-stepper__step {
  padding: 10px !important;
}
.v-stepper__content {
padding: 10px !important;
}
.v-stepper__header {
height: fit-content !important
}
/* .v-stepper__wrapper > .content-footer {
  box-shadow: 0px 3px 1px -2px rgb(0 0 0), 0px 2px 2px 0px rgb(0 0 0), 0px 1px 5px 0px rgb(0 0 0);
} */
</style> -->