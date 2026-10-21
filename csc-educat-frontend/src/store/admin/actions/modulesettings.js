import { api } from '@/services/axios'

export default {
  async getModuleFieldsAction({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.get(`/admin/module-settings/fields`).then(res => {
        commit('moduleFieldsMutation', res.data.data)
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

  async addModuleFieldsAction({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post(`/admin/module-settings/fields`, payload).then(res => {
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

  async updateModuleFieldsAction({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post(`/admin/module-settings/fields/${payload.id}`, payload).then(res => {
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

  async deleteModuleFieldsAction({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.delete(`/admin/module-settings/fields/${payload}`).then(res => {
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
  }

}