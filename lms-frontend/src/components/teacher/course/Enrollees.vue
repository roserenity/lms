<template>
    <v-sheet class="mb-5 transparent" height="auto" id="synchronous">
        <v-card class="pa-5 custom-border border">
            <section class="d-flex flex-wrap align-end">
                <SearchBar 
                    :outlined="true" 
                    :placeholder="'Search student'" 
                    :value.sync="search" 
                    @search="() => { page = 1, getData() }"
                    @clear="() => { search = '', page = 1, getData() }"
                    class="mb-5 mr-1"/>
            </section>
            
            <v-data-table
                    :headers="certificate_issuance_tbl"
                    :items="enrollees"
                    :loading="loading"
                    class="poppins"
                    :page.sync="page"
                    :items-per-page.sync="itemsPerPage"
                    hide-default-footer
                    dense
                >
                
                <template v-slot:item.certificate="{ item }">
                    <FormSelectObject
                        :items="categories"
                        :multiple="true"
                        class="my-3"
                        @change="(e) => categoryChange(e, item)"
                        :loading="setLoading && selected === item.user_id"
                        :value.sync="item.user.certificate_template_categories"
                    />
                </template>
            </v-data-table>

            <FormPagination 
                :pageCount="pageCount" 
                :page="page"
                :paginate="paginationPaginate"
                @page="onPageChange" 
                @paginate="onPaginateChange"/>
        </v-card>
    </v-sheet>
</template>

<script>
import { mapActions, mapState, mapMutations } from 'vuex';
import { certificate_issuance_tbl } from '@/constants/tblheaders/users';
import searchAndPaginateMixin from '@/mixins/searchAndPaginateMixin';

export default {
    mixins: [searchAndPaginateMixin],
    props: ["item", "course"],
    data: () => ({
        categories: [],
        search: '',
        loading: false,
        setLoading: false,
        pageCount: 1,
        certificate_issuance_tbl,
        selected: null
    }),
    computed: {

        ...mapState('instructor',{
            enrollees: (state) => state.enrollees,
        }),

        itemsPerPage(){
            return Number(this.paginate)
        },

        paginationPaginate(){
            return String(this.paginate)
        }
        
    },
    watch: {
    },
    mounted(){
        this.getCertificateTemplateCategory().then((res) => {
            this.categories = res
        })
        this.getData()
    },
    methods: {
        ...mapMutations(['alertMutation']),
        ...mapActions('instructor', ['getParticipantCertificatesAction', 'getCertificateTemplateCategory', 'setLearnerCertificateAction']),

        getData(){
            if(!this.loading) {
                this.loading = true

                this.getParticipantCertificatesAction({
                    course_id: this.$route.params.id,
                    page: this.page, 
                    paginate: Number(this.paginate),
                    search: this.search,
                    status: this.filter.status
                }).then(res => {
                    this.page = res.current_page
                    this.paginate = String(res.per_page)
                    this.pageCount = res.last_page
                    this.loading = false
                }).catch((e) => {
                    
                    console.log(e)
                    this.alertMutation({
                        show: true,
                        text: 'Something went wrong',
                        type: "error"
                    })
                    this.loading = false

                })
            }
        },

        categoryChange(e, user) {
            this.selected = user.user_id
            this.setLoading = true
            this.setLearnerCertificateAction({ course_id: this.course.id,  user_id: this.selected, certificate_template_categories_id: e}).then(() => {
                this.selected = null
                this.setLoading = false
                this.alertMutation({
                    show: true,
                    text: 'Certificate assignment updated!',
                    type: "succes"
                })
                this.getData()
            }).catch(() => {
                this.setLoading = false
                this.alertMutation({
                    show: true,
                    text: 'Something went wrong',
                    type: "error"
                })
            })
        }
    }
}

</script>