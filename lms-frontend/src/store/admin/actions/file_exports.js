import { api } from '@/services/axios'

export default {

    async getExportsAction({commit}, payload) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.get(`/admin/exports`, {params: payload}).then(res => {
                commit('exportsMutation', res.data.data.data)
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

    async downloadExportedFileAction({commit},  payload){
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
          api.post(`/admin/download/exports`, payload).then(res => {
            resolve(res.data.downloadable_url)
          }).catch(err => {
            commit(
              'errorsMutation', 
              err.response.data.errors, 
              { root: true }
            )
            reject(err)
          })
        })
    },

    async deleteExportedFileAction({commit},  payload){
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
          api.delete(`/admin/exports/${payload}`).then(res => {
            resolve(res)
          }).catch(err => {
            commit(
              'errorsMutation', 
              err.response.data.errors, 
              { root: true }
            )
            reject(err)
          })
        })
    },

}