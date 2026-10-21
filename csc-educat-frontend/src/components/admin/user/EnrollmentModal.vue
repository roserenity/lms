<template>
    <v-dialog v-model="dialog" persistent max-width="550">
        <v-card class="pa-5">
            
            <v-progress-linear
                v-if="loading"
                    indeterminate
                    color="primary"
                    rounded
                    height="4"
                    class="my-3"
            ></v-progress-linear>

            <section v-else>
                <v-row no-gutters>
                    <v-col>
                        <span class="poppins f14 fw500 text-center">Update User : <span class="text-capitalize primary--text">{{ item.first_name ? item.first_name : 'Learner' }} {{ item.middle_name ? $userFormat(item.middle_name) : '' }} {{ item.last_name ? item.last_name : 'Account' }} {{ item.suffix ? item.suffix : '' }}</span></span>
                    </v-col>
                    <v-col cols="1">
                        <v-btn icon @click="onEdit = false, $emit('close')">
                            <v-icon size="18">mdi-close</v-icon>
                        </v-btn>
                    </v-col>
                </v-row>

                <v-alert
                    v-if="msg.show"
                    dense
                    :type="msg.type"
                    class="f14 poppins my-3"
                    transition="scale-transition"
                    >
                    {{ msg.text }}
                </v-alert>

                <div class="pb-5">
                    <FormSelectArray 
                        :label="'ROLE *'"
                        :items="items"
                        :value.sync="role"
                        :loading="roleLoading"
                    />
                </div>

                <div v-if="user.role.toUpperCase() === 'USER'">
                    <div v-if="!addCourses" class=" pb-5">
                        <v-subheader class=" pa-0 poppins d-flex flex-row justify-space-between">
                            <FormLabel :label="'COURSES'" />
                            <v-btn text dense @click="addCourses = true" color="primary" >
                                <v-icon left>
                                    mdi-plus-circle-outline
                                </v-icon>
                                Add Courses
                            </v-btn>
                        </v-subheader>
                        <v-divider />
                        <v-sheet class="overflow-y-auto scroller" height="300">
                            <circular v-if="loading" class="m-auto"/>
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
                                <v-row>
                                    <v-col cols="12">
                                        <label class="caption">CLASS CATEGORIES</label>
                                        <v-select 
                                        outlined 
                                        dense 
                                        :items="classes"
                                        v-model="form.class_category_id"
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

                                        <label class="caption">COURSES</label>
                                        <v-select 
                                            outlined 
                                            dense 
                                            :items="class_courses"
                                            v-model="form.course_id"
                                            item-text="title"
                                            item-value="id"
                                            hide-details="auto"
                                            multiple
                                            :disabled="form.class_category_id.length === 0"
                                        >
                                        </v-select>
                                    </v-col>
                                </v-row>
                            </v-form>
                            <section class="d-flex align-center justify-end mt-5">
                                <v-btn text color="secondary" @click="addCourses = false" class="poppins">
                                    Cancel
                                </v-btn>
                                <v-btn color="primary" @click="updateUser" :loading="updateLoading" class="poppins">
                                    Update
                                </v-btn>
                            </section>
                        </div>
                    </v-form>
                </div>
                
            </section>
            
        </v-card>
    </v-dialog>
  </template>
  

<style>
.cursor-pointer {
cursor: pointer;
}
</style>

