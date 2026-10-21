<template>
  <v-dialog persistent max-width="600" v-model="dialog" >
    <v-sheet class="pa-4" rounded="lg">
      <div class="d-flex align-center">
        <div class="poppins f16 fw600">
          Add users
        </div>
        <v-spacer/>
        <v-btn icon small @click="$emit('close')">
          <v-icon>mdi-close</v-icon>
        </v-btn>
      </div>
      <SearchBar 
          :placeholder="'Search name'" 
          :value.sync="search" 
          :loading="loading1"
          :outlined="true"
          @search="() => { page = 1, getContacts()} "
          @clear="() => { search = '', page = 1, getContacts()} "
          :class_="'my-2 d-flex flex-row align-center col-12 pa-0'"/>
      
      <v-sheet max-height="300" class="overflow-y-auto scroller">
        <div class="roboto primary--text f12" v-if="selected_users.length > 0">
            {{ selected_users.length }} selected
        </div>
        <div class="roboto primary--text f12" v-else-if="users.length > 0">
            {{ users.length }} selected
        </div>
        <v-item-group multiple v-model="users">
          <v-item 
            v-for="item in contacts" 
            :key="item.id"
            :value="item.id"
            v-slot="{ active, toggle }"
            class="my-1"
          >
            <v-sheet class="my-3 d-flex align-center">
              <v-simple-checkbox :ripple="false" :value="selected_users.includes(item.id)" color="primary"  @click="() => { toggleUserSelection(item.id)} "/>
              <v-avatar class="" size="50">
                <v-img 
                  :src="item.image ? item.image.url : require('@/assets/default-photo.png')"
                  v-on:error="item.image = null"></v-img>
              </v-avatar>
              <div class="poppins ml-3">
                <div class="f12 fw500">
                  {{ item.full_name }} <span class="f11 secondary--text">({{ item.email }})</span>
                </div>
                <div class="f11 secondary--text">
                  {{`${item.role === 'USER' ? 'LEARNER' : item.role}`}} 
                </div>
              </div>
            </v-sheet>
          </v-item>
        </v-item-group>
      </v-sheet>

      <section>
        <FormPagination 
          :pageCount="pageCount" 
          :page="page"
          :paginate="paginate"
          @page="(e) => {page = e, selected_users = [...new Set([...selected_users, ...users])], users = selected_users, getContacts()}" 
          @paginate="(e) => {page = 1, paginate = e, selected_users = [...new Set([...selected_users, ...users])], users = selected_users, getContacts()}"/>
        <div class="d-flex align-center justify-end mt-2">
          <v-btn text class="secondary--text text-capitalize poppins" dense @click="$emit('close')">Cancel</v-btn>
          <v-btn color="primary" dense :disabled="selected_users.length === 0 || loading" class=" text-capitalize poppins" @click="add" :loading="loading"><v-icon left>mdi-plus</v-icon>Add</v-btn>
        </div>
      </section>
    </v-sheet>
  </v-dialog>
</template>

<script>
import { mapActions, mapMutations, mapState } from 'vuex'

export default {
  props: ['dialog'],
  data: () => ({
    filter: 'ALL',
    items: ['ALL', 'USER', 'INSTRUCTOR'],
    users: [],
    contacts: [],
    selected_users: [],
    search: '',
    pageCount: 1,
    page: 1,
    paginate: '10',
    loading: false,
    loading1: false,
  }),

  created() {
      this.users = []
      this.selected_users = []
  },
 
  methods: {
    ...mapActions('instructor', ['getCourseGroupUserListAction', 'addCourseGroupMembersAction']),
    ...mapMutations(['alertMutation']),
    
    toggleUserSelection(userId) {
      const index1 = this.selected_users.indexOf(userId);
      const index2 = this.users.indexOf(userId);
      if (index1 > -1) {
        this.selected_users.splice(index1, 1);
      } else {
        this.selected_users.push(userId);
      }
      if (index2 > -1) {
        this.users.splice(index2, 1);
      } else {
        this.users.push(userId);
      }
    },

    getContacts(){
      this.loading1 = true
      this.getCourseGroupUserListAction({ id : this.$route.params.id, search: this.search, page: this.page, paginate: Number(this.paginate) }).then((res) => {
        this.contacts = res.data
        this.pageCount = res.last_page
        this.paginate = res.per_page.toString()
        this.page = res.current_page
        this.loading1 = false
      }).catch(() => {
        this.loading1 = false
      })
    },

    add() {
      this.loading = true
      this.addCourseGroupMembersAction({
        id : this.$route.params.id,
        user_id: this.selected_users
      }).then(_ => {
        this.alertMutation({
          show: true,
          text: "User/s successfully added",
          type: "success"
        })
        this.loading = false
        this.$emit('getGroupMembers')
        this.$emit('close')
      }).catch(() => {
        this.alertMutation({
          show: true,
          text: "Something went wrong.",
          type: "error"
        })
        this.loading = false
      })
    }
  },

  watch: {
    dialog(val){
      this.search = ''
      this.users = []
      this.selected_users = []
      if(val) {
        this.getContacts()
      }
    },
  }
}
</script>
