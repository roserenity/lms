<template>
    <v-dialog v-model="dialog" persistent max-width="550" max-height="300">
        <v-card>
            <!-- <div class="poppins f18 fw1200 d-flex justify-end px-2 py-3">
                <v-btn icon small @click="$emit('close')">
                <v-icon size="18">mdi-close</v-icon>
                </v-btn>
            </div> -->
            <div class="poppins f18 fw1200 d-flex px-5 pt-5 pb-2">

                <div class="d-flex align-center ml-2 mr-auto">
                    <span class="poppins f14 fw500 text-center"> {{ type=='preview' ? 'Preview User :' : 'Update User :'  }} <span class="text-capitalize primary--text">{{ item.first_name ? item.first_name : 'User' }} {{ item.last_name ? item.last_name : 'Account' }} {{ item.suffix ? item.suffix : '' }}</span></span>
                </div>

                <!-- <div class="d-flex align-center ml-3 mr-auto" v-if="type=='preview'">
                    <span class="secondary--text poppins f14 fw500 text-center">Preview User : <span class="text-capitalize secondary-2--text">{{ item.first_name ? item.first_name : 'User' }} {{ item.last_name ? item.last_name : 'Account' }}</span></span>
                </div>
                <div class="d-flex align-center ml-3 mr-auto" v-if="type=='update'">
                    <span class="secondary--text poppins f14 fw500 text-center">Update User : <span class="text-capitalize secondary-2--text">{{ item.first_name ? item.first_name : 'User' }} {{ item.last_name ? item.last_name : 'Account' }}</span></span>
                </div> -->
                
                <v-btn class="ml-auto" icon small @click="onEdit = false, $emit('close')">
                    <v-icon size="18">mdi-close</v-icon>
                </v-btn>
            </div>

            <Tabs v-if="item.role === 'USER'" :value.sync="tab" :tabs="['PROFILE', 'COURSES']" class="px-5"/>

            <v-tabs-items v-model="tab" style="background-color: transparent" class="px-3">
                <v-tab-item v-for="i in ['PROFILE', 'COURSES']" :key="i">
                    <v-form ref="form" v-if="i === 'PROFILE'">
                        <v-card-text>
                            <div class="mb-5">
                                <v-row class="mb-5">
                                    <v-col cols="12">
                                        <label class="caption">FIRST NAME</label>
                                        <v-text-field v-model="item.first_name" outlined dense hide-details="auto"  
                                        class="general-custom-field roboto f14 secondary-1--text fw500" 
                                        :readonly="type=='preview'"
                                        :rules="[() => !!item.first_name || 'This field is required']"
                                        @keyup="item.first_name = item.first_name.charAt(0).toUpperCase() + item.first_name.slice(1)"/>
                                    </v-col>
                                    <v-col cols="12">
                                        <label class="caption">MIDDLE NAME</label>
                                        <v-text-field v-model="item.middle_name" outlined dense hide-details="auto"  
                                        class="general-custom-field roboto f14 secondary-1--text fw500" 
                                        :readonly="type=='preview'"
                                        @keyup="item.middle_name = item.middle_name.charAt(0).toUpperCase() + item.middle_name.slice(1)"/>
                                    </v-col>
                                    <v-col cols="12">
                                        <label class="caption">LAST NAME</label>
                                        <v-text-field v-model="item.last_name" outlined dense hide-details="auto"
                                        class="general-custom-field roboto f14 secondary-1--text fw500"
                                        :readonly="type=='preview'"
                                        :rules="[() => !!item.last_name || 'This field is required']"
                                        @keyup="item.last_name = item.last_name.charAt(0).toUpperCase() + item.last_name.slice(1)"/>
                                    </v-col>
                                    <v-col cols="12">
                                        <label class="caption">SUFFIX</label>
                                        <v-text-field v-model="item.suffix" outlined dense hide-details="auto"  
                                        class="general-custom-field roboto f14 secondary-1--text fw500"
                                        :readonly="type=='preview'"
                                        @keyup="item.suffix = item.suffix.charAt(0).toUpperCase() + item.suffix.slice(1)"/>
                                    </v-col>
                                    <v-col cols="12">
                                        <label class="caption">USER TYPE</label>
                                        <v-select v-model="item.role" :items="items" outlined dense hide-details="auto" 
                                        :readonly="type=='preview'"
                                        class="general-custom-field roboto f14 secondary-1--text fw500 text-capitalize"/>
                                    </v-col>
                                        <v-col cols="12">
                                        <label class="caption">EMAIL ADDRESS</label>
                                        <v-text-field v-model="item.email" outlined dense hide-details="auto" type="email"
                                        :rules="[() => 
                                        !!item.email || 'This field is required',
                                        v => /.+@.+\..+/.test(v) || 'E-mail must be valid',]"
                                        class="general-custom-field roboto f14 secondary-1--text fw500" readonly/>
                                    </v-col>
                                </v-row>
                                <section v-for="(tbl,  j) in registration_tables">
                                    <section v-if="item.data && item.data.hasOwnProperty(tbl.title)">
                                        <v-divider class="my-5"></v-divider>
                                        <div class="poppins f14 fw600 secondary-2--text">
                                            {{ tbl.title }}
                                        </div>
                                        <div v-for="(column, i) in tbl.custom_registration_fields" :key="i" class="my-3 d-flex flex-column">
                                            <label class="caption text-uppercase"> {{column.input_field_label}} <span v-if="column.is_required">*</span></label>
                                            <input
                                                v-if="column.input_field_type === 'file'"
                                                :type="column.input_field_type"
                                                dense
                                                :readonly="type=='preview'"
                                                class="roboto f14 fw500 secondary-2--text"
                                                hide-details="auto"
                                            />
                                            <v-select
                                                v-else-if="column.input_field_type === 'dropdown' && item.data"
                                                :items="column.data"
                                                outlined
                                                :readonly="type=='preview'"
                                                dense
                                                :multiple="column.is_multiple"
                                                class="general-custom-field roboto f14 fw500 secondary-2--text"
                                                v-model="item.data[tbl.title][column.input_field_name]"
                                                hide-details="auto"
                                                :rules="column.is_required && [v => !!v || 'This is required']"
                                            >
                                            </v-select>
                                            <v-select
                                                v-else-if="column.input_field_type === 'dropdown' && !item.data"
                                                :items="column.data"
                                                outlined
                                                :readonly="type=='preview'"
                                                dense
                                                :multiple="column.is_multiple"
                                                class="general-custom-field roboto f14 fw500 secondary-2--text"
                                                hide-details="auto"
                                                :rules="column.is_required && [v => !!v || 'This is required']"
                                            >
                                            </v-select>
                                            <v-text-field 
                                                v-else-if="item.data"
                                                :type="column.input_field_type"
                                                outlined
                                                dense
                                                :readonly="column.input_field_type !== 'date' && type=='preview'"
                                                class="general-custom-field roboto f14 fw500 secondary-2--text"
                                                v-model="item.data[tbl.title][column.input_field_name]"
                                                hide-details="auto"
                                                :rules="column.is_required && [v => !!v || 'This is required']"
                                            />
                                            <v-text-field 
                                                v-else-if="!item.data"
                                                :type="column.input_field_type"
                                                outlined
                                                dense
                                                :readonly="column.input_field_type !== 'date' && type=='preview'"
                                                class="general-custom-field roboto f14 fw500 secondary-2--text"
                                                hide-details="auto"
                                                :rules="column.is_required && [v => !!v || 'This is required']"
                                                v-model="item.data[tbl.title][column.input_field_name]"
                                            />
                                            </div>
                                    </section>
                                    <section v-else>
                                        <v-divider class="my-5"></v-divider>
                                        <div class="poppins f14 fw600 secondary-2--text">
                                            {{ tbl.title }}
                                        </div>
                                        <div v-for="(column, i) in tbl.custom_registration_fields" :key="i" class="my-3 d-flex flex-column">
                                            <label class="caption text-uppercase"> {{column.input_field_label}} <span v-if="column.is_required">*</span></label>
                                            <input
                                                v-if="column.input_field_type === 'file'"
                                                :type="column.input_field_type"
                                                dense
                                                :readonly="type=='preview'"
                                                class="roboto f14 fw500 secondary-2--text"
                                                hide-details="auto"
                                            />
                                            <v-select
                                                v-else-if="column.input_field_type === 'dropdown'"
                                                :items="column.data"
                                                outlined
                                                :readonly="type=='preview'"
                                                dense
                                                :multiple="column.is_multiple"
                                                class="general-custom-field roboto f14 fw500 secondary-2--text"
                                                hide-details="auto"
                                                :rules="column.is_required && [v => !!v || 'This is required']"
                                            >
                                            </v-select>
                                            <v-text-field 
                                                v-else
                                                :type="column.input_field_type"
                                                outlined
                                                dense
                                                :readonly="column.input_field_type !== 'date' && type=='preview'"
                                                class="general-custom-field roboto f14 fw500 secondary-2--text"
                                                hide-details="auto"
                                                :rules="column.is_required && [v => !!v || 'This is required']"
                                                v-on:input="updateData(tbl.title, column.input_field_name, $event)"
                                            />
                                            </div>
                                    </section>
                                </section>
                            </div>
                            
                            <ButtonPrimary
                                v-if="item.role === 'USER'"
                                :icon="'mdi-bell-outline'"
                                @click="requestInfoUpdate"
                                :label="'Request Info Update'"
                                :loading="infoUpdateLoading"
                            />
                        </v-card-text>
                    </v-form>

                    <section v-if="i === 'COURSES'">
                        <v-alert
                            v-if="msg.show"
                            dense
                            :type="msg.type"
                            class="f12 fw500 poppins mx-5"
                            transition="scale-transition"
                            >
                            {{ msg.text }} 
                        </v-alert>
                        <div v-if="!addCourses" class=" pb-5 px-5">
                            <v-subheader class=" pa-0 d-flex flex-row justify-space-between">
                                <label class="caption poppins">ENROLLED COURSES</label>
                                <v-btn text dense @click="addCourses = true" color="primary" class="text-capitalize f12 fw1200">
                                    <v-icon left>
                                        mdi-plus-circle-outline
                                    </v-icon>
                                    ADD COURSES
                                </v-btn>
                            </v-subheader>
                            <v-divider />
                            <v-progress-linear
                                v-if="unenrollCoursesLoading"
                                    indeterminate
                                    color="primary"
                                    class="mb-3"
                                    rounded
                                    height="4"
                                ></v-progress-linear>
                            <v-sheet class="overflow-y-auto scroller" height="300">
                                <v-progress-linear
                                    v-if="coursesLoading"
                                        indeterminate
                                        color="primary"
                                        class="mb-3"
                                        rounded
                                        height="4"
                                    ></v-progress-linear>
                                <section v-if="item">
                                    <v-list dense>
                                        <v-list-item
                                            v-for="enrollment in item.enrollments"
                                            :key="enrollment.id"
                                            class="poppins d-flex align-center px-0"
                                            dense
                                        >
                                            <v-list-item-content>
                                                <v-list-item-title class="primary--text cursor-pointer" @click="openCourse(enrollment.course)"> {{ enrollment.course.title ? enrollment.course.title : 'Untitled' }}</v-list-item-title>
                                            </v-list-item-content>

                                            <v-list-item-icon>
                                                <v-btn icon @click="removeCourse(enrollment)">
                                                    <v-icon small color="grey">
                                                        mdi-close
                                                    </v-icon>
                                                </v-btn>
                                            </v-list-item-icon>
                                        </v-list-item>
                                    </v-list>
                                </section>
                            </v-sheet>
                        </div>

                        <v-form ref="form" v-if="addCourses">
                            <div class=" px-5">
                                <v-form ref="form">
                                    <section class="my-3">
                                        <label class="caption">CLASS CATEGORIES</label>
                                        <v-select 
                                            outlined 
                                            dense 
                                            :items="classes"
                                            v-model="coursesForm.class_category_id"
                                            item-value="id"
                                            hide-details="auto"
                                            multiple
                                        >
                                            <template v-slot:item="{item, attrs, on}">
                                                <v-list-item v-on="on" v-bind="attrs" #default="{ active }">
                                                <v-list-item-action>
                                                    <v-checkbox :ripple="false" dense hide-details :input-value="active"></v-checkbox>
                                                </v-list-item-action>
                                                <v-list-item-content dense>
                                                    <v-list-item-title class="roboto f14 fw500">
                                                    <span class="secondary--text">{{item.class_code}}-</span>
                                                    <span class="secondary-3--text">{{item.class_name}}</span>
                                                    </v-list-item-title>
                                                </v-list-item-content>
                                                </v-list-item>
                                            </template>
                                            <template slot="selection"  slot-scope="data">
                                                <div class="roboto f14 fw500 my-1">
                                                <span class="secondary--text">{{data.item.class_code}}-</span>
                                                <span class="secondary-3--text">{{data.item.class_name}}, </span>
                                                </div>
                                            </template>
                                        </v-select>
                                    </section>
                                    <section class="my-3">
                                        <label class="caption">COURSES</label>
                                        <v-autocomplete
                                            outlined 
                                            dense 
                                            :items="class_courses"
                                            v-model="coursesForm.course_id"
                                            :filter="filterData"
                                            color="primary"
                                            auto-select-first
                                            clearable
                                            placeholder="Search course"
                                            item-text="title"
                                            item-value="id"
                                            hide-details="auto"
                                            multiple
                                            class="roboto f14"
                                        >
                                            <template v-slot:item="{item, attrs, on}">
                                                <v-list-item v-on="on" v-bind="attrs" #default="{ active }">
                                                    <v-list-item-action>
                                                        <v-checkbox :ripple="false" dense hide-details :input-value="active"></v-checkbox>
                                                    </v-list-item-action>
                                                    <v-list-item-content dense>
                                                        <v-list-item-title class="roboto">
                                                            <div class="f10 poppins secondary-2--text text-uppercase ">
                                                                {{ item.status }}ED &nbsp; | &nbsp; {{  item.type_of_learning }} &nbsp; | &nbsp; {{  item.price == 0 ? 'FREE' : item.price }} 
                                                                <span v-if="item.available_until">
                                                                    &nbsp; |
                                                                    <span v-if="item.available_until.start_date"> {{ $dateFormat.mmDDyy(item.available_until.start_date) }} </span>
                                                                    <span v-if="item.available_until.end_date"> until {{ $dateFormat.mmDDyy(item.available_until.end_date) }} </span>
                                                                </span>
                                                            </div>
                                                            <span class="text-capitalize f14">{{item.title}}</span>
                                                        </v-list-item-title>
                                                    </v-list-item-content>
                                                </v-list-item>
                                            </template>
                                        </v-autocomplete>
                                    </section>

                                    <FormSelectObject
                                        :label="'TYPE OF PAYMENT'"
                                        :items="type_of_payment"
                                        :value.sync="coursesForm.type_of_payment"
                                        class="mb-3"
                                    />

                                    <UploadCommitmentLetter 
                                        v-if="['commitment_letter'].includes(coursesForm.type_of_payment)"
                                        :form="true"
                                        @setCommitmentLetter="(e) => commitmentLetter = e"
                                        :error="errors.commitment_letter && errors.commitment_letter[0]"
                                        class="mb-3"
                                        :info="false"
                                    />

                                    <FormSelectObject
                                        :label="'TYPE OF PARTICIPANT'"
                                        :items="type_of_participant"
                                        :value.sync="coursesForm.type_of_participant"
                                        class="mb-3"
                                    />

                                    <UploadPrimeRequirements
                                        v-if="['regular_payee_hrm'].includes(coursesForm.type_of_participant)"
                                        :form="true"
                                        @setEndorsementLetter="(e) => endorsementLetter = e"
                                        @setPrimeResolution="(e) => primeResolution = e"
                                        :error_endorsement_letter="errors.endorsement_letter && errors.endorsement_letter[0]"
                                        :error_prime_resolution="errors.prime_resolution && errors.prime_resolution[0]"
                                    />

                                    <!-- <div class="d-flex align-center">
                                        <v-checkbox v-model="own_account" />
                                        <label class="caption">Is the learner's agency responsible for the payment?</label>
                                    </div>

                                    <UploadNomination
                                        v-if="own_account"
                                        @setNominationForm="(e) => nominationForm = e"
                                        :form="true"
                                        :error_nomination_form="errors.nomination_form && errors.nomination_form[0]"
                                        :error_proof_of_payment="errors.proof_of_payment && errors.proof_of_payment[0]"
                                    /> -->
                                    
                                </v-form>
                                <section class="d-flex align-center justify-end my-5">
                                    <v-btn text color="secondary" @click="addCourses = false" class="text-capitalize f12 fw1200">
                                        CANCEL
                                    </v-btn>
                                    <v-btn color="primary" @click="updateUser" :loading="updateCoursesLoading" :disabled="coursesForm.course_id.length === 0" class="text-capitalize f12 fw1200">
                                        UPDATE
                                    </v-btn>
                                </section>
                            </div>
                        </v-form>
                    </section>
                </v-tab-item>
            </v-tabs-items>
        </v-card>
    </v-dialog>
  </template>
  

