<template>
    <section>
        <Breadcrumb
            :links="[
                {
                label: 'Back',
                link: true,
                route: (prevRoute && prevRoute.name === 'Instructor Course Groups') 
                    ? { name: 'Instructor Course Groups' , params: { id: $route.params.id }, query: { ...prevRoute.query }} 
                    : { name: 'Instructor Course Groups' , params: { id: $route.params.id }, query: { search: '', page: 1, paginate: 10 }},
                },
                {
                label: `${course_group ? course_group.name : ''}`,
                link: false,
                route: null,
                },
            ]"
        />
        <section class="d-flex flex-wrap justify-space-between">
            <SearchBar 
                v-if="tab === 0"
                :placeholder="'Search group member'" 
                :value.sync="search" 
                @clear="() => { search = '', page = 1, getGroupMembers() }"
                @search="() => { page = 1, getGroupMembers() }"
                class="mb-2"/>

            <SearchBar 
                v-if="tab === 1"
                :placeholder="'Search subgroup'" 
                :value.sync="search" 
                @clear="() => { search = '', page = 1, getCourseSubgroups() }"
                @search="() => { page = 1, getCourseSubgroups() }"
                class="mb-2"/>

            <SearchBar 
                v-if="tab === 2"
                :placeholder="'Search subgroup member'" 
                :value.sync="search" 
                @clear="() => { search = '', page = 1, getCourseSubgroupMembers() }"
                @search="() => { page = 1, getCourseSubgroupMembers() }"
                class="mb-2"/>

            <section class="mb-3 ml-auto">
                <!-- <FilterMenu
                    :initialFilter="filter"
                    @resetFilters="resetFilters"
                    @applyFilters="() => { page = 1, updateQuery(), getData() }"
                    >
                    <v-sheet max-height="350" class="overflow-y-auto scroller">
                        <FormSelectObject
                            :label="'TYPE'"
                            :items="[{text: 'All', value: ''} , ...question_pool_types]"
                            :value.sync="filter.type_of_question_pool"
                        />
                    </v-sheet>
                </FilterMenu> -->
            
                <ButtonPrimary
                    v-if="tab === 0 && course_group && course_group.course_group_has_group_chat.length === 0"
                    :outlined="true"
                    @click="() => { groupChatDialog = true }"
                    :label="'Create Group Chat'"
                    class="ml-1"
                />
            
                <ButtonPrimary
                    v-if="tab === 0 && course_group && course_group.course_group_has_group_chat.length > 0"
                    :outlined="true"
                    @click="() => { $router.push({ name: 'Instructor Inbox', query: { group_chat_uuid: course_group.course_group_has_group_chat[0].uuid, timestamp: Date.now() } }) }"
                    :label="'View Group Chat'"
                    class="ml-1"
                />

                <ButtonPrimary
                    v-if="tab === 2 && selected_course_sub_group && selected_course_sub_group.user_subgroup_has_group_chat.length === 0"
                    :outlined="true"
                    :disabled="members.length === 0"
                    @click="() => { groupChatDialog = true }"
                    :label="'Create Group Chat'"
                    class="ml-1"
                />

                
                <ButtonPrimary
                    v-if="tab === 2 && selected_course_sub_group && selected_course_sub_group.user_subgroup_has_group_chat.length > 0"
                    :outlined="true"
                    @click="() => { $router.push({ name: 'Instructor Inbox', query: { group_chat_uuid: selected_course_sub_group.user_subgroup_has_group_chat[0].uuid, timestamp: Date.now() } }) }"
                    :label="'View Group Chat'"
                    class="ml-1"
                />

                <ButtonPrimary
                    v-if="tab === 0"
                    @click="course_group_member_dialog = true"
                    :label="'Add Users'"
                    :icon="'mdi-plus'"
                    class="ml-1"
                />

                <ButtonPrimary
                    v-if="tab === 1"
                    @click="() => { action = 'create', course_subgroup_dialog = true }"
                    :label="'Add Subgroup'"
                    :icon="'mdi-plus'"
                    class="ml-1"
                />

                <ButtonPrimary
                    v-if="tab === 2"
                    @click="course_subgroup_member_dialog = true"
                    :label="'Add Users'"
                    :icon="'mdi-plus'"
                    class="ml-1"
                />
            </section>
        </section>

        <Tabs :value.sync="tab" :tabs="items"/>
        <v-divider />
        <v-tabs-items v-model="tab" style="background-color: transparent">
            <v-tab-item>
                <v-data-table
                    :loading="loading"
                    :headers="course_group_members_tbl"
                    :items="members"
                    hide-default-footer
                    :items-per-page.sync="itemsPerPage"
                    class="text--center custom-border poppins f14">
                    <template v-slot:item.action="{ item }">
                        <v-btn icon color="primary" @click="() => { selected = item.id, deleteUserDialog = true }">
                            <v-icon small>mdi-trash-can-outline</v-icon>
                        </v-btn>
                    </template>
                </v-data-table>


                <FormPagination 
                    :pageCount="pageCount" 
                    :page="page"
                    :paginate="paginate"
                    @page="(e) => { page = e, getGroupMembers() }" 
                    @paginate="(e) => { paginate = e, page = 1, getGroupMembers() }"/>

            </v-tab-item>
        </v-tabs-items>

        <CourseGroupMemberDialog 
            :dialog="course_group_member_dialog"
            @getGroupMembers="getGroupMembers()"
            @close="() => { course_group_member_dialog = false }"
        />

        <DeleteDialog
            :type="`user from ${course_group?.name}`"
            :dialog="deleteUserDialog"
            :loading="deleteLoading"
            @close="deleteUserDialog = false"
            @delete="deleteUserMember"
        />
            
        <GroupDialog 
            :subgroup="tab == 2 ? selected_course_sub_group : null"
            :dialog="groupChatDialog"
            @close="() => { groupChatDialog=false } "
        />
    </section>
