import { api } from '@/services/axios'

export default {

    async getLandingPageBackgroundsAction({commit}) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.get(`/admin/landing-page/backgrounds`).then(res => {
                commit('landingPageBackgroundMutation', res.data.data)
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

    async createLandingPageBackgroundAction({commit}, payload) {
        const config = { 
          headers: {
              'Accept': 'application/json',
              'Content-Type': 'multipart/form-data',
          }
        } 
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.post(`/admin/landing-page/background`, payload, config).then(res => {
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
    
    async updateLandingPageBackgroundAction({commit}, payload) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.post(`/admin/landing-page/background/${payload.id}`, payload).then(res => {
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
    
    async deleteLandingPageBackgroundAction({commit}, payload) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.delete(`/admin/landing-page/background/${payload}`, payload).then(res => {
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