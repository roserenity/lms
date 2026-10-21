<template>
    <v-dialog v-model="dialog" persistent max-width="80%" max-height="300">
        <v-card class="pa-5" >
            <v-progress-linear
                v-if="loading"
                    indeterminate
                    color="primary"
                    rounded
                    height="4"
                    class="my-3"
            ></v-progress-linear>

            <section v-if="!loading">
                <div class="poppins f18 fw1200 d-flex pb-2">

                    <div class="d-flex align-center ml-2 mr-auto">
                        <span class="poppins f14 fw500 text-center"> Update User: <span class="text-capitalize primary--text">{{ form.first_name ? form.first_name : 'User' }} {{ form.last_name ? form.last_name : 'Account' }} {{ form.suffix ? form.suffix : '' }}</span></span>
                    </div>

                    <v-btn class="ml-auto" icon small @click="onEdit = false, $emit('close')">
                        <v-icon size="18">mdi-close</v-icon>
                    </v-btn>
                </div>
                <!-- <v-tabs class="tabs px-0" color="primary" v-if="user.role === 'USER'" v-model="tab" background-color="transparent">
                    <v-tab v-for="item in ['PROFILE', 'COURSES']"
                    :key="item" 
                    active-class="active"
                    class="primary-font f14 fw600">
                        {{item}}
                    </v-tab>
                </v-tabs> -->
                <v-tabs-items v-model="tab" style="background-color: transparent" class="px-3">
                    <v-tab-item v-for="item in ['PROFILE', 'COURSES']" :key="item">
                        <section v-if="item === 'PROFILE'">
                            <v-form ref="updateInfoForm">
                                <div class="mb-5">
                                    <section class="my-3">
                                        <label class="caption">FIRST NAME</label>
                                        <v-text-field v-model="form.first_name" outlined dense hide-details="auto"  
                                        class="general-custom-field roboto f14 secondary-1--text fw500" 
                                        :rules="[() => !!form.first_name || 'This field is required']"
                                        @keyup="form.first_name = form.first_name.charAt(0).toUpperCase() + form.first_name.slice(1)"/>
                                    </section>
                                    <section class="my-3">
                                        <label class="caption">MIDDLE NAME</label>
                                        <v-text-field v-model="form.middle_name" outlined dense hide-details="auto"  
                                        class="general-custom-field roboto f14 secondary-1--text fw500" 
                                        @keyup="form.middle_name = form.middle_name.charAt(0).toUpperCase() + form.middle_name.slice(1)"/>
                                    </section>
                                    <section class="my-3">
                                        <label class="caption">LAST NAME</label>
                                        <v-text-field v-model="form.last_name" outlined dense hide-details="auto"
                                        class="general-custom-field roboto f14 secondary-1--text fw500"
                                        :rules="[() => !!form.last_name || 'This field is required']"
                                        @keyup="form.last_name = form.last_name.charAt(0).toUpperCase() + form.last_name.slice(1)"/>
                                    </section>
                                    <section class="my-3">
                                        <label class="caption">SUFFIX</label>
                                        <v-text-field v-model="form.suffix" outlined dense hide-details="auto"  
                                        class="general-custom-field roboto f14 secondary-1--text fw500"
                                        @keyup="form.suffix = form.suffix.charAt(0).toUpperCase() + form.suffix.slice(1)"/>
                                    </section>
                                    <section class="my-3">
                                        <label class="caption">USER TYPE</label>
                                        <v-select v-model="form.role" :items="items" outlined dense hide-details="auto" 
                                        class="general-custom-field roboto f14 secondary-1--text fw500 text-capitalize"/>
                                    </section>
                                    <section class="my-3">
                                        <label class="caption">EMAIL ADDRESS</label>
                                        <v-text-field v-model="form.email" outlined dense hide-details="auto" type="email"
                                        :rules="[() => 
                                        !!form.email || 'This field is required',
                                        v => /.+@.+\..+/.test(v) || 'E-mail must be valid',]"
                                        class="general-custom-field roboto f14 secondary-1--text fw500" readonly/>
                                    </section>
                                    <section v-for="(tbl,  j) in registration_tables">
                                        <section v-if="form.data && form.data.hasOwnProperty(tbl.title)">
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
                                                    class="roboto f14 fw500 secondary-2--text"
                                                    hide-details="auto"
                                                />
                                                <v-select
                                                    v-else-if="column.input_field_type === 'dropdown' && form.data"
                                                    :items="column.data"
                                                    outlined
                                                    dense
                                                    :multiple="column.is_multiple"
                                                    class="general-custom-field roboto f14 fw500 secondary-2--text"
                                                    v-model="form.data[tbl.title][column.input_field_name]"
                                                    hide-details="auto"
                                                    :rules="column.is_required && [v => !!v || 'This is required']"
                                                >
                                                </v-select>
                                                <v-select
                                                    v-else-if="column.input_field_type === 'dropdown' && !form.data"
                                                    :items="column.data"
                                                    outlined
                                                    dense
                                                    :multiple="column.is_multiple"
                                                    class="general-custom-field roboto f14 fw500 secondary-2--text"
                                                    hide-details="auto"
                                                    :rules="column.is_required && [v => !!v || 'This is required']"
                                                >
                                                </v-select>
                                                <v-text-field 
                                                    v-else-if="form.data"
                                                    :type="column.input_field_type"
                                                    outlined
                                                    dense
                                                    :readonly="column.input_field_type !== 'date' && type=='preview'"
                                                    class="general-custom-field roboto f14 fw500 secondary-2--text"
                                                    v-model="form.data[tbl.title][column.input_field_name]"
                                                    hide-details="auto"
                                                    :rules="column.is_required && [v => !!v || 'This is required']"
                                                />
                                                <v-text-field 
                                                    v-else-if="!form.data"
                                                    :type="column.input_field_type"
                                                    outlined
                                                    dense
                                                    :readonly="column.input_field_type !== 'date' && type=='preview'"
                                                    class="general-custom-field roboto f14 fw500 secondary-2--text"
                                                    hide-details="auto"
                                                    :rules="column.is_required && [v => !!v || 'This is required']"
                                                    v-model="form.data[tbl.title][column.input_field_name]"
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
                                                    class="roboto f14 fw500 secondary-2--text"
                                                    hide-details="auto"
                                                />
                                                <v-select
                                                    v-else-if="column.input_field_type === 'dropdown'"
                                                    :items="column.data"
                                                    outlined
                                                    dense
                                                    :multiple="column.is_multiple"
                                                    class="general-custom-field roboto f14 fw500 secondary-2--text"
                                                    hide-details="auto"
                                                    :rules="column.is_required && [v => !!v || 'This is required']"
                                                    @change="updateData(tbl.title, column.input_field_name, $event)"
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
                                <v-card-action class=" d-flex align-end">
                                    <v-btn color="primary" class="text-capitalize f12 fw1200" :loading="updateLoading" @click="updateInfo">
                                        UPDATE
                                    </v-btn>
                                </v-card-action>
                            </v-form>
                        </section>
                        <section v-if="item === 'COURSES'">
                            
                            <v-alert
                                v-if="msg.show"
                                dense
                                :type="msg.type"
                                class="f12 poppins"
                                transition="scale-transition"
                                >
                                {{ msg.text }}
                            </v-alert>
                            <div v-if="!addCourses" class=" pb-5">
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
                                    <section v-if="user">
                                        <v-list dense>
                                            <v-list-item
                                                v-for="enrollment in user.enrollments"
                                                :key="enrollment.id"
                                                class="poppins d-flex align-center px-0"
                                                dense
                                            >
                                                <v-list-item-content>
                                                    <v-list-item-title> {{ enrollment.course.title ? enrollment.course.title : 'Untitled' }}</v-list-item-title>
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
                                <div>
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
                                                        <v-checkbox :ripple="false" :input-value="active"></v-checkbox>
                                                    </v-list-item-action>
                                                    <v-list-item-content>
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
                                            <v-select 
                                                outlined 
                                                dense 
                                                :items="class_courses"
                                                v-model="coursesForm.course_id"
                                                item-text="title"
                                                item-value="id"
                                                hide-details="auto"
                                                multiple
                                                class="roboto f14"
                                            >
                                            </v-select>
                                        </section>
                                    </v-form>
                                    <section class="d-flex align-center justify-end mt-5">
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
                

            </section>
        </v-card>
    </v-dialog>
  </template>

