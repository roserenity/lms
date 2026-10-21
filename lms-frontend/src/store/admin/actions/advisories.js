import { api } from '@/services/axios'

export default {

    async getAdvisoriesAction({commit}) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.get(`/admin/advisories`).then(res => {
                commit('advisoriesMutation', res.data.data)
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

    async createAdvisoryAction({commit}, payload) {
        const config = { 
          headers: {
              'Accept': 'application/json',
              'Content-Type': 'multipart/form-data',
          }
        } 
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.post(`/admin/advisories`, payload, config).then(res => {
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
    
    async updateAdvisoryAction({commit}, payload) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.post(`/admin/advisories/${payload.id}`, payload).then(res => {
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
    
    async deleteAdvisoryAction({commit}, payload) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.delete(`/admin/advisories/${payload}`, payload).then(res => {
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
}