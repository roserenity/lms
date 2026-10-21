<template>
    <v-dialog v-model="dialog" persistent max-width="550">
        <v-card class="pa-3">
            <v-overlay :value="permissionUpdated" opacity="0" transition="scale-transition">
                
                <v-alert
                    dense
                    type="success"
                    absolute
                    elevation="3"
                    :value="permissionUpdated"
                    transition="scale-transition"
                >
                Permission successfully updated
                </v-alert>
            </v-overlay>
            <div class="poppins f18 fw600 d-flex pa-3">
                <div class="primary--text mr-auto">
                    Share
                </div>
                <v-btn icon small right @click="$emit('close')">
                    <v-icon size="18">mdi-close</v-icon>
                </v-btn>
            </div>
            <div class="poppins f14 secondary-2--text px-3">
                Sharing this course will allow other instructors to view or edit the file. Enter the email address below
            </div>
            <v-card-text>
                <v-text-field
                    dense
                    outlined
                    class="general-custom-field secondary-1--text f14 fw600 roboto mb-2 mt-2"
                    prepend-inner-icon="mdi-magnify"
                    placeholder="Search Instructor"
                    hide-details
                    v-model="search"
                />
                <div class="roboto primary--text f12" v-if="email_addresses.length > 0">
                    {{ email_addresses.length }} selected
                </div>
                <v-sheet height="300" class="overflow-y-auto scroller">
                    <v-list dense flat>
                    <v-list-item-group v-model="email_addresses" active-class="active" multiple>
                        <v-list-item v-for="(item, i) in items" :key="i" :value="item.id" class="px-0">
                        <template v-slot:default="{ active }">
                            <v-list-item-content>
                            <v-list-item-title class="d-flex justify-space-between align-center">
                                <div class="d-flex flex-row align-center">
                                <v-icon :color="!active ? '' : 'primary'">{{
                                    active ? "mdi-checkbox-marked" : "mdi-checkbox-blank-outline"
                                }}</v-icon>
                                <div class="text-wrap roboto f14 mx-2">
                                    {{ item.name }}
                                </div>
                                </div>
                            </v-list-item-title>
                            </v-list-item-content>
                        </template>
                        </v-list-item>
                    </v-list-item-group>
                    </v-list>
                </v-sheet>
                
                <alert 
                    v-if="msg.show"
                    :show = "msg.show"
                    :text = "msg.text"
                    :type = "msg.type"
                />

                <v-btn dense class="f12 poppins mt-2" color="primary" :disabled="email_addresses.length === 0 ||  (shared_to_users.length + email_addresses.length > 3)" @click="shareCourse" :loading="loading">
                    Share Course
                </v-btn>
                <v-divider class="mt-3"/>
                <div class="poppins secondary-2--text">
                <span v-if="shared_to_users.length === 0" class="f12 px-3">No members yet</span>
                <v-list v-else>
                    <v-list-item v-for="(member, i) in shared_to_users" class="f12" :key="i">
                        <v-row no-gutters class="d-flex align-center justify-space-between">
                            <v-col class="text-wrap">
                                {{ member.first_name ? member.first_name : 'Instructor' }} {{ member.last_name ? member.last_name : 'Account' }} ({{ member.email }})
                            </v-col>
                            <v-col cols="5">
                                <v-select
                                    :items="roles"
                                    :value="member.pivot.permission"
                                    dense
                                    hide-details
                                    outlined
                                    class="f12"
                                    v-on:change="changePermission(member.id, $event)"/>
                            </v-col>
                            <v-col cols="1">
                                <v-btn dense icon @click="remove(member)" :loading="removeLoading && selected.id === member.id">
                                    <v-icon small>
                                        mdi-close
                                    </v-icon>
                                </v-btn>
                            </v-col>
                        </v-row>
                    </v-list-item>
                </v-list>
            </div>
            </v-card-text>
            <!-- <div class="poppins f14 secondary-2--text pa-3">
                <span class="f12">Email Address</span>
                <v-autocomplete
                    v-model="email_addresses"
                    :items="instructors"
                    filled
                    chips
                    color="blue-grey lighten-2"
                    item-text="name"
                    item-value="id"
                    multiple
                    flat
                    hide-details
                    class="f12 mb-3">
                    <template v-slot:selection="data">
                        <v-chip
                            outlined
                            color="primary"
                            v-bind="data.attrs"
                            :input-value="data.selected"
                            close
                            @click="data.select"
                            @click:close="remove(data.item)"
                            class="f12 text-wrap"
                            >
                            {{ data.item.name }}
                        </v-chip>
                    </template>
                    <template v-slot:item="data">
                        <v-list-item-content>
                            <v-list-item-title class="f12"> {{ data.item.name }} </v-list-item-title>
                        </v-list-item-content>
                    </template>
                </v-autocomplete>
            </div> -->
            
        </v-card>
    </v-dialog>
