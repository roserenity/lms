<template>
  <v-dialog persistent max-width="600" v-model="dialog" >
    <v-sheet class="pa-4" rounded="lg">
      <div class="d-flex align-center">
        <div class="poppins f14 fw600">
          Contacts
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
        <v-item-group multiple v-model="users">
          <v-item 
            v-for="item in contacts" 
            :key="item.id"
            :value="item.id"
            v-slot="{ active, toggle }"
            class="my-1"
          >
            <v-sheet class="my-3 d-flex align-center">
              <v-simple-checkbox :ripple="false" :value="active" color="primary" @click="toggle"/>
              <v-avatar class="" size="50">
                <v-img 
                  :src="item.image ? item.image.url : require('@/assets/default-photo.png')"
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
            </v-sheet>
          </v-item>
        </v-item-group>
      </v-sheet>

      <section>
        <FormPagination 
          :pageCount="pageCount" 
          :page="page"
          :paginate="paginate"
          @page="(e) => {page = e, getContacts()}" 
          @paginate="(e) => {page = 1, paginate = e, getContacts()}"/>
        <div class="d-flex align-center justify-end mt-2">
          <v-btn text class="secondary--text text-capitalize poppins" @click="$emit('close')">Cancel</v-btn>
          <v-btn color="primary" :disabled="users.length === 0 || loading" class=" text-capitalize poppins" @click="add" :loading="loading"><v-icon left>mdi-plus</v-icon>Add</v-btn>
        </div>
      </section>
    </v-sheet>
  </v-dialog>
</template>

<script>
import { mapActions, mapMutations, mapState } from 'vuex'

export default {
  props: {
    dialog: {
      type: Boolean,
      default: false
    },
    group_chat_uuid: {
      type: String || null,
      default: null
    },
    group: {
      type: Object || null,
      default: null
    },
  },
  data: () => ({
    filter: 'ALL',
    items: ['ALL', 'USER', 'INSTRUCTOR'],
    users: [],
    search: '',
    pageCount: 1,
    page: 1,
    paginate: '10',
    loading: false,
    loading1: false,
  }),

  created() {
      this.users = []
  },
 
  methods: {
    ...mapActions('instructor', ['addUsersGroupAction', 'getGroupContactsAction']),

    getContacts(){
      this.loading1 = true

      let course_id = this.$route.params.id

      if(this.$route.name === 'Instructor Inbox') {
        console.log(this.group)
        if(this.group.group_chat_has_course.length > 0) {
          course_id = this.group.group_chat_has_course[0].id
        } else {
          course_id = null
        }
      }
      
      this.getGroupContactsAction({ group_chat_uuid : this.group_chat_uuid, search: this.search, page: this.page, paginate: Number(this.paginate), course_id: course_id }).then((res) => {
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
      this.addUsersGroupAction({
        group_chat_uuid: this.group_chat_uuid,
        user_id:  this.users
      }).then(_ => {
        this.loading = false
        this.$emit('close')
      }).catch(() => {
        this.loading = false
      })
    }
  },

  computed: {
    ...mapState('instructor', {
      contacts: (state) => state.contacts,
    }),
  },

  watch: {
    dialog(val){
      this.search = ''
      this.users = []
      if(val) {
        this.getContacts()
      }
    }
  }
}
</script>
