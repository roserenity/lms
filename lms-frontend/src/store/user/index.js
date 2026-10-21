import state from './state'
import * as getters from './getters'
import * as mutations from './mutations'
import course from './actions/course'
import assessment from './actions/assessment'
import inbox from './actions/inbox'
import calendar from './actions/calendar'
import files from './actions/file'
import notification from './actions/notification'
import ticket from './actions/ticket'
import meeting from './actions/meeting'
import cart from './actions/cart'
import announcement from './actions/announcement'
import evaluation from './actions/evaluation'
import forum from './actions/forum'

export default {
  namespaced: true,
  mutations,
  getters,
  state,
  actions: {
    ...course,
    ...assessment,
    ...inbox,
    ...calendar,
    ...files,
    ...notification,
    ...ticket,
    ...meeting,
    ...cart,
    ...announcement,
    ...evaluation,
    ...forum
  }
}