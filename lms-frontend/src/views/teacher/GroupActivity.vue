<template>
    <div>
        <v-sheet class="custom-border border pa-5 mb-2">
            <v-row dense>
                <v-col>
                    <FormTextFieldRequired
                        :label="'TITLE'"
                        :error="errors && errors.title"
                        :value.sync="form.title"
                        :disabled="$isReadOnly.isReadOnly(status, permission)"
                    />
                </v-col>
                <v-col>
                    <FormTextFieldRequired
                        :label="'NO. OF GROUPS'"
                        :error="errors && errors.group_count"
                        :value.sync="form.group_count"
                        :disabled="$isReadOnly.isReadOnly(status, permission)"
                        :type="'number'"
                        :min="0"
                    />
                </v-col>
            </v-row>
            <v-row dense>
                <v-col>
                    <label class="caption">DUE DATE</label>
                    <FormDatePicker 
                        :val="form.due_date"
                        class_="mb-3"
                        :min="new Date().toISOString().split('T')[0]"
                        @apply="(e) => {
                            form.due_date = e
                        }"/>
                </v-col>
                <v-col>
                    <FormSelectObject
                        :label="'TYPE OF TOOL'"
                        :items="tools"
                        :value.sync="form.group_activity_tool"
                        :disabled="$isReadOnly.isReadOnly(status, permission)"
                    />
                </v-col>
            </v-row>
            
            <Tabs class="pt-3" :value.sync="tab" :tabs="items"/>
            <v-tabs-items v-model="tab">
                <v-tab-item class="py-2">
                    <label class="caption">DESCRIPTION</label>
                    <RichTextEditor v-if="showMce1"
                        :value="form.description"
                        @setValue='(e) => form.description = e'
                    />
                    <circular v-else />
                    <label class="caption">QUESTION *</label>
                    <RichTextEditor v-if="showMce2"
                        :rules="rules"
                        :value="form.group_activity_questions[0].question"
                        @setValue="(e) => form.group_activity_questions[0].question = e"
                    />
                    <circular v-else />
                </v-tab-item>
                <v-tab-item class="py-2">
                    <v-data-table
                        :headers="course_group_activity_group_tbl"
                        :items="groups"
                        :loading="loading1"
                        class="poppins"
                        :page.sync="page"
                        :items-per-page.sync="itemsPerPage"
                        hide-default-footer
                        dense>
                        <template v-slot:item.actions="{ item }" >
                            <v-btn dense icon class="primary--text f12" @click="() => { selected = item, group_activity_group_members_dialog = true, getGroupMembers() }">
                                <v-icon small>mdi-account-eye-outline</v-icon>
                            </v-btn>
                            <v-tooltip v-model="show" top >
                                <template v-slot:activator="{ on, attrs }">
                                    <v-btn dense icon class="primary--text f12" v-bind="attrs" v-on="on" :loading="generateLoading && ( selected && selected.id == item.id)" @click="() => { selected = item, generateLoading = true, generateFile() }">
                                        <v-icon small>mdi-cloud-refresh-outline</v-icon>
                                    </v-btn>
                                </template>
                                <span>Generate file</span>
                            </v-tooltip>
                            <v-btn 
                                v-if="item.file_url" dense icon class="primary--text f12" :href="item.file_url" target="_blank">
                                <v-icon small>mdi-file-document-outline</v-icon>
                            </v-btn>
                            <v-btn dense icon class="primary--text f12" @click="() => { selected = item, deleteDialog = true }">
                                <v-icon small>mdi-trash-can-outline</v-icon>
                            </v-btn>
                        </template>
                    </v-data-table>
                    
                    <FormPagination 
                        :pageCount="pageCount" 
                        :page="page"
                        :paginate="paginationPaginate"
                        @page="(e) => { page = e, getGroups() }" 
                        @paginate="(e) => { paginate = e, page = 1, getGroups() }"/>
                </v-tab-item>
            </v-tabs-items>

            <ButtonPrimary 
                v-if="!$isReadOnly.isReadOnly(status, permission)"
                @click="save"
                :label="'Save'"
                :loading="loading"
            />
        </v-sheet>

        <GroupActivityGroupMembers
            :dialog="group_activity_group_members_dialog"
            :loading="loading2"
            :members="group_activity_group_members"
            @close="() =>  { selected = null, group_activity_group_members_dialog = false }"
            @getMembers="() => {getGroups(), getGroupMembers()}"
            :group="selected"
        />

        <DeleteDialog
            type="Group"
            :dialog="deleteDialog"
            :loading="deleteLoading"
            @close="() => { selected = null, deleteDialog = false }"
            @delete="deleteGroupActivityGroup"
        />
    </div>
</template>

<script>
import { mapState, mapActions, mapMutations } from 'vuex';
import { tools } from '@/constants/tools';
import { course_group_activity_group_tbl } from '@/constants/tblheaders/course';
import GroupActivityGroupMembers from '@/components/teacher/course/GroupActivityGroupMembers.vue';