<script>
import { mapActions, mapState } from 'vuex'
export default {
    
props: ['dialog', 'item', 'type'],
    data: () => ({
        items: ['USER', 'INSTRUCTOR'],
        form : {
            class_category_id: [],
            course_id: [],
        },
        msg: {
            show: false,
            type: '',
            text: ''
        },
        loading: false,
        user: null,
        addCourses: false,
        class_list: [],
        loading: false,
        updateLoading: false,
        successRemove: false,
        successUpdated: false,
        role: '',
        roleLoading: false
    }),
    mounted() {
        this.addCourses = false
        this.loading = true
        this.$api.get(`/admin/enrollments/user/${this.item.id}`).then((res)=>{
            this.user = res.data.data
            this.role = res.data.data.role
        }).finally(() => {
            this.loading = false
        })
    },
    computed: {
        ...mapState('admin', {
            classes: (state) => state.classes,
            //courses: (state) => state.courses,
        }),
        ...mapState({
            tenant: (state) => state.tenant
        }),
        class_courses(){
            let _courses = []
            this.form.class_category_id.forEach((_class)=>{
                _courses.push(...this.classes.filter(item => item.id === _class)[0].courses)
            })

            return _courses
        }
    },
    watch:{
        dialog(val) {
            if(!val) {
                this.form.class_category_id = []
                this.form.course_id = []
                this.addCourses = false
            } else {
                this.loading = true
                this.$api.get(`/admin/enrollments/user/${this.item.id}`).then((res)=>{
                    this.user = res.data.data
                    this.loading = false
                })
            }
        },
        addCourses(val) {
            if(!val) {
                this.form.class_category_id = []
                this.form.course_id = []
                this.loading = true
                this.$api.get(`/admin/enrollments/user/${this.item.id}`).then((res)=>{
                    this.user = res.data.data
                    this.loading = false
                })
            }
        },
        role(){
            this.roleLoading = true
            this.updateUserRoleAction({ user_id: this.user.id, role: this.role}).then(() => {
                if(this.user.role.toUpperCase() !== this.role) {
                    this.msg = {
                        show: true,
                        text: 'Successfully updated user role',
                        type: 'success'
                    }
                    setTimeout(()=>{
                        this.successRemove = false
                        this.msg = {
                            show: false,
                            text: '',
                            type: ''
                        }
                        this.addCourses = false
                        this.loading = true
                        this.$api.get(`/admin/enrollments/user/${this.item.id}`).then((res)=>{
                            this.user = res.data.data
                            this.role = res.data.data.role
                        }).finally(() => {
                            this.loading = false
                        })
                    }, 2000)
                }
            }).catch(e => {
                this.loading = false
                this.msg = {
                    show: true,
                    text: 'Something went wrong.',
                    type: 'error'
                }
                setTimeout(()=>{
                    this.loading = false
                    this.msg = {
                        show: false,
                        text: '',
                        type: ''
                    }
                }, 2000)
            }).finally(() => {
                this.roleLoading = false
            })
        }
    },
    methods: {
        ...mapActions('admin', [
            'removeUserCoursesAction',
            'updateUserRoleAction'
        ]),
        removeCourse(payload){
            let _form = new FormData();

            _form.append("_method", 'DELETE');
            _form.append("user_id", payload.user_id);
            _form.append("course_id", payload.course_id);

            this.$api.post(`/admin/enrollments/${payload.id}`, _form).then((res)=>{
                this.$api.get(`/admin/enrollments/user/${this.item.id}`).then((res)=>{
                    this.user = res.data.data
                    this.loading = false
                    this.msg = {
                        show: true,
                        text: 'Successfully unenrolled user to course.',
                        type: 'success'
                    }
                    setTimeout(()=>{
                        this.successRemove = false
                        this.msg = {
                            show: false,
                            text: '',
                            type: ''
                        }
                    }, 2000)
                }).catch(e => {
                    this.loading = false
                })
            }).catch(e => {
                this.loading = false
                this.msg = {
                    show: true,
                    text: 'Something went wrong.',
                    type: 'error'
                }
                setTimeout(()=>{
                    this.loading = false
                    this.msg = {
                        show: false,
                        text: '',
                        type: ''
                    }
                }, 2000)
            })
        },
        updateUser(){
            this.updateLoading = true
            this.$api.post(`/admin/enrollments/user/${this.item.id}`, { course_id: this.form.course_id, class_category_id: this.form.class_category_id}).then((res)=>{
                this.successUpdated = true
                this.msg = {
                    show: true,
                    text: 'Successfully enrolled',
                    type: 'success'
                }
                setTimeout(()=>{
                    this.updateLoading = false
                    this.msg = {
                        show: false,
                        text: '',
                        type: ''
                    }
                    this.addCourses = false
                }, 2000)
            }).catch(e => {
                this.updateLoading = false
                this.msg = {
                    show: true,
                    text: 'Something went wrong.',
                    type: 'error'
                }
                setTimeout(()=>{
                    this.updateLoading = false
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