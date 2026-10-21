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
                        class="my-1"
                    >
                        <v-avatar class="" size="40">
                            <v-img :src="item.image ? item.image.url : require('@/assets/default-photo.png')"
                            v-on:error="item.image = null"></v-img>
                        </v-avatar>
                        <div class="poppins ml-3">
                            <div class="f12 fw500">
                                {{ item.full_name }}
                            </div>
                            <div class="f11 secondary--text">
                                {{`${item.role === 'USER' ? 'LEARNER' : item.role}`}} 
                            </div>
                        </div>
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
export default {
    props: {
        dialog: Boolean,
        members: Array,
    },
    data: () => ({
        search: ''
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
}
</script>