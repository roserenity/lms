import { api } from '@/services/axios'

export default {
//   async addClassAction({commit, dispatch}, payload) {
//     await api.post('/admin/class-categories', payload)
//     .then(res => {
//       // commit('successMutation', [], { root: true })
//       dispatch('getClassesAction')
//       console.log(res.data);
//     })
//     .catch(err => {
//       commit(
//         'errorsMutation', 
//         err.response.data.errors, 
//         { root: true }
//       )
//     })
//   },
  async getCoursesAction({commit}) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.get('/tenants/courses').then(res => {
        commit('coursesMutation', res.data.data)
        resolve(res.data.data)
      }).catch(err => {
        commit(
          'errorsMutation', 
          err.response.data.errors, 
          { root: true }
        )
        reject(err.response.data.errors)
      })
    })
  },

  async getCoursesUserCount({commit}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.get(`/admin/courses`, {params: payload }).then(res => {
        commit('coursesMutation', res.data.data.data)
        resolve(res.data.data)
      }).catch(err => {
        commit(
          'errorsMutation', 
          err.response.data.errors, 
          { root: true }
        )
        reject(err.response.data.errors)
      })
    })
  },

  async exportAdminAnalyticsAction({commit}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post('/admin/dashboard/courses/export', { ...payload }).then(res => {
        resolve(res.data)
      }).catch(err => {
        commit(
          'errorsMutation', 
          err.response.data.errors, 
          { root: true }
        )
        reject(err.response.data.errors)
      })
    })
  },

  async exportAdminUserAnalyticsAction({commit}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post('/admin/dashboard/course/users/export', { ...payload }).then(res => {
        resolve(res.data)
      }).catch(err => {
        commit(
          'errorsMutation', 
          err.response, 
          { root: true }
        )
        reject(err.response)
      })
    })
  },

  async exportAdminCompletersAnalyticsAction({commit}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post('/admin/dashboard/course/completers/export', { ...payload }).then(res => {
        resolve(res.data)
      }).catch(err => {
        commit(
          'errorsMutation', 
          err.response, 
          { root: true }
        )
        reject(err.response)
      })
    })
  },

//   async updateClassesAction({commit, dispatch}, payload) {
//     await api.put(`/admin/class-categories/${payload.id}`, payload)
//     .then( res => {
//       dispatch('getClassesAction')
//     })
//     .catch(err => {
//       return commit(
//         'errorsMutation', 
//         err.response.data.errors, 
//         { root: true }
//       )
//     })
//   },

//   async deleteClassAction({commit, dispatch}, payload) {
//     await api.delete(`/admin/class-categories/${payload.id}`, payload)
//     .then( res => {
//       dispatch('getClassesAction')
//     })
//     .catch(err => {
//       return commit(
//         'errorsMutation', 
//         err.response.data.errors, 
//         { root: true }
//       )
//     })
//   }

  async courseProofOfPaymentAction({commit}, payload) {
    commit('errorsMutation', [], { root: true })

    const config = { 
      headers: {
          'Accept': 'application/json',
          'Content-Type': 'multipart/form-data',
      }
    }

    return await new Promise((resolve, reject) => {
      api.post(`/admin/enrollment/upload/proof-of-payment`, payload, config).then(res => {
        resolve(res.data.data)
      }).catch(err => {

        console.log(err)
        commit(
          'errorsMutation', 
          err.response.data.errors, 
          { root: true }
        )
        reject(err.response.data.message)
      })
    })
  },
  
  async courseCommitmentLetterAction({commit}, payload) {
    commit('errorsMutation', [], { root: true })

    const config = { 
      headers: {
          'Accept': 'application/json',
          'Content-Type': 'multipart/form-data',
      }
    }

    return await new Promise((resolve, reject) => {
      api.post(`/admin/enrollment/upload/commitment-letter`, payload, config).then(res => {
        resolve(res.data.data)
      }).catch(err => {
        commit(
          'errorsMutation', 
          err.response.data.errors, 
          { root: true }
        )
        reject(err.response.data.message)
      })
    })
  },

  async coursePrimeRequirementsAction({commit}, payload) {
    commit('errorsMutation', [], { root: true })

    const config = { 
      headers: {
          'Accept': 'application/json',
          'Content-Type': 'multipart/form-data',
      }
    }

    return await new Promise((resolve, reject) => {
      api.post(`/admin/enrollment/upload/prime-requirements`, payload, config).then(res => {
        resolve(res.data.data)
      }).catch(err => {
        commit(
          'errorsMutation', 
          err.response.data.errors, 
          { root: true }
        )
        reject(err.response.data.message)
      })
    })
  },

}