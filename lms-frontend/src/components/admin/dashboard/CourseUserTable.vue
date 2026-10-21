<template>
    <section>
        <v-btn dense text color="primary" @click="search = '', $emit('back')" class="mb-2">
            <v-icon small>
                mdi-arrow-left
            </v-icon>
            BACK
        </v-btn>

        <h4 class="poppins mb-1">
            LIST OF {{show}} : {{ listName }} 
        </h4>
        <div class="poppins mb-3 f14 fw500 secondary-2--text">
            ( from {{ $dateFormat.mmDDyy(dates[0]) }} to {{ $dateFormat.mmDDyy(dates[1]) }} )
        </div>
        <section class="d-flex flex-wrap justify-space-between mb-3">
            <SearchBar :placeholder="'Search user'" :value.sync="search" @search="$emit('search', search)" class="mb-5" @clear="() => { search='', $emit('clear')}"/>
            
            <section class="d-flex flex-row align-center ml-auto">
                <v-chip class="fw400 f15" outlined>
                    <v-icon class="mr-2" color="primary">mdi-account-group-outline</v-icon>
                    <span class="fw600 mr-1 primary--text">{{ totalCount }}</span> users
                </v-chip>
                <ButtonExport 
                    @click="exportTable"
                    :loading="exportLoading"
                    :disabled="loading || items.length === 0"
                    class="ml-3"/>
            </section>
        </section>

        <v-expand-transition>
            <div>
                <v-data-table
                    :headers="headers"
                    :items="items"
                    class="elevation-1 poppins"
                    :loading="loading"
                    hide-default-footer
                    :items-per-page.sync="itemsPerPage"
                    >
                    <template v-slot:item.enrollments.0.created_at="{ item }">
                        <span> {{ $dateFormat.mmDDyy(item.enrollments[0].created_at) }} </span>
                    </template>
                    <template v-slot:item.user.enrollments.0.created_at="{ item }">
                        <span> {{ $dateFormat.mmDDyy(item.user.enrollments[0].created_at) }} </span>
                    </template>
                    <template v-slot:item.status="{ item }">
                        <v-chip v-if="item.status === 'active'" small dense color="#7BC14533" class="success--text ma-auto">ACTIVE</v-chip>
                        <v-chip v-if="item.status === 'inactive'" small dense color="#BDBDBD33" class="secondary-2--text ma-auto">INACTIVE</v-chip>
                    </template>
                </v-data-table>
                <FormPagination 
                    :pageCount="pageCount" 
                    :page="page"
                    :paginate="paginate"
                    @page="(e) => $emit('page', e)" 
                    @paginate="(e) => $emit('paginate', e)"/>
            </div>
        </v-expand-transition>
    </section>
</template>

