import { api } from '@/services/axios'

export default {

    async getDocumentAction({commit}) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.get(`/admin/documents`).then(res => {
                commit('documentsMutation', res.data.data)
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

    async createDocumentAction({commit}, payload) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.post(`/admin/documents`, payload).then(res => {
                commit('documentsMutation', res.data.data)
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
    
    async updateDocumentAction({commit}, payload) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.post(`/admin/documents/${payload.id}`, payload).then(res => {
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