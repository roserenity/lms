<template>
    <section>
        <v-row class="fade">
            <v-col v-if="$vuetify.breakpoint.smAndDown ? ( selected ? false : true) : true" :cols="$vuetify.breakpoint.smAndDown ? '12' : (selected ? '4' : '12')">
                <v-card class="custom-border border pa-2">
                    <v-card-text>
                        <section class="d-flex justify-end mb-3">
                            <FilterMenu
                                :initialFilter="filter"
                                @resetFilters="() => { page = 1, resetFilters(), getTickets() }"
                                @applyFilters="() => { page = 1, getTickets() }"
                                >
                                <v-sheet max-height="350" class="overflow-y-auto scroller">
                                    <label class="caption">CATEGORY</label>
                                    <v-select
                                        :items="category"
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
                                        item-value="id"
                                        class="general-custom-field poppins mb-3 f12"
                                        width="100"
                                    />
                                </v-sheet>
                            </FilterMenu>
                        </section>
                        <v-progress-linear
                            v-if="loading"
                                indeterminate
                                color="primary"
                                rounded
                                height="4"
                                class="mb-3"
                        ></v-progress-linear>
                        <div v-else-if="!loading && tickets.length === 0" class="poppins fw600 f13 secondary-4--text font-italic"> No current tickets. </div>
                        <v-card outlined v-for="ticket in tickets" :key="ticket.id" class=" hover:cursor-pointer mb-5" :elevation="selected ? (selected.id === ticket.id ? '1' : '') : ''" @click="() => { $router.replace({ query: { ticket_uuid: ticket.uuid, timestamp: Date.now() }}), selected = ticket}">
                            <section class='d-flex flex-row justify-space-between'>
                                <v-card-text class="poppins fw500 mt-0" order="2">
                                    <section class="poppins">
                                        <span class="f9">
                                            TICKET NO.
                                        </span>
                                        <h3 class="primary--text fw500">
                                            EDU-{{ ticket.id }}
                                        </h3>
                                    </section> 
                                </v-card-text>
                                <v-card-text class="d-flex flex-column text-uppercase text-right fw500" order="1">
                                    <v-icon :color="ticketStatus[ticket.status].color" small class="ml-auto">
                                        mdi-circle
                                    </v-icon>
                                    {{ ticketStatus[ticket.status].text }}
                                </v-card-text>
                            </section>
                            <v-chip small class="f9 mx-3 mb-3 text-uppercase" > {{  ticket.concern_category  }}</v-chip>
                            <v-divider />
                            <v-card-text  style="height:40px;" class="overflow-y-hidden ellipsis">
                                {{ ticket.content.length === 0 ? '-' : ticket.content }} 
                            </v-card-text>
                            <v-card-text class="text-right f11">
                                {{$dateFormat.dayDateTime(ticket.created_at)}}
                            </v-card-text>
                        </v-card>
                        
                        <FormPagination 
                            :pageCount="pageCount" 
                            :page="page"
                            :paginate="paginate"
                            @page="(e) => {page = e, getTickets()}" 
                            @paginate="(e) => {paginate = e, page = 1, getTickets()}"/>
                        
                    </v-card-text>
                </v-card>
            </v-col>
            <v-col v-if="selected" :cols="$vuetify.breakpoint.smAndDown ? '12' : (selected ? '8' : '12')">
                
                <v-card class="custom-border border pa-5">
                    <div class="d-flex justify-end">
                        <v-btn icon small @click="selected = null">
                            <v-icon small>
                                mdi-close
                            </v-icon>
                        </v-btn>
                    </div>
                    <TicketMessages 
                        :ticket="selected" 
                        @getTickets="getTickets()"
                        @setTicket="(e) => selected = e"/>
                </v-card>
            </v-col>
        </v-row>
    </section>
</template>
<script>
import TicketDialog from './TicketDialog.vue';
import { mapActions, mapMutations, mapState } from 'vuex'
import { ticket_tbl } from '../../../constants/tblheaders/ticket';
import { ticketStatus } from '@/constants/colors';
import TicketMessages from './TicketMessages.vue';

export default {
    props: ["tickets", "tab"],
    computed: {
        ...mapState('usr', {
            tickets: (state) => state.tickets,
        }),
        ...mapState({
            errors: (state) => state.errors,
        }),
    },
    data: () => ({
        ticket_tbl,
        dialog: false,
        selectedTicket: null,
        sortBy: 'id',
        sortDesc: true,
        loading: false,
        pageCount: 1,
        paginate: '10',
        page: 1,
        ticketStatus,
        filter: {
            category: '',
            status: ''
        },
        selected: null,
        menu: false,
        category: [ 
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
    }),
    
    created(){

        this.getTickets()
    },
    methods: {
        ...mapActions('usr', ['getTicketsAction', 'submitTicketAction']),

        openDialog(item){
            this.dialog = true
            this.selectedTicket = item
        },

        closeDialog(){
            this.dialog=false
        },

        getTickets(){
            this.loading = true
            this.getTicketsAction({ category: this.filter.category, status: this.filter.status, page: this.page, paginate: Number(this.paginate)}).then(res => {
                this.page = res.current_page
                this.paginate = String(res.per_page)
                this.pageCount = res.last_page
            }).finally(() => {
                this.loading = false
            })
        },

        resetFilters(){
            this.filter = {
                category: '',
                status: ''
            }
        }
    },
    watch: {
        tab() {
            this.getTickets()
        },
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
    components: { TicketDialog, TicketMessages }
}
</script>