</template>

<script>
import { mapActions, mapState, mapMutations } from 'vuex';
import { course_group_members_tbl, user_subgroup_tbl } from '@/constants/tblheaders/coursegroup';
import Breadcrumb from "@/layouts/teacher/InstructorBreadcrumbs.vue";
import CourseGroupMemberDialog from "@/components/teacher/usergroup/CourseGroupMemberDialog"
import GroupDialog from '@/components/teacher/usergroup/GroupDialog.vue';

export default {
    components: {
        Breadcrumb,
        CourseGroupMemberDialog,
        GroupDialog,
    },
    data: () => ({
        loading: false,
        createLoading: false,
        deleteLoading: false,
        deleteUserDialog: false,
        coursesLoading: false,
        groupChatDialog: false,
        subgroup_loading: false,
        course_group_members_tbl,
        status: [
            { text: 'ALL', value: '' },
            { text: 'PUBLISHED', value: 'PUBLISH' },
            { text: 'UNPUBLISHED', value: 'UNPUBLISH' },
        ],
        selected: null,
        selected_course_sub_group: null,
        course_group_member_dialog: false,
        course_subgroup_dialog: false, 
        course_subgroup_member_dialog: false,
        members: [],
        course_group: null,
        prevRoute: null,
        items: ['Members', 'Subgroups'],
        tab: 0,
        pageCount: 1,
        page: 1,
        paginate: '10',
        search: '',
        totalCount: 0,
        user_subgroup_tbl,
        action: 'create'
    }),

    mounted() {
        if(this.$route.params.id) {
            this.loading = true
            this.getCourseGroupAction({ id: this.$route.params.id }).then(res => {
                this.course_group = res
                this.loading = false
            }).catch(e => {
                this.loading = false
                this.alertMutation({
                    show: true,
                    text: 'Something went wrong.',
                    type: "error"
                })
            })
            this.getGroupMembers()
        }
    },

    methods: {
        ...mapActions('instructor', ['getCoursesAction','getCourseGroupAction', 'getCourseGroupMembersAction', 'removeCourseGroupMemberAction', 'getCourseSubgroupsAction', 'getCourseSubgroupMembersAction', 'deleteSubGroupAction', 'removeCourseSubgroupMembersAction']),
        ...mapMutations(['alertMutation']),

        getGroupMembers(){
            if(this.$route.params.id) {
                this.loading = true
                this.getCourseGroupMembersAction({ id: this.$route.params.id, paginate: Number(this.paginate), page: this.page, search: this.search }).then(res => {
                    this.members = res.data
                    this.loading = false
                    this.totalCount = res.total
                    this.pageCount = res.last_page
                    this.page = res.current_page
                    this.paginate = String(res.per_page)
                }).catch(e => {
                    this.loading = false
                    this.alertMutation({
                        show: true,
                        text: 'Something went wrong.',
                        type: "error"
                    })
                })
            }
        },

        resetFilters(){
            this.filter = {
                type_of_question_pool: '',
            }
        },

        deleteUserMember(){

            this.deleteLoading = true
            this.removeCourseGroupMemberAction({id: this.$route.params.id, user_id: this.selected}).then(res => {
                this.deleteLoading = false
                this.alertMutation({
                    show: true,
                    text: 'User has been successfully removed',
                    type: "success"
                })
                this.selected = null
                this.deleteUserDialog = false
                this.getGroupMembers()
            }).catch(() => {
                this.deleteLoading = false
                this.alertMutation({
                    show: true,
                    text: 'Something went wrong.',
                    type: "error"
                })
                this.selected = null
                this.deleteUserDialog = false
            })
        },

    },
    computed: {
        ...mapState('instructor', {
            course_groups: (state) => state.course_groups
        }),

        itemsPerPage(){
            return Number(this.paginate)
        },

        paginationPaginate(){
            return String(this.paginate)
        }
    },
    watch: {
        tab(val) {
            this.pageCount = 1,
            this.page = 1,
            this.paginate = '10',
            this.search = '',
            this.totalCount = 0
            if(val == 0) {
                this.members = []
                this.getGroupMembers()
            }
            if(val == 1) {
                this.getCourseSubgroups()
            }
        }
    }
}
</script>
