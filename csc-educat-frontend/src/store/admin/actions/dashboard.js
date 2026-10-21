import { api } from '@/services/axios'

export default {
    async getDashboardCardsAction({commit}, payload ) {
      commit('errorsMutation', [], { root: true })
      return await new Promise((resolve, reject) => {
        api.get('/admin/list/cards', { params: payload }).then(res => {
          resolve(res.data.data)
        }).catch(err => {
          reject(err)
        })
      })
    },

    async getDashboardPieGraphsAction({commit}, payload ) {
      commit('errorsMutation', [], { root: true })
      return await new Promise((resolve, reject) => {
        api.get('/admin/pie-graphs', { params: payload }).then(res => {
          resolve(res.data.data)
        }).catch(err => {
          reject(err)
        })
      })
    },

    async getDashboardRegistrationFieldListAction({commit}, payload) {
      commit('errorsMutation', [], { root: true })
      return await new Promise((resolve, reject) => {
        api.get('/admin/custom-registration/field/lists').then(res => {
          resolve(res.data.data)
        }).catch(err => {
          reject(err)
        })
      })
    },

    async getDashboardTotalUsersBarGraphAction({commit}, payload) {
      commit('errorsMutation', [], { root: true })
      return await new Promise((resolve, reject) => {
        api.get('/admin/total-users-per-course', { params: payload }).then(res => {
          resolve(res.data.data)
        }).catch(err => {
          reject(err)
        })
      })
    },

    async getDashboardEnrolledLearnersAction({commit}, payload) {
      commit('errorsMutation', [], { root: true })
      return await new Promise((resolve, reject) => {
        api.get('/admin/course/users', { params: payload}).then(res => {
          resolve(res.data.data)
        }).catch(err => {
          reject(err)
        })
      })
    },

    async getDashboardCompletedLearnersAction({commit}, payload) {
      commit('errorsMutation', [], { root: true })
      return await new Promise((resolve, reject) => {
        api.get('/admin/course/completers', { params: payload}).then(res => {
          resolve(res.data.data)
        }).catch(err => {
          reject(err)
        })
      })
    }

}