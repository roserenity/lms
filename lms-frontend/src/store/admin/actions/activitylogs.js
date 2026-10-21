import { api } from '@/services/axios'

export default {
    async getActivityLogsStatsAction({commit}) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.get(`/admin/activity-log/stats`).then(res => {
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

    async getActivityLogsAction({commit}, payload) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.get(`/admin/activity-log`, {params: payload}).then(res => {

                res.data.data.data.forEach(item => {
                    if(item.log_name === 'course_checkout') {
                        item.properties.invoice_data = JSON.parse(item.properties.invoice_data)
                    }
                });
                commit('activitylogMutation', res.data.data.data)
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

    async exportActivityLogsAction({commit}, payload) {
        return await new Promise((resolve, reject) => {
            api.post(`/admin/activity-log/export`, { ...payload }, { responseType: 'blob' }).then(res => {
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