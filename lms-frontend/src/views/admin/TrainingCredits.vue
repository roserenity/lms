<template>
    <section id="class-category">
        <section class="d-flex flex-wrap justify-space-between">
            <SearchBar :placeholder="'Search training credits'" :value.sync="search" 
            @clear="() => { search = '', page = 1, getData() }" @search="() => {
                page = 1
                getData()
            }" class="mb-5" />
    
            <ButtonPrimary 
            :outlined="true"
            @click="addTrainingCreditsDialog = true"
            :label="'Add Training Credits'"
            :icon="'mdi-plus'"
            class="mb-3 ml-auto"
            />
        </section>
        <section v-if="totalCount !== 0" class="text-right">
            <FormLabel :label="`${totalCount} result/s`"/>
        </section>
        <v-data-table
            :headers="headers"
            :items="classes"
            class="elevation-1 poppins"
            :loading="loading"
            hide-default-footer
            :page.sync="page"
            :items-per-page.sync="itemsPerPage"
        >
            <template v-slot:item.action="{ item }">
                <v-btn icon color="primary" @click="addTrainingCreditsDialog = true, selectedItem = item">
                    <v-icon small>mdi-pencil-outline</v-icon>
                </v-btn>
                <v-btn icon color="primary" @click="selectedItem = item, deletedialog=true">
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
    
            <v-dialog persistent v-model="addTrainingCreditsDialog" max-width="450">
                <v-card class="rounded-lg">
                    <v-card-title class="d-flex align-center justify-center px-4">
                        <h5 class="primary--text poppins fw600 text-center" v-if="!selectedItem">Add Training Credit</h5>
                        <h5 class="primary--text poppins fw600 text-center" v-else>Edit Training Credit</h5>
                        <v-spacer/>
                        <v-btn icon @click="() => { addTrainingCreditsDialog = false, selectedItem = null }" dense>
                            <v-icon size="22">mdi-close</v-icon>
                        </v-btn>
                    </v-card-title>
                    <v-form @submit.prevent="add" ref="form">
                    <div class="mx-4 mb-4">
                        <FormTextFieldRequired
                            :label="'TRAINING CREDIT *'"
                            :value.sync="training_credit"
                            :readonly="savingLoading"
                        />
                    </div>
                    <v-divider/>
                    <v-card-actions class="d-flex align-center justify-end mx-2">
                        <v-btn text @click="() => {
                            training_credit = '',
                            addTrainingCreditsDialog = false,
                            selectedItem = null
                        }">Cancel</v-btn>
                        <ButtonPrimary
                            @click="saveTrainingCredits"
                            :loading="savingLoading"
                            :label="selectedItem ? 'Update' : 'Save'"
                        />
                    </v-card-actions>
                    </v-form>
                </v-card>
            </v-dialog>
            <DeleteDialog 
            :dialog="deletedialog"
            type="Training Credit"
            :loading="deleteLoading"
            @close="deletedialog=false"
            @delete="deleteTrainingCredit"
            />
        </section>
    </template>
        
    <script>
        import { mapActions, mapState, mapMutations } from 'vuex'
        import Table from '../../components/admin/course/ClassTable.vue'
        import searchAndPaginateMixin from '@/mixins/searchAndPaginateMixin';
        import ButtonPrimary from '@/components/reusable/ButtonPrimary.vue';
        
        export default {
        mixins: [searchAndPaginateMixin],
        components: { Table },
        data: () => ({
            addTrainingCreditsDialog: false,
            toast: false,
            text: '',
            loading: false,
            savingLoading: false,
            deleteLoading: false,
            classes: [],
            totalCount: 0,
            pageCount: 1,
            search: '',
            headers: [
            {
                text: 'Training Credit',
                value: 'name',
                align: 'start',
                sortable: false
            },
            {
                text: 'Action',
                value: 'action',
                align: 'center',
                sortable: false
            },
            ],
            training_credit: '',
            selectedItem: null,
            deletedialog: false
        }),
        computed: {
            ...mapState({
            errors: (state) => state.errors,
            }), 
    
            itemsPerPage(){
            return Number(this.paginate)
            },
    
            paginationPaginate(){
                return String(this.paginate)
            }
    
        },
        methods: {
            ...mapActions('admin', ['getTrainingCreditsAction', "addTrainingCreditAction", "deleteTrainingCreditAction", "updateTrainingCreditAction"]),
            ...mapMutations(['alertMutation']),
        
            getData(){
            this.loading = true
            this.getTrainingCreditsAction({page: this.page, paginate: Number(this.paginate), search: this.search}).then(res => {
                this.loading=false
                this.classes = res.data
                this.pageCount = res.last_page
                this.totalCount = res.total
            }).catch(() => {
                this.loading = false
                this.classes = []
                this.pageCount = 0
                this.totalCount = 0
                this.alertMutation({
                show: true,
                text: 'Something went wrong',
                type: "error"
                })
            })
            },
    
            saveTrainingCredits() {
                this.savingLoading = true
                if(this.selectedItem) {
                    this.updateTrainingCreditAction({ name: this.training_credit, id: this.selectedItem.id }).then(() => {
                        this.alertMutation({
                            show: true,
                            text: 'Successfully updated a training credit',
                            type: "success"
                        })
                        this.savingLoading = false
                        this.addTrainingCreditsDialog = false
                        this.selectedItem = null
                        this.getData();
                    }).catch(() => {
                        this.savingLoading = false
                        this.alertMutation({
                            show: true,
                            text: 'Something went wrong.',
                            type: "error"
                        })
                    })
                } else {
                    this.addTrainingCreditAction({ name: this.training_credit }).then(() => {
                        this.alertMutation({
                            show: true,
                            text: 'Successfully added a new training credit',
                            type: "success"
                        })
                        this.savingLoading = false
                        this.addTrainingCreditsDialog = false
                        this.getData();
                    }).catch(() => {
                        this.savingLoading = false
                        this.alertMutation({
                            show: true,
                            text: 'Something went wrong.',
                            type: "error"
                        })
                    })
                }
            },
    
            deleteTrainingCredit(){
                this.deleteLoading = true
                this.deleteTrainingCreditAction({ id: this.selectedItem.id }).then(() => {
                    this.alertMutation({
                        show: true,
                        text: 'Successfully deleted a category',
                        type: "success"
                    })
                    this.deleteLoading = false
                    this.deletedialog = false
                    this.getData();
                }).catch(() => {
                    this.deleteLoading = false
                    this.deletedialog = false
                    this.alertMutation({
                        show: true,
                        text: 'Something went wrong.',
                        type: "error"
                    })
                })
            }
        },
    
        watch: {
            addTrainingCreditsDialog(val) {
                if(val) {
                    if(this.selectedItem) {
                        this.training_credit = this.selectedItem.name
                    } else this.training_credit = ''
                }
            }
        }
    
    }
</script>    