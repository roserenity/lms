<template>
    <section>
        <section class="d-flex flex-wrap justify-space-between">
            <SearchBar 
                :placeholder="'Search question pool'" 
                :value.sync="search" 
                @clear="clearSearch"
                @search="onSearch"
                class="mb-2"/>

            <section class="mb-3 ml-auto">
                <FilterMenu
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
                </FilterMenu>

                <ButtonPrimary
                    @click="createQuestionPool"
                    :label="'Create New Pool'"
                    :icon="'mdi-plus'"
                    class="ml-1"
                    :loading="createLoading"
                />
            </section>
        </section>

        <v-data-table
            :loading="loading"
            :headers="question_pools_tbl"
            :items="question_pools"
            hide-default-footer
            :items-per-page.sync="itemsPerPage"
            class="text--center custom-border poppins f14">
            
            <template v-slot:item.type_of_question_pool="{ item }">
                <span>{{ question_pool_types.find((type) => type.value === item.type_of_question_pool)?.text }}</span>
            </template>
            <template v-slot:item.last_modified="{ item }">
                <span>{{ $dateFormat.calendar(item.updated_at) }}</span>
            </template>
            <template v-slot:item.action="{ item }">
                <v-btn icon color="primary" @click="() => { $router.push({ name: 'Instructor Question Pool Edit' , params: { id: item.id }}) }">
                    <v-icon small>mdi-pencil-outline</v-icon>
                </v-btn>
                <v-btn icon color="primary" @click="() => { selected_question_pool = item.id, deleteDialog = true }">
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
            type="Question Pool"
            :dialog="deleteDialog"
            :loading="deleteLoading"
            @close="deleteDialog = false"
            @delete="deleteQuestionPool"
        />
    </section>
</template>

<script>
import { mapActions, mapState, mapMutations } from 'vuex';
import searchAndPaginateMixin from '@/mixins/searchAndPaginateMixin';
import { question_pools_tbl } from '@/constants/tblheaders/questionpool';
import { question_pool_types } from '@/constants/questionpool';

export default {
mixins: [searchAndPaginateMixin],
data: () => ({
    loading: false,
    createLoading: false,
    deleteLoading: false,
    deleteDialog: false,
    question_pools_tbl,
    totalCount: 0,
    pageCount: 1,
    status: [
        { text: 'ALL', value: '' },
        { text: 'PUBLISHED', value: 'PUBLISH' },
        { text: 'UNPUBLISHED', value: 'UNPUBLISH' },
    ],
    filter: {
        type_of_question_pool: ''
    },
    question_pool_types,
    selected_question_pool: null
}),
methods: {
    ...mapActions('instructor', ['getQuestionPoolsAction', 'createQuestionPoolAction', 'deleteQuestionPoolAction']),
    ...mapMutations(['alertMutation']),

    getData(){
        if(!this.loading) {
            this.loading = true

            this.getQuestionPoolsAction({ paginate: Number(this.paginate), page: this.page, search: this.search, ...this.filter}).then(res => {
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

    createQuestionPool(){
        this.createLoading = true
        this.createQuestionPoolAction().then(res => {
            this.createLoading = false
            this.$router.push({ name: 'Instructor Question Pool Edit' , params: { id: res.id }})
        }).catch(() => {
            this.createLoading = false
            this.alertMutation({
                show: true,
                text: 'Something went wrong.',
                type: "error"
            })
        })
    },

    deleteQuestionPool(){
        this.deleteLoading = true
        this.deleteQuestionPoolAction(this.selected_question_pool).then(res => {
            this.deleteLoading = false
            this.alertMutation({
                show: true,
                text: 'Question Pool has been successfully deleted',
                type: "success"
            })
            this.selected_question_pool = null
            this.deleteDialog = false
            this.getData()
        }).catch(() => {
            this.deleteLoading = false
            this.alertMutation({
                show: true,
                text: 'Something went wrong.',
                type: "error"
            })
            this.selected_question_pool = null
            this.deleteDialog = false
        })
    }
},
computed: {
    ...mapState('instructor', {
        question_pools: (state) => state.question_pools
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
