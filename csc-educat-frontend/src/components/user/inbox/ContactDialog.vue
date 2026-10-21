<template>
  <v-dialog persistent max-width="500" v-model="dialog" >
    <v-sheet class="pa-3" rounded="lg">
      <div class="d-flex align-center">
        <div class="poppins f14 fw600">
          Contacts
        </div>
        <v-spacer/>
        <v-btn dense icon small @click="$emit('close')">
          <v-icon small>mdi-close</v-icon>
        </v-btn>
      </div>
      <SearchBar 
        :placeholder="'Search name'" 
        :value.sync="search" 
        :loading="loading"
        :outlined="true"
        @search="() => { page = 1, getContacts()} "
        @clear="() => { search = '', page = 1, getContacts()} "
        :class_="'my-2 d-flex flex-row align-center col-12 pa-0'"/>
      <v-sheet height="300" class="overflow-y-auto scroller">
        <v-list-item 
          v-for="item in contacts" 
          :key="item.id"
          @click="$emit('startMessage', item)"
          class="pa-1 ma-1 border">
          <v-sheet class="d-flex align-center" >
            <v-avatar class="" size="40">
              <v-img 
              :src="item.image?item.image.url:require('@/assets/default-photo.png')"
              v-on:error="item.image = null"/>
            </v-avatar>
            <div class="poppins ml-3">
              <div class="f12 fw500">
                {{ item.full_name }}
              </div>
              <div class="f11 secondary--text">
                {{`${item.role === 'USER' ? 'LEARNER' : item.role}`}} 
              </div>
            </div>
          </v-sheet>
        </v-list-item>
      </v-sheet>
      <FormPagination 
        class="d-flex align-center justify-end"
          :pageCount="pageCount" 
          :page="page"
          :paginate="paginate"
          @page="(e) => {page = e, getContacts()}" 
          @paginate="(e) => {page = 1, paginate = e, getContacts()}"/>
    </v-sheet>
  </v-dialog>
</template>

<script>
import { mapActions, mapMutations, mapState } from 'vuex'

export default {
  props: ['dialog'],
  data: () => ({
    items: ['ALL', 'USER', 'INSTRUCTOR'],
    filter: 'ALL',
    search: '',
    pageCount: 1,
    page: 1,
    paginate: '10',
    loading: false,
  }),

  watch: {
    dialog(val){
      this.search = ''

      if(val) {
        this.getContacts()
      }
    }
  },

  methods: {
    ...mapActions('usr', ['getContactsAction']),

    getContacts(){
      this.loading = true
      this.getContactsAction({ search: this.search, page: this.page, paginate: Number(this.paginate) }).then((res) => {
        this.pageCount = res.last_page
        this.paginate = res.per_page.toString()
        this.page = res.current_page
        this.loading = false
      }).catch(() => {
        this.loading = false
      })
    }

  },

  computed: {
    ...mapState('usr', {
      contacts: (state) => state.contacts,
    })
  }
}
</script>