</template>
<script>
import {mapState, mapActions, mapMutations} from 'vuex'
export default {
    props: ['dialog', 'course_id', "shared_to_users"],
    data: () => ({
        email_addresses: [],
        roles: [
            {
                text: 'Viewer',
                value: 'viewer'
            },
            {
                text: 'Editor',
                value: 'editor'
            }
        ],
        permissionUpdated: false,
        loading: false,
        search: '',
        items: [],
        msg: {
            show: false,
            text: '',
            type: ''
        },
        selected: null,
        removeLoading: false
    }),
    methods: {
        ...mapActions('instructor', ["shareCourseAction", "updatePermissionAction", "removeCourseSharedIntructor"]),
        ...mapMutations(["alertMutation"]),

        remove (item) {
            this.selected = item

            this.removeLoading = true
            this.removeCourseSharedIntructor({ user_id: item.id, course_id: this.course_id}).then(() => {
                this.removeLoading = false
                this.alertMutation({
                    show: true,
                    text: 'Instructor successfully unassigned.',
                    type: "success"
                })
                this.selected = null
                this.$emit('getShareUsers')
            }).catch(() => {
                this.removeLoading = false
                this.$emit('close')
                this.alertMutation({
                    show: true,
                    text: 'Something went wrong',
                    type: "error"
                })
            })
        },

        shareCourse() {
                this.loading = true
                this.shareCourseAction({
                    course_id: this.course_id,
                    user_id: this.email_addresses
                }).then(() => {
                    this.$emit('close')
                    this.alertMutation({
                        show: true,
                        text: 'Course successfully shared',
                        type: "success"
                    })
                    this.email_addresses = []
                }).catch(() => {
                    this.$emit('close')
                    this.alertMutation({
                        show: true,
                        text: 'Something went wrong',
                        type: "error"
                    })
                }).finally(() => {
                    this.loading = false
                })
        },

        changePermission(id, permission){
            this.updatePermissionAction({
                course_id: this.course_id,
                user_id: id,
                permission: permission
            }).then(() => {
                this.permissionUpdated = true
                setTimeout(()=>{
                    this.permissionUpdated = false
                }, 2000)
                
            })
        }
    },
    watch: {
        dialog() {
            this.items = [...this.instructors]
        },

        search(val){
            if (val == '') {
                this.items = [...this.instructors]
            } else {
                this.items = this.items.filter(user => String(`${user.name}`).toLowerCase().search(val.toLowerCase()) !== -1)
            }
        },
        email_addresses() {
            if(this.shared_to_users.length + this.email_addresses.length > 3) {
                this.msg = {
                    show: true,
                    text: 'Courses can be shared with up to three instructors.',
                    type: 'error'
                }
            } else {
                this.msg = {
                    show: false,
                    text: '',
                    type: ''
                }
            }
        }
    },
    computed: {
        ...mapState("instructor", {
            instructors: (state) => {
                let _instructors = []

                state.instructors.forEach(instructor => {
                    _instructors.push({
                        name: `${ instructor.first_name ? instructor.first_name : 'Instructor' } ${ instructor.last_name ? instructor.last_name : 'Account' } ${ instructor.suffix ? instructor.suffix : '' } (${ instructor.email })`,
                        id: instructor.id
                    })
                })

                return _instructors
            },
        }),
    }
}
</script>