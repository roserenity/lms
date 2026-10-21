<template>
    <section>
        <LinearProgress v-if="loading" class="mb-3"/>

        <div v-if="$vuetify.breakpoint.smAndDown">
            <section class="d-flex align-center justify-end">
                <FilterMenu
                    :initialFilter="filter"
                    @resetFilters="clearFilters()"
                    @applyFilters="() => { page = 1, updateQuery(), getTicketCount() }"
                >
                    <section>
                        <label class="caption">CATEGORY</label>
                        <v-select
                            :items="tabs"
                            dense
                            outlined
                            hide-details="auto"
                            v-model="filter.category"
                            item-text="text"
                            item-value="id"
                            class="general-custom-field poppins mb-3 f12"
                        />
                        <label class="caption">STATUS</label>
                        <v-select
                            :items="ticketStatus"
                            outlined
                            dense
                            hide-details="auto"
                            v-model="filter.status"
                            item-text="text"
                            item-value="value"
                            class="general-custom-field poppins mb-3 f12"
                            width="100"
                        />

                        <label class="caption">DATE RECEIVED FROM:</label>
                        <FormDatePicker 
                            :val="filter.date_from"
                            :max="maxDate"
                            class_="col-12"
                            @apply="(e) => {
                                filter.date_from = e
                                minDate = e
                            }"/>

                        <label class="caption">DATE RECEIVED TO:</label>
                        <FormDatePicker 
                                :val="filter.date_to"
                                :min="minDate"
                                class_="col-12"
                                @apply="(e) => {
                                    filter.date_to = e
                                    maxDate = e
                                }"/>
                    </section>
                </FilterMenu>
                
                <ButtonExport 
                    @click="exportTickets"
                    :loading="exportLoading"
                    :disabled="loading || tickets.length === 0"
                    class="my-2"/>
            </section>
            <v-divider class="my-3"/>
            <v-row class="mb-5">
                <v-col cols="12" md="4" v-for="(item, i) in items" :key="i" justify="center">
                    <TopCard :item="item" :loading="cardLoading"/>
                </v-col>
            </v-row>
        </div>

        <div :style="$vuetify.breakpoint.smAndDown ? 'display: flex; flex-direction: column' : ''">
            <v-card v-if="$vuetify.breakpoint.smAndDown ? !selected : true" class="custom-border border pa-2"  :style="!$vuetify.breakpoint.smAndDown ? 'height: 80vh; position: fixed; overflow-y: auto; width: 23%; margin-top: -10px' : 'order: 1'">
                <v-card-text>
                    <section class="d-flex flex-row align-center pa-0 mb-5">
                        <v-text-field
                            outlined
                            dense
                            flat
                            class="custom-border general-custom-field poppins fw500"
                            hide-details
                            prepend-inner-icon="mdi-magnify"
                            :append-icon="search.trim() === '' ? '' : 'mdi-close'"
                            :placeholder="'Search user'"
                            @input="($event) => { search = $event }"
                            @click:append="clearSearch"
                        />
                        <v-btn color="primary" class="poppins" @click="onSearch"> Search</v-btn>
                    </section>
                    <div v-if="!loading && tickets.length === 0" class="poppins fw600 f13 secondary-4--text font-italic"> No Tickets Available. </div>
                    <v-card :disabled="disableSelection" outlined v-for="ticket in tickets" :key="ticket.id" class=" hover:cursor-pointer mb-3" :elevation="selected ? (selected.id === ticket.id ? '1' : '') : ''" @click="selected = ticket">
                            <v-alert
                                border="right"
                                :color="ticketStatus[ticket.status].color"
                                colored-border
                                class="mb-0"
                                tile
                            >
                                <section class="primary--text poppins fw500 mt-0" order="2">
                                    <v-avatar size="20" class="mr-2">
                                        <v-img 
                                        :src="ticket.user.image? ticket.user.image.url : require('@/assets/default-photo.png')"
                                        v-on:error="ticket.user.image = null"></v-img>
                                    </v-avatar>
                                    {{ ticket.submitted_by }}
                                </section>
                            </v-alert>
                        <v-chip small class="f9 mx-3 mt-3 mb-3 text-uppercase" > {{  ticket.concern_category  }}</v-chip>
                        <v-divider />
                        <v-card-text  style="height:40px;" class="overflow-y-hidden ellipsis">
                            {{ ticket.content.length === 0 ? '-' : ticket.content }} 
                        </v-card-text>
                        <v-card-text class="text-right f11">
                            {{$dateFormat.dayDateTime(ticket.created_at)}}
                        </v-card-text>
                    </v-card>
                    <FormPagination 
                        :wrap="true"
                        :pageCount="pageCount" 
                        :page="page"
                        :paginate="paginationPaginate"
                        @page="onPageChange" 
                        @paginate="onPaginateChange"/>
                    
                </v-card-text>
            </v-card>
            <div v-if="$vuetify.breakpoint.smAndDown ? selected : true" :style="!$vuetify.breakpoint.smAndDown ? 'float: right; width: 70%;' : ''">
                <div  v-if="!$vuetify.breakpoint.smAndDown">
                    <section class="d-flex align-center justify-end">
                        <FilterMenu
                            :initialFilter="filter"
                            @resetFilters="clearFilters()"
                            @applyFilters="() => { page = 1, updateQuery(), getTicketCount() }"
                        >
                            <section>
                                <label class="caption">CATEGORY</label>
                                <v-select
                                    :items="tabs"
                                    dense
                                    outlined
                                    hide-details="auto"
                                    v-model="filter.category"
                                    item-text="text"
                                    item-value="id"
                                    class="general-custom-field poppins mb-3 f12"
                                />
                                <label class="caption">STATUS</label>
                                <v-select
                                    :items="ticketStatus"
                                    outlined
                                    dense
                                    hide-details="auto"
                                    v-model="filter.status"
                                    item-text="text"
                                    item-value="value"
                                    class="general-custom-field poppins mb-3 f12"
                                    width="100"
                                />

                                <label class="caption">DATE RECEIVED FROM:</label>
                                <FormDatePicker 
                                    :val="filter.date_from"
                                    :max="maxDate"
                                    class_="col-12"
                                    @apply="(e) => {
                                        filter.date_from = e
                                        minDate = e
                                    }"/>

                                <label class="caption">DATE RECEIVED TO:</label>
                                <FormDatePicker 
                                    :val="filter.date_to"
                                    :min="minDate"
                                    class_="col-12"
                                    @apply="(e) => {
                                        filter.date_to = e
                                        maxDate = e
                                    }"/>
                            </section>
                        </FilterMenu>
                        <ButtonExport 
                            @click="exportTickets"
                            :loading="exportLoading"
                            :disabled="loading || tickets.length === 0"
                            class="my-2"/>
                    </section>
                    <v-row class="mb-5">
                        <v-col cols="12" md="4" v-for="(item, i) in items" :key="i" justify="center">
                            <TopCard :item="item" :loading="cardLoading"/>
                        </v-col>
                    </v-row>
                </div>
                <v-card class="custom-border border pa-5" v-if="selected" >
                    <div class="d-flex justify-end">
                        <v-btn icon small @click="selected = null">
                            <v-icon small>
                                mdi-close
                            </v-icon>
                        </v-btn>
                    </div>
                    <TicketMessages 
                        :ticket="selected" 
                        @getTickets="() => 
                        {getData(), getTicketCount()}" 
                        @setTicket="(e) => selected = e"
                        @setDisableSelection="(e) => disableSelection = e"/>
                </v-card>
            </div>
        </div>
    </section>
