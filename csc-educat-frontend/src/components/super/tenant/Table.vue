<template>
  <section>
    <ModalStatus :dialog="dialog" :type="type" @close="() => {
        dialog=false
        getTenantsAction()
      }"
      @emitChange="dispatchChangeStatus"  
    />
    
    <section v-if="totalCount !== 0" class="text-right">
      <FormLabel :label="`${totalCount} result/s`"/>
    </section>
    <v-data-table
      :headers="tenant_tbl"
      :items="tenants"
      class="elevation-1"
      :search="search"
      :loading="loading"
      hide-default-footer
      :items-per-page.sync="itemsPerPage"
    >
      <template v-slot:item.status="{ item }">
        <v-select
          v-model="item.status"
          :items="status"
          item-value="value"
          dense
          flat
          solo
          :menu-props="{ top: true, offsetY: true }"
          small-chips
          class="f14 align-center"
          hide-details
          v-on:input="changeStatus($event, item.id)"
        >
          <template #selection="{ item }">
            <v-chip small label :color="item.text=='ACTIVE' ? 'primary' : ''">{{ item.text }}</v-chip>
          </template>
        </v-select>
      </template>
      <template v-slot:item.action="{ item }">
        <v-btn text class="text-capitalize primary--text f12 fw600" @click="$emit('edit', item.id)">
          <v-icon left>mdi-pencil</v-icon>
          Edit
        </v-btn>
      </template>
    </v-data-table>

    <FormPagination 
      :pageCount="pageCount" 
      :page="page"
      :paginate="paginate"
      @page="(e) => page = e" 
      @paginate="(e) => paginateMutation(e)"/>
  </section>
  
</template>

<script>
import ModalStatus from './ModalStatus.vue'
import { mapActions, mapState, mapMutations } from 'vuex';
import { tenant_tbl } from '@/constants/tblheaders/tenant'

export default {
  components: {
    ModalStatus
  },
  props: {
    tenants: {
      type: Array,
      default: []
    },
  },
  data: () => ({
    tenant_tbl,
    headers: [
      'Name',
      'Organization',
      'Email',
      'Status',
      'Domain Name',
      'Action',
    ],
     status: [
      { text: "ACTIVE", value: "active" },
      { text: "INACTIVE", value: "inactive" },
    ],
    dialog: false,
    type: '',
    id: '',
    pageCount: 1,
    page: 1,
    totalCount: 0
  }),
  computed: {
    ...mapState('superad', {
      loading: (state) => state.loading,
      paginate: (state) => state.paginate
    }),

    itemsPerPage(){
      return Number(this.paginate)
    }
  },

  mounted() {
    this.getTenants()
  },

  methods: {
    ...mapActions('superad', ['updateStatusTenantAction', 'getTenantsAction']),

    ...mapMutations('superad', ['loadingMutation', 'paginateMutation']),

    changeStatus(text, id) {
      this.dialog=true
      this.type = text
      this.id = id
    },

    dispatchChangeStatus() {
      this.updateStatusTenantAction({
        tenant_id: this.id,
        tenant_status: this.type.toLowerCase(),
        _method: 'PUT'
      }).then(() => {
        this.dialog=false
        this.$store.commit('alertMutation', {
          show: true,
          text: `Tenant Updated!`,
          type: "success"
        })
      })
    },

    getTenants(){
      this.loadingMutation(true)
      this.getTenantsAction({
        general_education: true,
        corporate: true,
        ngo: true,
        active: true,
        inactive: false,
        today: false,
        this_week: false,
        this_month: false,
        this_year: false,
        paginate: this.paginate
      }).then(res => {
        this.totalCount = res.total
        this.loadingMutation(false)
      }).catch(() => {
        this.loadingMutation(false)
        this.alertMutation({
          show: true,
          text: 'Something went wrong',
          type: "error"
        })
      })
    }
  },

  watch: {
    paginate(){
      this.getTenants()
    }
  }

}
</script>