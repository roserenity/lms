<template>
  <section id="tenants">
    <Table :tenants="tenants" @view="view" @edit="edit"/>
    <ModalUpdateTenant :dialog="show" :form="tenant" @close="show=false" :type="type"/>
  </section>
</template>

<script>
import { mapActions, mapState, mapMutations } from 'vuex'

import SearchHeader from '../../components/super/tenant/SearchHeader.vue'
import Table from '../../components/super/tenant/Table.vue'
import ModalUpdateTenant from '../../components/super/tenant/ModalUpdateTenant.vue'

export default {
  components: {
    SearchHeader,
    Table,
    ModalUpdateTenant
  },
  data: () => ({
    tab: null,
    items: ['Tenants', 'Unpaid'],
    show: false,
    type: 'view'
  }),
  computed: {
    ...mapState('superad', {
      tenants: (state) => state.tenants,
    }),
    ...mapState({
      tenant: (state) => state.show
    })
  },
  methods: {
    ...mapActions('superad',[
      'showTenantAction'
    ]),
    ...mapMutations(['alertMutation']),

    view(id) {
      this.showTenantAction(id).then(() => {
        this.type='view'
        this.show=true
      }).catch(() => {
        this.alertMutation({
          show: true,
          text: 'Something went wrong',
          type: "error"
        })
      })
    },

    edit(id) {
      this.showTenantAction(id).then(() => {
        this.type='edit'
        this.show=true
      }).catch(() => {
        this.alertMutation({
          show: true,
          text: 'Something went wrong',
          type: "error"
        })
      })
    }
  }
}
</script>
