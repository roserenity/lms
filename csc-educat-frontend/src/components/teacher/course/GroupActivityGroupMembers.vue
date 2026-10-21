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
                :loading="fetchLoading"
            />
            <LinearProgress v-if="loading"/>
            <v-sheet v-if="['view', 'remove'].includes(mode)" height="300" class="overflow-y-auto scroller">
                <v-list v-if="memberList.length > 0">
                    <v-list-item-group v-model="selected" active-class="active" multiple>
                        <v-list-item 
                            v-for="item in memberList" 
                            :key="item.id"
                            class="my-1"
                            :value="item.id"
                        >
                            <template v-slot:default="{ active }">
                                <v-list-item-content>
                                    <v-list-item-title class="d-flex justify-space-between align-center">
                                    <div class="d-flex align-center">
                                        <v-icon v-if="mode === 'remove'" small class="mr-2" :color="!active ? '' : 'primary'">{{
                                        active ? "mdi-checkbox-marked" : "mdi-checkbox-blank-outline"
                                        }}</v-icon>
                                        
                                        <v-avatar class="" size="30">
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
                                    </v-list-item-title>
                                </v-list-item-content>
                            </template>
                        </v-list-item>

                    </v-list-item-group>
                </v-list>
                <div class="secondary-2--text f12 fw500 mt-3" v-if="memberList.length === 0 && search === '' && !loading">
                    <i>No members available</i>
                </div>
            </v-sheet>
            <v-sheet v-if="mode == 'add'" height="300" class="overflow-y-auto scroller">
                <div class="roboto primary--text f14 mt-1" v-if="selected.length > 0">
                    {{ selected.length }} selected
                </div>
                <v-list v-if="nonMembersList.length > 0" >
                    <v-list-item-group v-model="selected" active-class="active" multiple>
                        <v-list-item 
                            v-for="item in nonMembersList" 
                            :key="item.id"
                            class="my-1"
                            :value="item.id"
                        >
                            <template v-slot:default="{ active }">
                                <v-list-item-content>
                                    <v-list-item-title class="d-flex justify-space-between align-center">
                                    <div class="d-flex align-center">
                                        <v-icon small class="mr-2" :color="!active ? '' : 'primary'">{{
                                        active ? "mdi-checkbox-marked" : "mdi-checkbox-blank-outline"
                                        }}</v-icon>
                                        
                                        <v-avatar class="" size="30">
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
                                    </v-list-item-title>
                                </v-list-item-content>
                            </template>
                        </v-list-item>
                    </v-list-item-group>
                </v-list>
                <div class="secondary-2--text f12 fw500 mt-3" v-if="nonMembersList.length === 0 && search === '' && !fetchLoading">
                    <i>No members available</i>
                </div>
            </v-sheet>

            <div class="d-flex flex-row">
                <ButtonPrimary  v-if="mode === 'view'" @click="() => {mode='remove'}"
                    :label="'Remove Members'"
                    :icon="'mdi-account-multiple-remove-outline'"
                    class="ml-auto mt-3"
                    :outlined="true"
                />
                <ButtonPrimary  v-if="mode === 'view'" @click="() => {mode='add', getNonMembers()}"
                    :label="'Add Members'"
                    :icon="'mdi-account-multiple'"
                    class="mt-3 ml-1"
                />
                <v-btn  v-if="mode !== 'view'" text class="poppins mt-3" @click="mode = 'view'">
                    Back
                </v-btn>
                
                <ButtonPrimary  v-if="mode === 'add'" @click="() => {addMembers()}"
                    :label="'Add'"
                    class="ml-auto poppins mt-3"
                    :loading="addLoading"
                />

                <ButtonPrimary  v-if="mode === 'remove'" @click="() => {removeMembers()}"
                    :label="'Remove'"
                    class="ml-auto poppins mt-3"
                    :loading="deleteLoading"
                />
            </div>
        </v-sheet>
  </v-dialog>
</template>

<script>
import { mapActions, mapMutations } from 'vuex'
export default {
    props: {
        dialog: Boolean,
        members: Array,
        loading: Boolean,
        group: Object || null
    },
    data: () => ({
        search: '',
        mode: 'view',
        fetchLoading: false,
        addLoading: false,
        deleteLoading: false,
        nonMembers: [],
        selected: []
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
        },
        
        nonMembersList(){
            if (this.search === '') {
                return this.nonMembers
            } else {
                return this.nonMembers.filter(member => String(`${member.first_name} ${member.last_name}`).toLowerCase().search(this.search.toLowerCase()) !== -1)
            }
        }
    },
    methods: {
        ...mapActions('instructor', [
            'getGroupActivityGroupNonMembersAction',
            'addGroupActivityGroupMembersAction',
            'deleteGroupActivityGroupMemberAction'
        ]),
        ...mapMutations(['alertMutation']),


        getNonMembers(){
            if(this.group) {
                this.fetchLoading = true
                this.getGroupActivityGroupNonMembersAction(this.group).then(res => {
                    this.nonMembers = res
                    this.fetchLoading = false
                }).catch(err => {
                    this.fetchLoading = false
                })
            }
        },

        addMembers(){
            if(this.group) {
                this.addLoading = true
                this.addGroupActivityGroupMembersAction({ ...this.group, user_id: this.selected}).then(res => {
                    this.addLoading = false
                    this.alertMutation({
                        show: true,
                        text: "Member/s has been added",
                        type: "success"
                    })
                    this.mode = 'view'
                    this.selected = []
                    this.$emit('getMembers')
                }).catch(err => {
                    this.addLoading = false
                })
            }
        },

        removeMembers(){
            if(this.group) {
                this.deleteLoading = true
                this.deleteGroupActivityGroupMemberAction({ ...this.group, user_id: this.selected}).then(res => {
                    this.deleteLoading = false
                    this.alertMutation({
                        show: true,
                        text: "Member/s has been removed",
                        type: "success"
                    })
                    this.mode = 'view'
                    this.selected = []
                    this.$emit('getMembers')
                }).catch(err => {
                    console.log(err)
                    this.deleteLoading = false
                })
            }
        }
        
    }
}
</script>