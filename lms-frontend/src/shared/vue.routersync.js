import VuexRouterSync from 'vuex-router-sync';

import store from '../store' // vuex store instance
import router from '../router'

VuexRouterSync.sync(store, router);
