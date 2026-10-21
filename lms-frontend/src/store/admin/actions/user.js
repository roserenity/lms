import { api } from '@/services/axios'

export default {
  async addUserAction({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post('/admin/user-management', payload).then(res => {
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
  async getUsersAction({commit}, payload) {
    let _query = `/admin/user-management`
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.get(`/admin/user-management`, {params: payload}).then(res => {
        if(!payload) {
          commit('usersMutation', res.data.data)
        }
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
  async importEnrolleessAction({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post('/admin/enrollment/import/users', payload).then(res => {
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
  async importUsersAction({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post('/admin/user-management/import/users', payload).then(res => {
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
  async updateUserDetails({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.put(`/admin/user-management/${payload.id}`, payload).then(res => {
        dispatch('getUsersAction')
        resolve(res)
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

  async enrollUserAction({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post(`/admin/enrollments/user/${payload.user_id}/enroll`, payload).then(res => {
        resolve(res)
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

  async unenrollUserAction({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.delete(`/admin/enrollments/${payload.id}`, {params: payload}).then(res => {
        resolve(res)
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

  async updateUserRoleAction({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.put(`/admin/user-management/update/role`, payload).then(res => {
        dispatch('getUsersAction')
        resolve(res)
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

  async updateUsersDetailPromptAction({commit}) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.put('/admin/user-management/prompt-update/all').then(res => {
        resolve(res.data)
      }).catch(err => {
        commit(
          'errorsMutation', 
          err.response.data, 
          { root: true }
        )
        reject(err.response.data)
      })
    })
  },

  async updateUserDetailPromptAction({commit}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.put(`/admin/user-management/prompt-update/${payload}`).then(res => {
        resolve(res.data)
      }).catch(err => {
        commit(
          'errorsMutation', 
          err.response.data, 
          { root: true }
        )
        reject(err.response.data)
      })
    })
  },

  async exportRegisteredUsersAction({commit}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post('/admin/user-management/export', { ...payload }).then(res => {
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

  async deleteUserAction({commit}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.delete(`/admin/user-management/${payload}`).then(res => {
        resolve(res)
      }).catch(err => {
        reject(err)
      })
    })
  },

  async updateUserStatusAction({commit}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.put(`/admin/user-management/update/status`, payload).then(res => {
        resolve(res)
      }).catch(err => {
        reject(err)
      })
    })
  }

}