</template>

<style>
.wrap {
    overflow-wrap: anywhere !important;
    word-wrap: break-word !important;
    word-break: normal !important;
    hyphens: auto !important;
}
</style>

<script>
import { defineComponent, ref } from '@vue/composition-api'
import { admin_ticket_tbl } from '@/constants/tblheaders/ticket'
import TicketDialog from '@/components/admin/ticket/TicketDialog.vue';
import TicketMessages from '@/components/admin/ticket/TicketMessages.vue';
import { mapState, mapActions, mapMutations } from 'vuex';
import { ticketStatus } from '@/constants/colors'
import TopCard from '@/components/reusable/TopCard.vue'
import searchAndPaginateMixin from '@/mixins/searchAndPaginateMixin';

export default defineComponent({
    mixins: [searchAndPaginateMixin],
    components: {
        TopCard,
        TicketDialog,
        TicketMessages
    },
    data: () => ({
        disableSelection: false,
        admin_ticket_tbl,
        loading: true,
        exportLoading: false, 
        cardLoading: false,
        dialog: false,
        selectedTicket: null,
        pageCount: 1,
        totalCount: 0,
        selected: null,
        items: [
            {
                title: 'Open Tickets',
                count: '-',
                color: '#2196F333', 
                outline: '#2196F3'
            },
            {
                title: 'Pending Tickets',
                count: '-',
                color: '#F9A82533',
                outline: '#F9A825'
            },
            {
                title: 'On Hold Tickets',
                count: '-',
                color: '#9E9E9E33',
                outline: '#9E9E9E'
            },
            {
                title: 'Solved Tickets',
                count: '-',
                color: '#388E3C33',
                outline: '#388E3C'
            },
            {
                title: 'Closed Tickets',
                count: '-',
                color: '#21212133',
                outline: '#212121'
            },
        ],
        tabs: [ 
            {
                text: "All",
                id: "",
                count: 0
            },
            {
                text: "Platform",
                id: "platform",
                count: 0
            },
            {
                text: "Content",
                id: "content",
                count: 0
            },
            {
                text: "Assessment",
                id: "assessment",
                count: 0
            },
            {
                text: "Others",
                id: "others",
                count: 0
            }
        ],
        menu: false,
        ticketStatus,
        minDate: '',
        maxDate: ''
    }),
    methods: {
        ...mapActions('admin', ['getTicketsAction', 'getTicketCountAction', 'ticketExportAction']),
        ...mapMutations(['alertMutation']),

        openDialog(item){
            this.dialog = true
            this.selectedTicket = item
        },

        closeDialog(){
            this.dialog=false
        },

        getData(){
            this.loading = true
            this.getTicketsAction({ ...this.filter, paginate: Number(this.paginate), page: this.page, search: this.search }).then(res => {
                this.loading = false
                this.paginate = String(res.per_page)
                this.page = res.current_page
                this.pageCount = res.last_page
                this.totalCount = res.total
            }).catch(() => this.loading = false)
        },

        getTicketCount(){
            this.cardLoading = true

            this.getTicketCountAction(this.filter).then(res => {
                this.items[0].count = res.total_open_tickets
                this.items[1].count = res.total_pending_tickets
                this.items[2].count = res.total_on_hold_tickets
                this.items[3].count = res.total_solved_tickets
                this.items[4].count = res.total_closed_tickets
                this.cardLoading = false
            }).catch(() => this.cardLoading = false)
        },

        getAllTickets(){
            this.cardLoading = true
            this.getTicketCountAction(this.filter).then(res => {
                this.items[0].count = res.total_open_tickets
                this.items[1].count = res.total_pending_tickets
                this.items[2].count = res.total_on_hold_tickets
                this.items[3].count = res.total_solved_tickets
                this.items[4].count = res.total_closed_tickets
                this.cardLoading = false
                this.getData() 
            }).catch(() => {
                this.cardLoading = false
            })
        },

        exportTickets(){
            this.exportLoading = true
            this.ticketExportAction({...this.filter, search: this.search}).then(res => {
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
                    text: 'Something went wrong in exporting',
                    type: "error"
                })
            })
        },

        clearFilters(){
            this.filter = {
                category: '',
                status: '',
                date_from: '',
                date_to: ''
            }

            this.updateQuery()
            this.minDate = ''
            this.maxDate = ''
        }
    },
    computed: {
        ...mapState('admin', {
            tickets: (state) => state.tickets
        }),

        getHighlight(){
            return this.$vuetify.theme.currentTheme['primary-1']
        },

        paginationPaginate(){
            return String(this.paginate)
        }
    },
    mounted(){
        this.cardLoading = true
        this.getTicketCountAction(this.filter).then(res => {
            this.items[0].count = res.total_open_tickets
            this.items[1].count = res.total_pending_tickets
            this.items[2].count = res.total_on_hold_tickets
            this.items[3].count = res.total_solved_tickets
            this.items[4].count = res.total_closed_tickets
            this.cardLoading = false
            this.getData() 
        }).catch(() => {
            this.cardLoading = false
        })
        
    },
    watch: {
        '$route.query': {
            handler(newQuery) {
                if(newQuery.ticket_uuid) {
                    this.selected = { uuid: this.$route.query.ticket_uuid }
                }
            },
            immediate: true,
            deep: true
        }
    },
})
</script>
