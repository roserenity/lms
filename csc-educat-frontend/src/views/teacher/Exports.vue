<template>
    <div>
        <section class=" d-flex flex-row align-center ml-auto ">
            <ButtonPrimary 
                :icon="'mdi-restore'"
                :outlined="true"
                :label="'Fetch progress'"
                @click="getData()"
            />
            <FilterMenu
                :initialFilter="filter"
                @resetFilters="resetFilters"
                @applyFilters="() => { page = 1, updateQuery() }"
                :width="'500'"
                >
                <v-sheet max-height="300" class="overflow-y-auto scroller">
                    <section class="my-3">
                        <label class="caption">TYPE</label>
                        <v-select 
                            outlined 
                            dense 
                            :items="activities"
                            item-value="id"
                            item-text="text"
                            hide-details="auto"
                            multiple
                            v-model="filter.type"
                        >
                        </v-select>
                    </section>
                </v-sheet>
            </FilterMenu>
        </section>
        <section class="text-right">
            <FormLabel :label="`${totalCount === 0 ? 'No' : totalCount} result/s`"/>
        </section>
        <v-data-table
            :headers="exports_tbl"
            :items="file_exports"
            :loading="loading"
            class="elevation-1 poppins f10"
            hide-default-footer
            :items-per-page.sync="itemsPerPage"
            >
                <template v-slot:item.exported_at="{ item }" class="text-capitalize">
                    {{$dateFormat.mmDDyy(item.exported_at)}}
                </template>
                <template v-slot:item.type="{ item }" class="text-capitalize">
                    <span class="text-capitalize"> {{item.type.replaceAll('_', ' ')}} </span>
                </template>
                <template v-slot:item.status="{ item }" class="text-capitalize">
                    <span class="text-capitalize"> {{item.status}} </span>
                </template>
                <template v-slot:item.url="{ item }" class="text-capitalize">
                    <v-btn text v-if="item.status === 'completed'" color="primary" small target="_blank" :href="item.url" class="poppins text-capitalize fw600 cursor-pointer" download>
                        <v-icon left size="24">mdi-download</v-icon> Ready for download
                    </v-btn>
                </template>
                <template v-slot:item.action="{ item }" class="text-capitalize">
                    
                    <v-btn @click.stop="() => { selected = item.id, deleteFile(item.id) }" text color="primary" small class="poppins text-capitalize fw600 cursor-pointer">
                        <v-icon size="18">mdi-trash-can-outline</v-icon>
                    </v-btn>
                </template>
            </v-data-table>
            <FormPagination 
                :pageCount="pageCount" 
                :page="page"
                :paginate="paginationPaginate"
                @page="(e) => { page = e, updateQuery() }" 
                @paginate="(e) => { paginate = e, page = 1, updateQuery() }"/>
    </div>
</template>

<script>
import searchAndPaginateMixin from '@/mixins/searchAndPaginateMixin';
import { exports_tbl } from '@/constants/tblheaders/exports';
import { mapActions, mapMutations, mapState } from 'vuex';

export default {
    mixins: [searchAndPaginateMixin],
    data: () => ({
        exports_tbl,
        loading: false,
        filterLoading: false,
        cardLoading: false,
        downloadLoading: false,
        deleteLoading: false,
        selected: null,
        pageCount: 1,
        totalCount: 0,
        prevRoute: null,
        otherFilters: [],
        filter: {
            type: ['course_evaluation', 'module_evaluation', 'invoice', 'assessment', 'course'], 
        },
        activities: [
            { id: "invoice", text: "Invoice" },
            { id: "course_evaluation", text: "Course Evaluation" },
            { id: "module_evaluation", text: "Module Evaluation Summary" },     
            { id: "assessment", text: "Assessment" },   
        ]
    }),

    mounted() {

        if(typeof this.$route.query.type === 'string'){
            this.filter.type = [this.$route.query.type]
        } 

        this.getData()
        
    },
    
    computed: {
        ...mapState('instructor', {
            file_exports: (state) => state.file_exports
        }),

        paginationPaginate(){
            return String(this.paginate)
        },

        itemsPerPage(){
            return Number(this.paginate)
        },
    },

    methods: {
        ...mapMutations(['alertMutation']),
        ...mapActions('instructor', ['getExportsAction', 'downloadExportedFileAction', 'deleteExportedFileAction']),

        getData(){
            this.loading = true
            this.getExportsAction({
                page: this.page, 
                paginate: Number(this.paginate),
                search: this.search,
                ...this.filter
            }).then((res) => {
                this.loading = false
                this.pageCount = res.last_page
                this.totalCount = res.total
            }).catch(() => {
                this.loading = false
                this.pageCount = 0
                this.totalCount = 0
                this.alertMutation({
                    show: true,
                    text: 'Something went wrong',
                    type: "error"
                })
            })
        },
  
        beforeRouteEnter(to, from, next) {
            next(vm => {
                vm.prevRoute = from 
            })
        },

        resetFilters(){
            this.filter.type = ['course_evaluation', 'module_evaluation', 'invoice', 'assessment', 'course']
        },

        download(url){
            this.downloadExportedFileAction({url: url}).then(res =>  
                window.open(res)
            ).catch(errs => 
                this.alertMutation({
                    show: true,
                    text: 'Unexpected error encountered while downloading.',
                    type: "error"
                })
            )
        },

        deleteFile(id) {
            this.deleteLoading = true
            this.deleteExportedFileAction(id).then((res) => {
                this.deleteLoading = false
                this.alertMutation({
                    show: true,
                    text: 'File deleted successfully',
                    type: "success"
                })
                this.getData()
            }).catch(() => {
                this.deleteLoading = false
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