export default {
    components: {
        GroupActivityGroupMembers
    },
    props: {
        status: {
            type: String || null,
            default: null
        },
        permission: {
            type: String,
            default: ''
        },
        groupActivity: {
            type: Object || null,
            default: null
        },
    },
    data: () => ({
        tools,
        loading: false,
        loading1: false,
        loading2: false,
        generateLoading: false,
        items: ['Settings', 'Groups'],
        tab: 0,
        pageCount: 1,
        page: 1,
        paginate: '10',
        totalCount: 0,
        selected: null,
        form: {
            title: null,
            description: null,
            group_count: 0,
            due_date: '',
            group_activity_tool: 'GDOC',
            group_activity_questions: [
                {
                    id: -1,
                    question: ''
                }
            ]
        },
        rules: [ v => !!v || 'This is required' ],
        showMce1: false,
        showMce2: false,
        course_group_activity_group_tbl,
        groups: [],
        add_group_activity_group_members_dialog: false,
        group_activity_group_members_dialog: false,
        group_activity_group_members: [],
        deleteDialog: false,
        deleteLoading: false,
    }),

    mounted() {
        this.$nextTick(() => {
            setTimeout(() => {
                this.showMce1 = true;
                this.showMce2 = true;
            }, 2000);
        });
    },

    computed: {
        ...mapState({
            errors: (state) => state.errors
        }),

        itemsPerPage(){
            return Number(this.paginate)
        },

        paginationPaginate(){
            return String(this.paginate)
        }
    },

    watch: {
        groupActivity(newVal, oldVal) {
            if(newVal && oldVal ) {     
                if(newVal.id !== oldVal.id) {
                    this.tab = 0
                }
            }

            this.form = { ...newVal }

            if(!newVal.due_date) {
                this.form.due_date = ''
            }
            
            if(newVal.group_activity_questions.length > 0) {
                this.form.group_activity_questions = [...newVal.group_activity_questions]
            } else {
                this.form.group_activity_questions = [
                    {
                        id: -1,
                        question: ''
                    }
                ]
            }

            this.showMce1 = false;
            this.showMce2 = false;
            this.$nextTick(() => {
                setTimeout(() => {
                    this.showMce1 = true;
                    this.showMce2 = true;
                }, 1000);
            });
        },

        tab(val) {
            if(val == 1) {
                this.getGroups()
            }
        }
    },

    methods: {
        ...mapMutations(["alertMutation"]),
        ...mapActions('instructor', ['updateGroupActivityAction', 'getGroupActivityGroupsAction', 'getGroupActivityGroupMembersAction', 'deleteGroupActivityGroupMembersAction', 'generateGroupActivityGroupFileAction']),

        save() {
            this.loading = true
            this.updateGroupActivityAction({ ...this.form, due_date: this.form.due_date === '' ? null : this.form.due_date}).then(() => {
                this.alertMutation({
                    show: true,
                    text: "Successfully updated a group activity",
                    type: "success"
                }) 
                this.loading = false
                this.getGroups()
            }).catch(() => {
                this.loading = false
                this.alertMutation({
                    show: true,
                    text: "Something went wrong.",
                    type: "error"
                }) 
            })
        },

        getGroups() {
            this.loading1 = true
            this.getGroupActivityGroupsAction({id: this.form.id, paginate: Number(this.paginate), page: this.page }).then(res => {
                this.groups = res.data
                this.page = res.current_page
                this.paginate = String(res.per_page)
                this.pageCount = res.last_page
                this.totalCount = res.total
                this.loading1 = false
            }).catch(() => {
                this.alertMutation({
                    show: true,
                    text: "Something went wrong.",
                    type: "error"
                }) 
                this.loading1 = false
            })
        },

        getGroupMembers() {
            this.loading2 = true
            this.getGroupActivityGroupMembersAction(this.selected).then(res => {
                this.group_activity_group_members = [...res.group_activity_has_group_members]
                this.loading2 = false
            }).catch(() => {
                this.loading2 = false
                this.group_activity_group_members_dialog = false
                this.alertMutation({
                    show: true,
                    text: "Something went wrong.",
                    type: "error"
                }) 
            })
        },

        deleteGroupActivityGroup() {
            this.deleteLoading = true
            this.deleteGroupActivityGroupMembersAction(this.selected).then(() => {
                this.deleteLoading = false
                this.alertMutation({
                    show: true,
                    text: "Successfully deleted a group activity group",
                    type: "success"
                }) 
                this.deleteDialog = false
                this.getGroups()
                this.$emit('viewGroupActivity', this.groupActivity)
                this.selected = null
            }).catch(() => {
                this.deleteLoading = false
                this.alertMutation({
                    show: true,
                    text: "Something went wrong.",
                    type: "error"
                }) 
            })
        },

        generateFile() {
            this.generateLoading = true
            this.generateGroupActivityGroupFileAction(this.selected).then(() => {
                this.generateLoading = false
                this.alertMutation({
                    show: true,
                    text: "Successfully generated a file",
                    type: "success"
                }) 
                this.getGroups()
                this.selected = null
            }).catch(err => {
                this.selected = null
                this.generateLoading = false
                this.alertMutation({
                    show: true,
                    text: (err.response && err.response.data && err.response.data.message) || "Something went wrong.",
                    type: "error"
                }) 
            })
        }
    }
}

</script>