<style>
.cursor-pointer {
cursor: pointer;
}
</style>

<script>
import UploadNomination from '@/components/user/course/tocart/UploadNomination.vue';
import UploadPrimeRequirements from '@/components/user/course/tocart/UploadPrimeRequirements.vue';
import UploadCommitmentLetter from '@/components/user/course/tocart/UploadCommitmentLetter.vue';

import { mapActions, mapState, mapMutations } from 'vuex'

export default {
    components: { UploadPrimeRequirements, UploadNomination, UploadCommitmentLetter },
    props: ['dialog', 'item', 'type'],
    data: () => ({
        addCourses: false,
        items: ['USER', 'INSTRUCTOR', 'ADMIN'],
        loading: false,
        data: {},
        tab: 0,
        search:'',
        coursesForm : {
            class_category_id: [],
            course_id: [],
            type_of_payment: null,
            type_of_participant: null
        },
        msg: {
            show: false,
            type: '',
            text: ''
        },
        coursesLoading: false,
        updateCoursesLoading: false,
        unenrollCoursesLoading: false,
        type_of_payment: [
            { text: '-', value: null },
            { text: 'CASH DEPOSIT', value: 'cash_deposit' },
            // { text: 'CASH-CASHIER', value: 'cash-cashier' },
            { text: 'CHECK DEPOSIT', value: 'check_deposit' },
            // { text: 'CHECK-CASHIER', value: 'check-cashier' },
            { text: 'FUND TRANSFER', value: 'fund_transfer' },
            { text: 'COMMITMENT LETTER (CL)', value: 'commitment_letter' },
            // { text: 'LDDAP-ADA', value: 'lddap_ada' },
        ], 
        type_of_participant: [
            { text: '-', value: null },
            { text: 'INTERNAL EMPLOYEE', value: 'internal_employee' },
            { text: 'REGULAR PAYEE', value: 'regular_payee' },
            { text: 'REGULAR PAYEE (Partner Awardee)', value: 'regular_payee_hrm' },
        ],
        own_account: false,
        primeResolution: null, 
        endorsementLetter: null,
        nominationForm: null,
        commitmentLetter: null,
        infoUpdateLoading: false
    }),
    mounted() {
    },
    computed: {
        ...mapState('admin', {
            courses: (state) => state.courses,
            registration_tables: (state) => state.registration_tables,
            classes: (state) => state.classes,
        }),

        ...mapState({
            tenant: (state) => state.tenant,
            errors: (state) => state.errors
        }),

        class_courses(){
            let _courses = []

            if(this.coursesForm.class_category_id.length === 0) {
                return this.courses
            }
            this.coursesForm.class_category_id.forEach((_class)=>{
                let _class_courses = this.classes.filter(item => item.id === _class)[0].courses //get courses for class
                if(_courses.length === 0){
                    _courses.push(..._class_courses)
                } else {
                    _courses.forEach(_courses_ => {
                        if(_class_courses.findIndex(item => item.id === _courses_.id) == -1) //get unique for all courses
                        {
                            _courses.push(_courses_)
                        }
                    })
                }
            })

            return _courses
        }
    },
    watch:{
        

        dialog(val) {
            this.addCourses = false
            this.coursesForm.class_category_id = []
            this.coursesForm.course_id = []
            this.coursesForm.type_of_payment = null
            this.coursesForm.type_of_participant = null
            this.own_account = false
            this.resetFiles()
        },

        tab(val) {
            this.addCourses = false
            this.coursesForm.class_category_id = []
            this.coursesForm.course_id = []
            this.coursesForm.type_of_payment = null
            this.coursesForm.type_of_participant = null
            this.own_account = false
            this.resetFiles()
        },

        addCourses(val) {
            if(!val) {
                this.form.class_category_id = []
                this.form.course_id = []
                this.loading = true
                this.$api.get(`/admin/enrollments/user/${this.item.id}`).then((res)=>{
                    this.user = res.data.data
                    this.loading = false
                }).catch(() => {
                    this.loading = false
                })
            }
        },
    },
    methods: {
        ...mapActions('admin', [
            'updateUserDetails',
            'updateUserDetailPromptAction'
        ]),

        ...mapMutations(['errorsMutation', 'alertMutation']),

        filterData(item, queryText) {
            return item.title.toLowerCase().includes(queryText.toLowerCase())
        },

        updateInfo(){
            if(this.$refs.form.validate()) {
                this.updateUserDetails(this.item).then(()=>{
                    this.$store.commit('alertMutation', {
                        show: true,
                        text: `User Details Successfully Updated`,
                        type: "success"
                    })
                    this.$emit('close')
                })
            }
        },

        removeCourse(payload){
            let _form = new FormData();

            _form.append("_method", 'DELETE');
            _form.append("user_id", payload.user_id);
            _form.append("course_id", payload.course_id);

            this.unenrollCoursesLoading = true
            this.$api.post(`/admin/enrollments/${payload.id}`, _form).then((res)=>{
                this.$api.get(`/admin/enrollments/user/${this.item.id}`).then((res)=>{
                    this.user = res.data.data
                    this.msg = {
                        show: true,
                        text: 'Successfully unenrolled user to course.',
                        type: 'success'
                    }
                    this.$emit('getUsers');
                    setTimeout(()=>{
                        this.unenrollCoursesLoading = false
                        this.msg = {
                            show: false,
                            text: '',
                            type: ''
                        }
                    }, 2000)
                }).catch(e => {
                    this.unenrollCoursesLoading = false
                })
            }).catch(e => {
                this.unenrollCoursesLoading = false
                this.msg = {
                    show: true,
                    text: 'Something went wrong.',
                    type: 'error'
                }
                setTimeout(()=>{
                    this.unenrollCoursesLoading = false
                    this.msg = {
                        show: false,
                        text: '',
                        type: ''
                    }
                }, 2000)
            })
        },
        
        requestInfoUpdate(){
            this.infoUpdateLoading = true

            this.updateUserDetailPromptAction(this.item.id).then(res => {
                this.infoUpdateLoading = false
                this.alertMutation({
                    show: true,
                    text: 'The system will now prompt learner to update their info.',
                    type: "success"
                })
            }).catch(e => {
                this.infoUpdateLoading = false
                this.alertMutation({
                    show: true,
                    text: 'Something went wrong',
                    type: "error"
                })
            })
        },

        resetFiles(){
            this.primeResolution = null
            this.endorsementLetter = null
            this.commitmentLetter = null
            this.nominationForm = null
        },

        updateUser(){
            this.updateCoursesLoading = true
            
            let _form = new FormData()

            if(Array.isArray(this.coursesForm.course_id)) {
                this.coursesForm.course_id.forEach((item, i) => {
                    _form.append(`course_id[${i}]`, item)
                })
            }
            _form.append('class_category_id', this.coursesForm.class_category_id)
            _form.append('type_of_payment', this.coursesForm.type_of_payment)
            _form.append('type_of_participant', this.coursesForm.type_of_participant ?? '')
            
            if(this.primeResolution) {
                _form.append('prime_resolution', this.primeResolution)
            }

            if(this.endorsementLetter) {
                _form.append('endorsement_letter', this.endorsementLetter)
            }
            
            if(this.nominationForm) {
                _form.append('nomination_form', this.nominationForm)
            }

            if(this.commitmentLetter) {
                _form.append('commitment_letter', this.commitmentLetter)
            }

            const config = { 
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'multipart/form-data',
                }
            }

            this.$api.post(`/admin/enrollments/user/${this.item.id}`, _form, config).then((res)=>{
                this.successUpdated = true
                this.msg = {
                    show: true,
                    text: res.data.message,
                    type: 'success'
                }
                setTimeout(()=>{
                    this.coursesForm.class_category_id = []
                    this.coursesForm.course_id = []
                    this.coursesForm.type_of_payment = null
                    this.coursesForm.type_of_participant = null
                    this.own_account = false
                    this.resetFiles()
                    this.updateCoursesLoading = false
                    this.msg = {
                        show: false,
                        text: '',
                        type: ''
                    }
                    this.addCourses = false
                }, 2000)
                this.$emit('getUsers')
            }).catch(err => {
                if(err.response.data.errors) {
                    this.errorsMutation(err.response.data.errors)
                }
                
                if(!this.errors.nomination_form || !this.errors.proof_of_payment || !this.errors.prime_resolution || !this.errors.endorsement_letter || !this.errors.commitment_letter) {
                    this.msg = {
                        show: true,
                        text: 'Something went wrong.',
                        type: 'error'
                    }
                }
                setTimeout(()=>{
                    this.updateCoursesLoading = false
                    this.msg = {
                        show: false,
                        text: '',
                        type: ''
                    }
                }, 2000)
            })
        },

        openCourse(course){
            window.open(`/admin/course/${course.uuid}/learn_more`,  '_blank')
        }
    }
}
</script>