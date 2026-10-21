<template>
    <section id="grading-assessment">
        <Breadcrumb
            :links="[
            {
                label: `<  ${course_title} `,
                link: true,
                route: (prevRoute && prevRoute.name === 'Instructor Grading Course') ? { name: 'Instructor Grading Course' , params: { id: $route.params.id }, query: { ...prevRoute.query}} : { name: 'Instructor Grading Course' , params: { id: $route.params.id }, query: { search: '', page: 1, paginate: 10 }}
            },
            {
                label: `${group_activity_title}`,
                link: false,
                route: null,
            },
            ]"
        />

        <SearchBar 
            :placeholder="'Search group'" 
            :value.sync="search" 
            @clear="clearSearch"
            @search="onSearch"
            class="mb-5"/>

        <v-data-table
            :headers="grading_group_activity_tbl"
            :items="grading"
            class="text--center custom-border poppins f14 mt-3"
            :loading="loading"
            :items-per-page.sync="itemsPerPage"
            hide-default-footer>
            <template v-slot:item.action="{ item }">
                <v-btn dense icon class="primary--text f12" @click="() => { selected = item, group_activity_group_members_dialog = true, getGroupMembers() }">
                    <v-icon small>mdi-account-eye-outline</v-icon>
                </v-btn>
                <v-btn
                    text
                    small
                    icon
                    v-if="item.file_url"
                    class="primary--text text-capitalize"
                    :to="{ name: 'Instructor Group Activity Group', params: { id: $route.params.id, group_activity_id: item.group_activity_id, group_id: item.id } }"
                    >
                    <v-icon small>mdi-eye-outline</v-icon>
                </v-btn>
            </template>
            <template v-slot:item.checked="{ item }">
                <v-alert dense small :color="item.checked === 1 ? '#7BC14533' : '#BDBDBD33'" class="ma-auto my-2" :class="item.checked === 1 ? 'success--text' : 'secondary-2--text'">
                    {{ item.checked ? 'CHECKED' : 'PENDING' }}
                </v-alert>
            </template>
        </v-data-table>
                            
        <FormPagination 
            :pageCount="pageCount" 
            :page="page"
            :paginate="paginationPaginate"
            @page="onPageChange" 
            @paginate="onPaginateChange"/>
            
        <GroupActivityGroupMembers
            :dialog="group_activity_group_members_dialog"
            :loading="loading1"
            :members="group_activity_group_members"
            @close="group_activity_group_members_dialog = false"
            @getMembers="getGroupMembers()"
            :group="selected"
        />
    </section>
</template>

<script>

import { mapActions, mapState, mapMutations } from 'vuex'
import searchAndPaginateMixin from '@/mixins/searchAndPaginateMixin';
import Breadcrumb from "../../layouts/teacher/InstructorBreadcrumbs.vue";
import { grading_group_activity_tbl } from '@/constants/tblheaders/grading';
import GroupActivityGroupMembers from '@/components/teacher/course/GroupActivityGroupMembers.vue';

export default {
    mixins: [searchAndPaginateMixin],
    components: { Breadcrumb, GroupActivityGroupMembers },
    data: () => ({
        search: '',
        group_activity_title: '',
        course_title: '',
        loading: false,
        loading1: false,
        pageCount: 1,
        paginate: '10',
        totalCount: 0,
        prevRoute: null,
        grading_group_activity_tbl,
        group_activity_group_members_dialog: false,
        selected: null,
        group_activity_group_members: []
    }),
    mounted(){
        this.getData()
    },
    methods: {
        ...mapMutations(["alertMutation"]),
        ...mapActions('instructor', ['getGradingGroupActivitiesAction', 'getGroupActivityGroupMembersAction', 'generateGroupActivityGroupFileAction']),

        getData(){
            if(!this.loading) {
                this.loading = true
                this.getGradingGroupActivitiesAction({ group_activity_id: this.$route.params.group_activity_id, paginate: Number(this.paginate), page: this.page, search: this.search}).then(res => {
                    this.course_title = res.course_title
                    this.group_activity_title = res.group_activity_title
                    this.pageCount = res.groups.last_page
                    this.paginate = res.groups.per_page.toString()
                    this.page = res.groups.current_page
                    this.loading = false
                }).catch(()=>{
                    this.loading = false
                })
            }
        },

        getGroupMembers() {
            this.loading1 = true
            this.getGroupActivityGroupMembersAction(this.selected).then(res => {
                this.group_activity_group_members = [...res.group_activity_has_group_members]
                this.loading1 = false
            }).catch(() => {
                this.loading1 = false
                this.group_activity_group_members_dialog = false
                this.alertMutation({
                    show: true,
                    text: "Something went wrong.",
                    type: "error"
                }) 
            })
        },
        
    },
    computed: {
        ...mapState('instructor', {
            grading: (state) => state.grading,
        }),
    },
    beforeRouteEnter(to, from, next) {
        next(vm => {
            vm.prevRoute = from          
        })
    },
}

</script>
