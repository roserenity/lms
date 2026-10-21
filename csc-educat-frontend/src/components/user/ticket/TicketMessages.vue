<template>
    <section>
        <v-card>
            <section class="d-flex flex-row justify-space-between">
                <v-card-text >
                    <label class="caption">TICKET NO.</label>
                    <h2 class="primary--text fw500 poppins">
                        EDU-{{ ticket.id }}
                    </h2>
                </v-card-text>
                <v-card-text class="text-right text-uppercase fw500">
                    <v-icon :color="ticketStatus[ticket.status].color" small class="ml-auto">
                        mdi-circle
                    </v-icon>
                    {{ ticketStatus[ticket.status].text }}
                </v-card-text>
            </section>
        </v-card>
        <v-card class="mt-3">
            <v-card-text class="primary--text poppins fw500">
                <v-avatar size="50" class="mr-2">
                    <v-img 
                    :src="ticket.user?.image? ticket.user.url : require('@/assets/default-photo.png')"
                    v-on:error="require('@/assets/default-photo.png')"></v-img>
                </v-avatar>
                {{ user?.first_name }} {{user?.last_name }} {{ user?.suffix }}
            </v-card-text>
            <v-chip small class="f9 mx-3 mb-3 text-uppercase" > {{  ticket.concern_category  }}</v-chip>
            <v-divider />
            <v-progress-linear
                v-if="loading"
                    indeterminate
                    color="primary"
                    rounded
                    height="4"
            ></v-progress-linear>
            <v-card-text class="text-right f11">
                {{$dateFormat.dayDateTime(ticket.created_at)}}
            </v-card-text>
            <v-card-text class="">
                {{ticket.content.length === 0 ? '-' : ticket.content}}
            </v-card-text>
            <v-card-text class="">
                <div>
                    <label class="caption f10">FILE ATTACHMENT/S</label>
                </div>
                <div v-if="ticket.file_attachment.length > 0 " class="rounded secondary-5 pa-2 d-flex flex-wrap">
                    <section v-for="item in ticket.file_attachment"
                            :key="item.id">
                        <v-hover v-slot="{ hover }">
                            <v-img 
                            :src="item.url"
                            id="attachment"
                            height="100"
                            width="100"
                            :gradient="!hover && 'to top right, rgba(100,115,201,.20), rgba(25,32,72,.20)'"
                            class="ma-1"
                            @click="viewFull(item.url)"/>
                        </v-hover>
                    </section>
                </div>
                <div v-else class="rounded secondary-5 pa-2">
                    <h5>Nothing Attached</h5>
                </div>
            </v-card-text>
        </v-card>
        <div v-for="replies in messages" :key="replies.id" class="mt-3">
            <v-card>
                <section v-if="replies.from_user_id === user.id">
                    <v-card-text class="primary--text poppins fw500 text-right">
                        {{ replies?.from_user?.first_name }} {{ replies?.from_user?.last_name }} {{ replies?.from_user?.suffix }}
                        <v-avatar size="50" class="ml-2">
                            <v-img 
                            :src="replies?.from_user?.image? replies?.from_user.url : require('@/assets/default-photo.png')"
                            v-on:error="require('@/assets/default-photo.png')"></v-img>
                        </v-avatar>
                    </v-card-text>
                </section>
                <section v-else>
                    <v-card-text class="primary--text poppins fw500 text-left">
                        <v-avatar size="50" class="mr-2">
                            <v-img 
                            :src="replies?.from_user?.image? replies?.from_user.url : require('@/assets/default-photo.png')"
                            v-on:error="require('@/assets/default-photo.png')"></v-img>
                        </v-avatar>
                        {{ replies?.from_user?.first_name }} {{ replies?.from_user?.last_name }} {{ replies?.from_user?.suffix }}
                    </v-card-text>
                </section>
                <v-divider />
                <v-card-text class="text-right f11">
                    {{$dateFormat.dayDateTime(replies.created_at)}}
                </v-card-text>
                <v-card-text class="">
                    {{replies.content.length === 0 ? '-' : replies.content}}
                </v-card-text>
                <v-card-text class="">
                    <div>
                        <label class="caption f10">FILE ATTACHMENT/S</label>
                    </div>
                    <div v-if="replies.file_attachment.length > 0 " class="rounded secondary-5 pa-2 d-flex flex-wrap">
                        <section v-for="item in replies.file_attachment"
                                :key="item.id">
                            <v-hover v-slot="{ hover }">
                                <v-img 
                                :src="item.url"
                                id="attachment"
                                height="100"
                                width="100"
                                :gradient="!hover && 'to top right, rgba(100,115,201,.20), rgba(25,32,72,.20)'"
                                class="ma-1"
                                @click="viewFull(item.url)"/>
                            </v-hover>
                        </section>
                    </div>
                    <div v-else class="rounded secondary-5 pa-2">
                        <h5>Nothing Attached</h5>
                    </div>
                </v-card-text>
            </v-card>
        </div>
        <v-dialog
            v-model="fullDialog"
            fullscreen
            overlay
            >
            <v-card>
                <v-btn @click="closeViewFull" icon absolute top right class="mt-3">
                    <v-icon large>mdi-close-thick</v-icon>
                </v-btn>
                <v-container class="fill-height"  >
                    <v-img 
                        :src="selectedImage"
                        contain
                    />
                </v-container>
            </v-card>
        </v-dialog>
        <section>
            <v-text-field
                solo
                class="noline my-3 general-custom-field roboto f14 secondary-1--text fw500" 
                dense
                placeholder="Write your reply"
                hide-details="auto"
                v-model="form.reply"
                :loading="submitLoading"
                append-icon="mdi-send-outline"
                @click:append="submit_reply"
                :error-messages="errors.reply"
            />
                
        
            <div class="mb-5">
                <v-chip 
                    v-for="(item, i) in attachments"
                    class="ma-1"
                    color="primary"
                    :key="i"
                    small
                    outlined
                    close
                    @click:close="remove(i)"
                    >
                    <v-icon small left>
                        mdi-image
                    </v-icon>
                    {{ item.name }}
                    </v-chip>
            </div>
            <FormUploadFile 
                :accepted_types="'(.jpg, .jpeg, .png)'"
                :accept="'.jpeg,.png,.jpg'"
                :accepted_types_list="['image/png', 'image/jpeg', 'image/jpg']"
                :multiple="true"
                @acceptFile="acceptFile"/>
        </section>
    </section>
