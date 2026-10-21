import state from './state'
import * as getters from './getters'
import * as mutations from './mutations'
import user from './actions/user'
import dashboard from './actions/dashboard'
import courses from './actions/courses'
import classes from './actions/classes'
import registration from './actions/registration'
import enrollment from './actions/enrollment'
import tickets from './actions/tickets'
import logs from './actions/logs'
import activitylogs from './actions/activitylogs'
import advisories from './actions/advisories'
import certificatetemplates from './actions/certificatetemplates'
import documents from './actions/documents'
import modulesettings from './actions/modulesettings'
import announcements from './actions/announcements'
import forum from './actions/forum'
import notification from './actions/notification'
import inbox from './actions/inbox'
import trainingcredits from './actions/trainingcredits'
import landingpage from './actions/landingpage'
import file_exports from './actions/file_exports'
import bannedwords from './actions/bannedwords'

export default {
  namespaced: true,
  mutations,
  getters,
  state,
  actions: {
    ...user,
    ...dashboard,
    ...courses,
    ...classes,
    ...registration,
    ...enrollment,
    ...tickets,
    ...logs,
    ...activitylogs,
    ...advisories,
    ...certificatetemplates,
    ...documents,
    ...modulesettings,
    ...announcements,
    ...forum,
    ...notification,
    ...inbox,
    ...trainingcredits,
    ...landingpage,
    ...file_exports,
    ...bannedwords
  }
}