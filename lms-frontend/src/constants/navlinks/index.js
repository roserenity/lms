import Vue from "vue";

import teacher from './teacher'
import user from './user'
import admin from './admin'
import superRoutes from './super'


Vue.use(teacher)
Vue.use(user)
Vue.use(admin)
Vue.use(superRoutes)