</template>

<script>
import { defineComponent, ref } from '@vue/composition-api'
import { mapActions, mapMutations, mapState } from 'vuex'
import { ticketStatus } from '@/constants/colors'

export default defineComponent({
    props: ['ticket'],
    computed: {
        ...mapState({
            user: (state) => state.user,
            errors: (state) => state.errors,
        })
    },
    data: () => ({
        loading: false,
        submitLoading: false,
        statusLoading: false,
        attachments: [],
        messages: [],
        form: {
            reply: "",
        },
        fullDialog: false,
        selectedImage: null,
        ticketStatus
    }),
    methods: {
        ...mapActions('usr', ['getTicketMessages', 'submitTicketResponseAction']),
        
        acceptFile(e) {
            this.attachments.push(e)
        },
        
        remove(i){
            this.attachments.splice(i, 1)
        },

        getTicketMessage(){
            this.loading = true
            this.getTicketMessages(this.ticket.uuid).then(res=> {
                this.$emit('setTicket', res)
                this.messages = res.ticket_messages
            }).finally(() => {
                this.loading = false
            })
        },

        submit_reply(){
            this.submitLoading = true
            const params = new FormData()
            params.append('reply', this.form.reply)
            params.append('uuid', this.ticket.uuid)
            params.append('to_user_id', this.ticket.user_id)
            params.append('from_user_id', this.user.id)
            this.attachments.forEach(item => {
                params.append('file_attachment[]', item)
            })
            this.submitTicketResponseAction(params).then(res=> {
                this.form.reply = []
                this.attachments = []
                this.getTicketMessage()
            }).finally(() => {
                this.submitLoading = false
            })
        },

        viewFull(url){
            this.fullDialog = true
            this.selectedImage = url
        },
        closeViewFull () {
            this.fullDialog = false
        },
    },
    created(){
        this.getTicketMessage()
    },
    watch: {
        ticket: {
            handler(prev, _new) {
                if(_new.uuid !== prev.uuid) {
                    this.getTicketMessage()
                }
            },
            deep: true
        },
    }
})
</script>