<script>
import { mapActions, mapState, mapMutations } from 'vuex';
export default {
    props: {
        items: {
            type: Array,
            default: function () {
                return [];
            }
        },
        show: {
            type: String,
            default: ''
        },
        listName: {
            type: String,
            default: ''
        },
        loading: {
            type: Boolean,
            default: false
        },
        totalCount: {
            type: Number,
            default: 0
        },
        pageCount: {
            type: Number,
            default: 1
        },
        page: {
            type: Number,
            default: 1
        },
        paginate: {
            type: String,
            default: '10'
        },
        filter: {
            type: String,
            default: ''
        },
        value: {
            type: Number || null,
            default: null
        },
        dates: {
            type: Array,
            default: []
        }
    },
    data: () => ({
        headers: [ 
            { text: "Enrollment Date", value: "enrollments.0.created_at", align: 'center', sortable: false },
            { text: "First Name", value: "first_name", sortable: false },
            { text: "Middle Name", value: "middle_name", align: 'center', sortable: false },
            { text: "Last Name", value: "last_name", align: 'center', sortable: false },
            { text: "Suffix", value: "suffix", align: 'center', sortable: false },
            { text: "Email", value: "email", align: 'center', sortable: false },
            { text: "Status", value: "status", align: 'center', sortable: false },
        ],
        search: '',
        exportLoading: false,
    }),
    mounted(){
        if(this.filter === 'Total Users per Course') {

            if(this.headers[0].text !== 'Enrollment Date') {
                this.headers.unshift({ text: "Enrollment Date", value: "enrollments.0.created_at", align: 'center', sortable: false })
            }

            this.headers[0].value = "enrollments.0.created_at"
        } else {
            this.headers = [ 
                { text: "First Name", value: "first_name", sortable: false},
                { text: "Middle Name", value: "middle_name", align: 'center', sortable: false },
                { text: "Last Name", value: "last_name", align: 'center', sortable: false },
                { text: "Suffix", value: "suffix", align: 'center', sortable: false },
                { text: "Email", value: "email", align: 'center', sortable: false },
                { text: "Status", value: "status", align: 'center', sortable: false },
            ]
        }
    },
    methods: {
        ...mapMutations(['alertMutation']),

        ...mapActions('admin', ['exportAdminUserAnalyticsAction', 'exportAdminCompletersAnalyticsAction']),

        exportTable() {
            if (this.show === 'COMPLETERS') {
                this.exportLoading = true
                this.exportAdminCompletersAnalyticsAction({ 
                    filter: this.filter, 
                    value: this.value, 
                    field: this.listName,
                    search: this.search, 
                    date_from: this.dates[0], 
                    date_to: this.dates[1] 
                }).then(res => {
                    this.exportLoading = false
                    this.alertMutation({
                        show: true,
                        text: 'Export is being processed in the background.',
                        type: "success"
                    })
                }).catch(() => {
                    this.exportLoading = false
                    this.alertMutation({
                        show: true,
                        text: 'Something went wrong',
                        type: "error"
                    })
                })
            } else {
                this.exportLoading = true
                this.exportAdminUserAnalyticsAction({ 
                    filter: this.filter, 
                    value: this.value, 
                    field: this.listName,
                    search: this.search, 
                    date_from: this.show === 'ENROLLEES' ? this.dates[0] : '', 
                    date_to: this.show === 'ENROLLEES' ? this.dates[1] : ''
                }).then(res => {
                    this.exportLoading = false
                    this.alertMutation({
                        show: true,
                        text: 'Export is being processed in the background.',
                        type: "success"
                    })
                }).catch(() => {
                    this.exportLoading = false
                    this.alertMutation({
                        show: true,
                        text: 'Something went wrong',
                        type: "error"
                    })
                })
            }
        }
    },
    computed: {
        itemsPerPage(){
            return Number(this.paginate)
        }
    },
    watch: {
        filter(val){
            if(val === 'Total Users per Course') {

                if(this.headers[0].text !== 'Enrollment Date') {
                    this.headers.unshift({ text: "Enrollment Date", value: "enrollments.0.created_at", align: 'center', sortable: false })
                }

                if(val === 'COMPLETERS') {
                    this.headers[0].value = "user.enrollments.0.created_at"
                } else {
                    this.headers[0].value = "enrollments.0.created_at"
                }
            } else {
                this.headers = [ 
                    { text: "First Name", value: "first_name", sortable: false},
                    { text: "Middle Name", value: "middle_name", align: 'center', sortable: false },
                    { text: "Last Name", value: "last_name", align: 'center', sortable: false },
                    { text: "Suffix", value: "suffix", align: 'center', sortable: false },
                    { text: "Email", value: "email", align: 'center', sortable: false },
                    { text: "Status", value: "status", align: 'center', sortable: false },
                ]
            }
        },
        show(val) {
            if(this.filter === 'Total Users per Course') {

                if(this.headers[0].text !== 'Enrollment Date') {
                    this.headers.unshift({ text: "Enrollment Date", value: "enrollments.0.created_at", align: 'center', sortable: false })
                }

                if(val === 'COMPLETERS') {
                    this.headers[0].value = "user.enrollments.0.created_at"
                } else {
                    this.headers[0].value = "enrollments.0.created_at"
                }
            } else {
                this.headers = [ 
                    { text: "First Name", value: "first_name", sortable: false},
                    { text: "Middle Name", value: "middle_name", align: 'center', sortable: false },
                    { text: "Last Name", value: "last_name", align: 'center', sortable: false },
                    { text: "Suffix", value: "suffix", align: 'center', sortable: false },
                    { text: "Email", value: "email", align: 'center', sortable: false },
                    { text: "Status", value: "status", align: 'center', sortable: false },
                ]
            }
        }
    }
}

</script>