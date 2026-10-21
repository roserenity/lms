import Vue from 'vue'

import tenantLogo from '../shared/logo'
import api from '../services/axios'
import fileValidator from './fileValidator'
import dateFormat from './dateFormat'
import userFormat from './userFormat'
import coursePermission from './coursePermission'
import errors from './errors'
import assetStorage from './assetStorage'
import dompurifier from './dompurifier'
import paymentHandler from './paymentHandler'
import exportHandler from './exportHandler'
import userCertificateProgress from './userCertificateProgress'

Vue.use(api)
Vue.use(fileValidator)
Vue.use(dateFormat)
Vue.use(userFormat)
Vue.use(errors)
Vue.use(tenantLogo)
Vue.use(assetStorage)
Vue.use(coursePermission)
Vue.use(dompurifier)
Vue.use(paymentHandler)
Vue.use(userCertificateProgress)
Vue.use(exportHandler)