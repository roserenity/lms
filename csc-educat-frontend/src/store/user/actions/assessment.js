import { api } from '@/services/axios'

export default {
  async getAssessmentAction({commit}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.get(`/user/assessments/${payload}`).then(res => {
        commit('assessmentsMutation', res.data.data)
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

  async storeAssessmentAnswersAction({commit}, payload) {
    const config = { 
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'multipart/form-data',
      }
    }
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post(`/user/assessments/answers`, payload, config).then(res => {
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