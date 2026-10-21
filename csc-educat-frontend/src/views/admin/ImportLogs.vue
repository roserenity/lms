<template>
    <v-card class="custom-border border pa-7 mb-5">

        <section class="d-flex flex-wrap justify-space-between align-center">

            <section v-if="totalCount !== 0" class="text-right mb-3">
                <FormLabel :label="`Import Logs: ${totalCount} result/s`"/>
            </section>

            <ButtonPrimary 
                label="Delete All"
                @click="deleteAll"
                :loading="deleteLoading && selected_id === null"
                :disabled="deleteLoading || logs.length === 0"
                class="poppins mb-3 ml-auto"/>
        </section>
        <v-data-table
            :headers="import_logs_tbl"
            :items="logs"
            :loading="loading"
            class="poppins"
            :page.sync="page"
            :items-per-page.sync="itemsPerPage"
            hide-default-footer>
            
            <template v-slot:item.status="{ item }">
                <v-alert dense :color="item.status === 'success' ? '#7BC14533' : '#BDBDBD33'" class="ma-auto f12 text-uppercase" :class="item.status === 'success' ? 'success--text' : 'secondary-2--text'">
                    {{ item.status }}
                </v-alert>
            </template>
            
            <template v-slot:item.message="{ item }">
                <div class="d-flex flex-column my-2">
                    {{ item.message }}
                    <span class="f11">{{ $dateFormat.dayDateTime(item.created_at) }}</span>
                </div>
            </template>
            
            <template v-slot:item.action="{ item }">
                <v-btn icon dense color="primary" :loading="deleteLoading && selected_id === item.id" @click="deleteLog(item.id)">
                    <v-icon small>
                        mdi-trash-can-outline
                    </v-icon>
                </v-btn>
            </template>
        </v-data-table>
        
        <FormPagination 
            :pageCount="pageCount" 
            :page="page"
            :paginate="paginate"
            @page="(e) => {page = e, getLogs()}" 
            @paginate="(e) => {paginate = e, page = 1, getLogs()}"/>

    </v-card>
</template>

<script>
import { mapActions, mapMutations, mapState } from 'vuex'
import { import_logs_tbl } from "@/constants/tblheaders/shared"

export default {
    data: () => ({
        loading: false,
        deleteLoading: false,
        totalCount: 0,
        pageCount: 1,
        paginate: '10',
        page: 1,
        search: '',
        selected_id: null,
        import_logs_tbl
    }),
    computed: {
        ...mapState('admin', {
            logs: (state) => state.logs
        }),

        itemsPerPage(){
            return Number(this.paginate)
        },
    },

    mounted(){
        this.getLogs()
    },

    methods: {
        ...mapActions('admin', ['getImportLogsAction', 'deleteImportLogsAction', 'deleteAllImportLogsAction']),
        ...mapMutations(['alertMutation']),

        getLogs(){
            this.loading = true
            this.getImportLogsAction({ paginate: Number(this.paginate), page: this.page }).then(res => {
                console.log(res)
                this.loading = false
                this.totalCount = res.total
                this.pageCount = res.last_page
                this.page = res.current_page
                this.paginate = String(res.per_page)
            }).catch(e => {
                this.loading = false
                this.alertMutation({
                    show: true,
                    text: 'Something went wrong in fetching import logs',
                    type: "error"
                })
            })
        },

        deleteLog(id){
            this.selected_id = id
            this.deleteLoading = true
            this.deleteImportLogsAction(id).then(() => {
                this.deleteLoading = false
                this.alertMutation({
                    show: true,
                    text: 'Log successfully deleted',
                    type: "success"
                })
                this.getLogs()
            }).catch(e => {
                this.deleteLoading = false
                this.alertMutation({
                    show: true,
                    text: 'Something went wrong in deleting import logs',
                    type: "error"
                })
            })
        },

        deleteAll(){
            this.selected_id = null
            this.deleteLoading = true
            this.deleteAllImportLogsAction().then(() => {
                this.deleteLoading = false
                this.alertMutation({
                    show: true,
                    text: 'Logs successfully deleted',
                    type: "success"
                })
                this.getLogs()
            }).catch(e => {
                this.deleteLoading = false
                this.alertMutation({
                    show: true,
                    text: 'Something went wrong in deleting all import logs',
                    type: "error"
                })
            })
        }
    }
}

</script>