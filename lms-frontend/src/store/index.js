import Vue from 'vue'
import Vuex from 'vuex'
import state from './state'
import * as getters from './getters'
import * as mutations from './mutations'
import * as actions from './actions'

Vue.use(Vuex);

import usr from './user'
import admin from './admin'
import instructor from './instructor'

export default new Vuex.Store({
  state,
  getters,
  mutations,
  actions,
  modules: {
    usr,
    admin,
    instructor,
  }
})
