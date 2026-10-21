import { api } from '@/services/axios'

export default {
    async getForumPostsAction({commit}, payload) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.get(`/admin/forum/post`, {params: payload}).then(res => {
                commit('forumPostMutation', res.data.data.data)
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

    async getPendingForumPostsAction({commit}, payload) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.get(`/admin/forum/post`, {params: payload}).then(res => {
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

    async getForumPostAction({commit}, payload) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.get(`/admin/forum/post/${payload}`).then(res => {
                resolve(res.data.data)
            }).catch(err => {
                reject(err.response.data)
            })
        })
    }, 

    async postForumPostAction({commit}, payload) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.post(`/admin/forum/post`, payload).then(res => {
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

    async postForumCommentAction({commit}, payload) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.post(`/admin/forum/post/comment`, payload).then(res => {
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

    async updateForumPostAction({commit}, payload) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.post(`/instructor/forum/post/${payload.id}`, payload.form).then(res => {
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

    async deleteForumPostAction({commit}, payload) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.delete(`/admin/forum/post/${payload}`).then(res => {
                resolve(res.data.data)
            }).catch(err => {
                commit(
                    'errorsMutation', 
                    err.response, 
                    { root: true }
                )
                reject(err.response)
            })
        })
    }, 

    async submitForumCommentAction({commit}, payload) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.delete(`/admin/forum/post/${payload}`).then(res => {
                resolve(res.data.data)
            }).catch(err => {
                commit(
                    'errorsMutation', 
                    err.response, 
                    { root: true }
                )
                reject(err.response)
            })
        })
    }
}