<template>
    <section>
        <section class="d-flex flex-wrap justify-space-between">
            <SearchBar 
                :placeholder="'Search course group'" 
                :value.sync="search" 
                @clear="clearSearch"
                @search="onSearch"
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
                    @click="() => { action = 'create' , course_group_dialog = true }"
                    :label="'Create Group'"
                    :icon="'mdi-plus'"
                    class="ml-1"
                    :loading="createLoading"
                />
                
            </section>
        </section>

        <v-data-table
            :loading="loading"
            :headers="course_group_tbl"
            :items="course_groups"
            hide-default-footer
            :items-per-page.sync="itemsPerPage"
            class="text--center custom-border poppins f14">
            <template v-slot:item.action="{ item }">
                <v-btn v-if="item.course_group_has_group_chat.length > 0" icon color="primary" @click="() => { $router.push({ name: 'Instructor Inbox', query: { group_chat_uuid: item.course_group_has_group_chat[0].uuid, timestamp: Date.now() } }) }">
                    <v-icon small>mdi-message-text-outline</v-icon>
                </v-btn>
                <v-btn v-if="item.course_group_has_group_chat.length > 0" @click="() => { selected_course_group = item, group_chat_member_dialog = true }" icon class="primary--text f12 fw600">
                    <v-icon size="16" >mdi-account-multiple</v-icon>
                </v-btn>
                <v-btn v-if="item.course_group_has_group_chat.length > 0" @click="() => { selected_course_group = item, group_chat_contact_dialog = true }" icon class="primary--text f12 fw600">
                    <v-icon size="16" >mdi-account-multiple-plus</v-icon>
                </v-btn>
                <v-btn icon color="primary" @click="() => { action = 'edit', course_group_dialog = true, selected_course_group = item }">
                    <v-icon small>mdi-pencil-outline</v-icon>
                </v-btn>
                 <v-btn icon color="primary" @click="() => { selected_course_group = item, deleteDialog = true }">
                    <v-icon small>mdi-trash-can-outline</v-icon>
                </v-btn>
            </template>
        </v-data-table>

        <FormPagination 
            :pageCount="pageCount" 
            :page="page"
            :paginate="paginationPaginate"
            @page="onPageChange" 
            @paginate="onPaginateChange"/>

        <DeleteDialog
            type="Course Group"
            :dialog="deleteDialog"
            :loading="deleteLoading"
            @close="deleteDialog = false"
            @delete="deleteCourseGroup"
        />

        <GroupContactDialog
            :dialog="group_chat_contact_dialog" 
            :group_chat_uuid="(selected_course_group && selected_course_group.course_group_has_group_chat.length > 0) ? selected_course_group.course_group_has_group_chat[0].uuid : null"
            @close="() => { group_chat_contact_dialog = false, getData(), selected_course_group = null }"
        />

        <GroupMembersDialog 
            v-if="selected_course_group"
            :dialog="group_chat_member_dialog" 
            :members="selected_course_group.course_group_has_users"
            :group="(selected_course_group && selected_course_group.course_group_has_group_chat.length > 0) ? selected_course_group.course_group_has_group_chat[0] : null"
            @getData="getData()"
            @close="() => { group_chat_member_dialog = false, selected_course_group = null}"/>

        <CoursegroupDialog 
            :dialog="course_group_dialog" 
            :group="selected_course_group"
            :loading="coursesLoading"
            :course_id="Number($route.params.id)"
            @close="() => {  course_group_dialog = false, action = 'create', selected_course_group = null }" 
            @getData="getData" :action="action"/>
    </section>
</template>

<script>
import { mapActions, mapState, mapMutations } from 'vuex';
import searchAndPaginateMixin from '@/mixins/searchAndPaginateMixin';
import { course_group_tbl } from '@/constants/tblheaders/coursegroup';
import CoursegroupDialog from '@/components/teacher/usergroup/CoursegroupDialog.vue';
import GroupDialog from '@/components/teacher/usergroup/GroupDialog.vue';
import GroupContactDialog from '@/components/teacher/inbox/GroupContactDialog.vue';
import GroupMembersDialog from '@/components/teacher/inbox/GroupMembersDialog.vue';

export default {
mixins: [searchAndPaginateMixin],
components: {
    CoursegroupDialog,
    GroupDialog,
    GroupContactDialog,
    GroupMembersDialog
},
data: () => ({
    loading: false,
    createLoading: false,
    deleteLoading: false,
    deleteDialog: false,
    course_group_tbl,
    totalCount: 0,
    pageCount: 1,
    status: [
        { text: 'ALL', value: '' },
        { text: 'PUBLISHED', value: 'PUBLISH' },
        { text: 'UNPUBLISHED', value: 'UNPUBLISH' },
    ],
    selected_course_group: null,
    course_group_dialog: false,
    action: 'create',
    coursesLoading: false,
    group_chat_contact_dialog: false,
    group_chat_member_dialog: false
}),
mounted() {

    this.coursesLoading = true
    this.getCoursesAction().finally(() => {
        this.coursesLoading = false
    })
    
},
methods: {
    ...mapActions('instructor', ['getCourseGroupsAction', 'deleteCourseGroupAction', 'getCoursesAction']),
    ...mapMutations(['alertMutation']),

    getData(){
        if(!this.loading) {
            this.loading = true

            this.getCourseGroupsAction({ paginate: Number(this.paginate), page: this.page, search: this.search, ...this.filter, course_id: this.$route.params.id}).then(res => {
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

    deleteCourseGroup(){
        this.deleteLoading = true
        this.deleteCourseGroupAction(this.selected_course_group.id).then(res => {
            this.deleteLoading = false
            this.alertMutation({
                show: true,
                text: 'Course group has been successfully deleted',
                type: "success"
            })
            this.selected_course_group = null
            this.deleteDialog = false
            this.getData()
        }).catch(() => {
            this.deleteLoading = false
            this.alertMutation({
                show: true,
                text: 'Something went wrong.',
                type: "error"
            })
            this.selected_course_group = null
            this.deleteDialog = false
        })
    }
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
}
</script>
