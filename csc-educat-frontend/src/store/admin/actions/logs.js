import { api } from '@/services/axios'

export default {

    async getImportLogsAction({commit}, payload) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.post(`/admin/user-management/import/logs`, payload).then(res => {
                commit('logMutation', res.data.data.data)
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

    async deleteImportLogsAction({commit, dispatch}, payload) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.delete(`/admin/user-management/import/logs/${payload}`).then(res => {
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

    async deleteAllImportLogsAction({commit, dispatch}, payload) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.delete(`/admin/user-management/import/logs`).then(res => {
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