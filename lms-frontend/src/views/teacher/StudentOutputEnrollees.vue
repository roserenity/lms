<template>
    <section class="mx-auto">
        <Breadcrumb
            :links="[
                {
                    label: `<  Student Outputs`,
                    link: true, 
                    route: (prevRoute && prevRoute.name=== 'Instructor Student Outputs') ? { name: 'Instructor Student Outputs', query: { ...prevRoute.query} } : { name: 'Instructor Student Outputs', query: { search: '', page: 1, paginate: 10, classes: '' } },
                },
                {
                    label: `${title}`,
                    link: false,
                    route: null,
                },
            ]"
        />
        <section class="d-flex flex-wrap justify-space-between">
            <SearchBar 
                :placeholder="'Search student'"
                :value.sync="search" 
                class='mb-3' 
                @clear="clearSearch"
                @search="onSearch"/>

            <ButtonExport
                @click="exportEnrollees"
                :loading="exportLoading"
                :disabled="loading || enrollees.length === 0"
                class="ma-1 ml-auto mb-3"/>
        </section>

        <v-data-table
            :headers="enrollees_tbl"
            :items="enrollees"
            class="elevation-1 poppins"
            :loading="loading"
            hide-default-footer
            :items-per-page.sync="itemsPerPage"
        >
            <template v-slot:item.progress="{ item }">
                <section class="d-flex flex-row align-center">
                    <v-progress-linear 
                        height="8"
                        :value="item.progress"
                        rounded
                        color="primary"
                        background-color="grey lighten-3"
                        class="mr-2"
                        style="width: 80%"
                    >
                    </v-progress-linear>
                    <div class="d-flex flex-row caption">
                        {{ item.progress }}%
                    </div>
                </section>
                <span></span>
            </template>
            <template v-slot:item.action="{ item }">
                <v-btn block dense color="primary" text class="f12"  
                    @click="$router.push({name: 'Instructor Student Outputs Enrollee Progress', params: {course_uuid: $route.params.course_uuid, user_id: item.id},  query: { search: '', page: 1, paginate: 10 }})">
                    <v-icon class="mr-1" dense>
                        mdi-eye-outline
                    </v-icon>
                </v-btn>
            </template>
        </v-data-table>
        
        <FormPagination 
            :pageCount="pageCount" 
            :page="page"
            :paginate="paginationPaginate"
            @page="onPageChange" 
            @paginate="onPaginateChange"/>

    </section>
</template>

<script>
import { mapActions, mapMutations, mapState } from "vuex";
import searchAndPaginateMixin from '@/mixins/searchAndPaginateMixin';
import Breadcrumb from "@/layouts/teacher/InstructorBreadcrumbs.vue";
import { enrollees_tbl } from "@/constants/tblheaders/studentprogress"

export default {
    mixins: [searchAndPaginateMixin],
    components: {
        Breadcrumb
    },
    data: () => ({
        title: '',
        loading: false,
        exportLoading: false,
        enrollees_tbl,
        pageCount: 1,
        prevRoute: null,
    }),

    computed: {
        ...mapState('instructor', {
            enrollees: (state) => state.enrollees
        }),

        itemsPerPage(){
            return Number(this.paginate)
        },

        paginationPaginate(){
            return String(this.paginate)
        }
    },

    mounted(){
    },

    methods: {
        ...mapActions('instructor', ['getEnrolleesAction', 'enrolleesExportAction']),
        ...mapMutations(['alertMutation']),

        getData() {
            if(!this.loading) {
                this.loading = true

                this.getEnrolleesAction({ course_uuid: this.$route.params.course_uuid, paginate: Number(this.paginate), page: this.page, search: this.search }).then(res => {
                    this.title = res.title
                    this.page = res.current_page
                    this.pageCount = res.last_page
                    this.paginate = String(res.per_page)
                    this.loading = false
                }).catch(() => {
                    this.loading = false
                })
            }
        },

        exportEnrollees(){
            this.exportLoading = true
            this.enrolleesExportAction({ course_uuid: this.$route.params.course_uuid, search: this.search}).then(res => {
                this.exportLoading = false
                this.$exportToCSV(`${this.title} - enrollees`, res)
            }).catch(e => {
                this.exportLoading = false
                this.alertMutation({
                    show: true,
                    text: 'Something went wrong.',
                    type: "error"
                })
            })
        }
    },
  
    beforeRouteEnter(to, from, next) {
        next(vm => {
            vm.prevRoute = from          
        })
    },
}
</script>