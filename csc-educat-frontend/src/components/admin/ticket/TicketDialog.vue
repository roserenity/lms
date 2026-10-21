<template>
    <section>
        <v-dialog persistent max-width="500" v-model="dialog">
            <v-card rounded="lg">
                <v-btn icon absolute right class="mt-3" @click="$emit('close')">
                    <v-icon>mdi-close</v-icon>
                </v-btn>
                <v-card-text>
                    <div class="text-center pt-5">
                        <h3 class="poppins black--text mt-5 primary--text">
                            EDU-{{ ticket.id }}
                        </h3>
                    </div>
                </v-card-text>
                <v-divider class="mb-3"></v-divider>
                <v-card-text>
                    <FormTextDisplay 
                        :label="'CONCERN CATEGORY'"
                        :value="ticket.concern_category"
                    />
                </v-card-text>
                <v-card-text>
                    <FormTextDisplay 
                        :label="'DESCRIPTION'"
                        :value="ticket.content"
                    />
                </v-card-text>
                <v-card-text>
                    <label class="caption">FILE ATTACHMENT/S</label>
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
        </v-dialog>
        <v-dialog
            v-model="fullDialog"
            fullscreen
            overlay
            >
            <v-container class="fill-height" @click="closeViewFull" >
                <v-btn icon absolute top right class="mt-3" @click="closeViewFull" color="white">
                    <v-icon large>mdi-close-thick</v-icon>
                </v-btn>
                <v-img 
                    :src="selectedImage"
                    contain
                />
            </v-container>
        </v-dialog>
    </section>
</template>

<script>
    export default {
        props: ['dialog', 'ticket'],
        data: ()=> ({
            fullDialog: false,
            selectedImage: null
        }),
        methods: {
            viewFull(url){
                this.fullDialog = true
                this.selectedImage = url
            },
            closeViewFull () {
                this.fullDialog = false
            },
        }
    }
</script>

<style >
    .v-dialog.v-dialog--fullscreen {
        box-shadow: none !important;
    }
    #attachment:hover {
        cursor: zoom-in;
    }
</style>