import { api } from '@/services/axios'
export default {
    async createCourseGroupAction({commit, dispatch}, payload) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.post(`/instructor/course-groups`, payload).then(res => {
                resolve(res.data.data)
            }).catch(err => {
                reject(err.response.data.errors)
            })
        })
    },

    async getCourseGroupsAction({commit, dispatch}, payload) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.get(`/instructor/course-groups`, { params: payload}).then(res => {
                if(payload.paginate) {
                    commit('userGroupMutation', res.data.data.data)
                } else {
                    commit('userGroupMutation', res.data.data)
                }
                resolve(res.data.data)
            }).catch(err => {
                reject(err.response.data)
            })
        })
    },

    async getCourseGroupAction({commit, dispatch}, payload) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.get(`/instructor/course-groups/${payload.id}`, { params: payload}).then(res => {
                resolve(res.data.data)
            }).catch(err => {
                reject(err.response.data.errors)
            })
        })
    },

    async getCourseGroupMembersAction({commit, dispatch}, payload) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.get(`/instructor/course-groups/${payload.id}/members`, {params: payload}).then(res => {
                resolve(res.data.data)
            }).catch(err => {
                reject(err.response.data.errors)
            })
        })
    },

    async getCourseGroupUserListAction({commit, dispatch}, payload) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.get(`/instructor/course-groups/${payload.id}/user-list`, {params: payload}).then(res => {
                resolve(res.data.data)
            }).catch(err => {
                reject(err.response.data.errors)
            })
        })
    },

    async addCourseGroupMembersAction({commit, dispatch}, payload) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.post(`/instructor/course-groups/${payload.id}/members/add`, payload).then(res => {
                resolve(res.data.data)
            }).catch(err => {
                reject(err.response)
            })
        })
    },

    async removeCourseGroupMemberAction({commit, dispatch}, payload) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.delete(`/instructor/course-groups/${payload.id}/members/remove`, {params: payload}).then(res => {
                resolve(res.data.data)
            }).catch(err => {
                reject(err.response)
            })
        })
    },

    async deleteCourseGroupAction({commit, dispatch}, payload) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.delete(`/instructor/course-groups/${payload}`).then(res => {
                resolve(res.data.data)
            }).catch(err => {
                reject(err.response.data.errors)
            })
        })
    },

    async createCourseGroupAction({commit, dispatch}, payload) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.post(`/instructor/course-groups`, payload).then(res => {
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

    async updateCourseGroupAction({commit, dispatch}, payload) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.put(`/instructor/course-groups/${payload.id}`, payload).then(res => {
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
    
    async getCourseGroupsAction({commit, dispatch}, payload) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.get(`/instructor/course-groups`, { params: payload}).then(res => {
                commit('userGroupMutation', res.data.data.data)
                resolve(res.data.data)
            }).catch(err => {
                reject(err.response.data.errors)
            })
        })
    },

    async addCourseGroupChatAction({commit, dispatch}, payload) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.post(`/instructor/course-groups/${payload.id}/group-chat`, payload.form).then(res => {
                resolve(res.data.data)
            }).catch(err => {
                reject(err.response)
            })
        })
    },

}