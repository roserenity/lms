import state from './state'
import * as getters from './getters'
import * as mutations from './mutations'
import tenant from './actions/tenant'

export default {
  namespaced: true,
  mutations,
  getters,
  state,
  actions: {
    ...tenant
  }
}