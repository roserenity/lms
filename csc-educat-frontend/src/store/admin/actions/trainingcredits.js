import { api } from '@/services/axios'

export default {
  async getTrainingCreditsAction({commit}, payload) {
    let _query = `/admin/training-credits`
    if(payload) {
      _query = _query + `?page=${payload.page}&paginate=${payload.paginate}&search=${payload.search}`
    }
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.get(_query).then(res => {
        commit('trainingCreditMutation', res.data.data.data)
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

  async addTrainingCreditAction({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post(`/admin/training-credits`, payload).then(res => {
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

  async updateTrainingCreditAction({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.put(`/admin/training-credits/${payload.id}`, payload).then(res => {
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

  async deleteTrainingCreditAction({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.delete(`/admin/training-credits/${payload.id}`).then(res => {
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

}