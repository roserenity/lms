<template>
    <v-dialog persistent max-width="600" v-model="dialog" >
        <v-sheet class="pa-4" rounded="lg">
            <div class="d-flex align-center">
                <v-spacer/>
                <v-btn icon small @click="$emit('close')">
                <v-icon>mdi-close</v-icon>
                </v-btn>
            </div>
            <v-text-field 
                filled 
                class="noline general-custom-field roboto f14 secondary-1--text fw500" 
                dense
                prepend-inner-icon="mdi-magnify"
                placeholder="Search"
                hide-details
                v-model="search"
            />
            <v-sheet height="300" class="overflow-y-auto scroller">
                <v-list v-if="memberList.length > 0">
                    <v-list-item 
                        v-for="item in memberList" 
                        :key="item.id"
                        class="my-1 d-flex flex-row justify-space-between"
                    >
                        <div class="d-flex flex-row">
                            <v-avatar class="" size="40">
                                <v-img :src="item.image ? item.image.url : require('@/assets/default-photo.png')"
                                v-on:error="require('@/assets/default-photo.png')"></v-img>
                            </v-avatar>
                            <div class="poppins ml-3">
                                <div class="f12 fw500">
                                    {{ item.first_name ? item.first_name : 'User' }}
                                    {{ $userFormat.middle_initial(item.middle_name) }}
                                    {{ item.last_name ? item.last_name : 'Account' }}
                                    {{ item.suffix ? item.suffix : '' }}
                                </div>
                                <div class="f11 secondary--text">
                                    {{`${item.role === 'USER' ? 'LEARNER' : item.role}`}} 
                                </div>
                            </div>
                        </div>
                        <v-btn v-if="group.user_id !== item.id" icon dense small class="ml-auto" @click="()=>{ selected = item, remove()}" :loading="deleteLoading && selected === item">
                            <v-icon>
                                mdi-minus-circle-outline
                            </v-icon>
                        </v-btn>
                    </v-list-item>
                </v-list>
                <div class="secondary-2--text f12 fw500 mt-3" v-if="memberList.length === 0 && search === ''">
                    <i>No members available</i>
                </div>
            </v-sheet>
        </v-sheet>
  </v-dialog>
</template>

<script>
import { mapActions, mapMutations } from "vuex"

export default {
    props: {
        dialog: Boolean,
        members: Array,
        group: Object || null
    },
    data: () => ({
        search: '',
        selected: null,
        deleteLoading: false
    }),
    watch: {
        dialog(){
            this.search = ''
        }
    },
    computed: {
        memberList(){
            if (this.search === '') {
                return this.members
            } else {
                return this.members.filter(member => String(`${member.first_name} ${member.last_name}`).toLowerCase().search(this.search.toLowerCase()) !== -1)
            }
        
        }
    },
    methods: {
        ...mapActions('instructor', ['removeUserGroupAction']),
        ...mapMutations(['alertMutation']),

        remove(){
            this.deleteLoading = true
            this.removeUserGroupAction({ group_chat_uuid: this.group.uuid, user_id: this.selected.id }).then(() => {
                this.deleteLoading = false
                this.selected = null
                this.alertMutation({
                    show: true,
                    text: "Succeessfully removed a member",
                    type: "success"
                })
                this.$emit('close')
                this.$emit('getData')
            }).catch(() => {
                this.deleteLoading = false
                this.selected = null
                this.$emit('close')
                this.alertMutation({
                    show: true,
                    text: "Something went wrong",
                    type: "error"
                })
            })
        }
    }
}
</script>