<script>
import { mapActions, mapState } from 'vuex'
export default {
props: ['dialog', 'item'],
    data: () => ({
        items: ['USER', 'INSTRUCTOR', 'ADMIN'],
        form : {
            id: '',
            first_name: '',
            middle_name: '',
            last_name: '',
            suffix: '',
            email: '',
            class_category_id: '',
            role: '',
            data: {}
        },
        coursesForm : {
            class_category_id: [],
            course_id: [],
        },
        msg: {
            show: false,
            type: '',
            text: ''
        },
        user: null,
        loading: false,
        updateLoading: false,
        coursesLoading: false,
        updateCoursesLoading: false,
        unenrollCoursesLoading: false,
        data: {},
        tab: 0,
        addCourses: false,
    }),
    mounted() {
        this.tab = 0
        this.loading = true
        this.$api.get(`/admin/enrollments/user/${this.item.id}`).then((res)=>{
            this.user = res.data.data
            this.role = res.data.data.role
            this.form.id = this.item.id
            this.form.first_name = this.item.first_name
            this.form.middle_name = this.item.middle_name
            this.form.last_name = this.item.last_name
            this.form.suffix = this.item.suffix
            this.form.email = this.item.email
            this.form.role = this.item.role
            this.form.data = this.item.data
        }).finally(() => {
            this.loading = false
        })
    },
    computed: {
        ...mapState('admin', {
            //courses: (state) => state.courses,
            registration_tables: (state) => state.registration_tables,
            classes: (state) => state.classes,
        }),
        ...mapState({
            tenant: (state) => state.tenant
        }),
        
        class_courses(){
            let _courses = []

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
            if(!val) {
                this.item.id = this.form.id
                this.item.first_name = this.form.first_name
                this.item.middle_name = this.form.middle_name
                this.item.last_name = this.form.last_name
                this.item.suffix = this.form.suffix
                this.item.email = this.form.email
                this.item.role = this.form.role
                this.item.class_category_id = this.form.class_category_id
            } else {
                this.form.id = this.item.id
                this.form.first_name = this.item.first_name
                this.form.middle_name = this.item.middle_name
                this.form.last_name = this.item.last_name
                this.form.suffix = this.item.suffix
                this.form.email = this.item.email
                this.form.role = this.item.role
                this.form.class_category_id = this.item.class_category_id
            }

            this.addCourses = false
            this.coursesForm.class_category_id = []
            this.coursesForm.course_id = []
        },

        tab(val) {
            if(val === 1) {
                this.getCourses()
            }

            this.addCourses = false
            this.coursesForm.class_category_id = []
            this.coursesForm.course_id = []
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
            'updateUserDetails'
        ]),

        getCourses(){
            this.coursesLoading = false
            this.$api.get(`/admin/enrollments/user/${this.item.id}`).then((res)=>{
                this.user = res.data.data
            }).finally(() => {
                this.coursesLoading = false
            })
        },

        updateData(title, column, value){
            if(this.data.hasOwnProperty('title')) {
                this.data = this.data[title][column] = value
            } else {
                this.data = { 
                    ...this.data,
                    [title] : {
                        [column]: value
                    }
                }
            }

            this.form.data = { ...this.form.data, ...this.data}
        },

        updateInfo(){
            if(this.$refs.updateInfoForm[0].validate()) {
                this.updateLoading = true
                this.updateUserDetails(this.form).then(()=>{
                    this.$store.commit('alertMutation', {
                        show: true,
                        text: `User Details Successfully Updated`,
                        type: "success"
                    })
                    this.$emit('close')
                }).finally(() => {
                    this.updateLoading = false
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
                    this.unenrollCoursesLoading = false
                    this.msg = {
                        show: true,
                        text: 'Successfully unenrolled user to course.',
                        type: 'success'
                    }
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

        updateUser(){
            this.updateCoursesLoading = true
            this.$api.post(`/admin/enrollments/user/${this.item.id}`, { course_id: this.coursesForm.course_id, class_category_id: this.coursesForm.class_category_id}).then((res)=>{
                this.successUpdated = true
                this.msg = {
                    show: true,
                    text: 'Successfully enrolled',
                    type: 'success'
                }
                setTimeout(()=>{
                    this.updateCoursesLoading = false
                    this.msg = {
                        show: false,
                        text: '',
                        type: ''
                    }
                    this.addCourses = false
                }, 2000)
            }).catch(e => {
                this.updateCoursesLoading = false
                this.msg = {
                    show: true,
                    text: 'Something went wrong.',
                    type: 'error'
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
        }
    }
}
</script>