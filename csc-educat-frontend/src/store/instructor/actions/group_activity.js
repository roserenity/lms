import { api } from "@/services/axios"

export default {
    async addGroupActivityAction({commit, dispatch}, payload) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.post('/instructor/group-activity', payload).then(res => {
                dispatch('showCourseAction', payload.course_id)
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
    
    async updateGroupActivityAction({commit, dispatch}, payload) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.put(`/instructor/group-activity/${payload.id}`, payload).then(res => {
                dispatch('showCourseAction', payload.course_id)
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

    async viewGroupActivityAction({commit, dispatch}, payload) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.get(`/instructor/group-activity/${payload}`).then(res => {
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

    async deleteGroupActivityAction({commit, dispatch}, payload) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.delete(`/instructor/group-activity/${payload.id}`).then(res => {
                dispatch('showCourseAction', payload.course_id)
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

    async getGroupActivityGroupsAction({commit, dispatch}, payload) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.get(`/instructor/group-activity/${payload.id}/groups`, { params: payload }).then(res => {
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

    async getGroupActivityGroupAction({commit, dispatch}, payload) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.get(`/instructor/grading/group-activity/${payload.group_activity_id}/group/${payload.group_id}`).then(res => {
                resolve(res.data.data)
            }).catch(err => {
                reject(err)
            })
        })
    },

    async getGroupActivityGroupStatusAction({commit, dispatch}, payload) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.put(`/instructor/grading/group-activity/${payload.group_activity_id}/group/${payload.group_id}/checked`, payload).then(res => {
                resolve(res.data.data)
            }).catch(err => {
                reject(err)
            })
        })
    },

    async addGroupActivityGroupMembersAction({commit, dispatch}, payload) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.post(`/instructor/group-activity/${payload.group_activity_id}/groups/${payload.id}/members/add`, payload).then(res => {
                resolve(res.data.data)
            }).catch(err => {
                reject(err)
            })
        })
    },

    async deleteGroupActivityGroupMemberAction({commit, dispatch}, payload) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.delete(`/instructor/group-activity/${payload.group_activity_id}/groups/${payload.id}/members/remove`,{params: payload}).then(res => {
                resolve(res.data.data)
            }).catch(err => {
                reject(err)
            })
        })
    },

    async getGroupActivityGroupNonMembersAction({commit, dispatch}, payload) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.get(`/instructor/group-activity/${payload.group_activity_id}/groups/non-members`).then(res => {
                resolve(res.data.data)
            }).catch(err => {
                reject(err)
            })
        })
    },

    async getGroupActivityGroupMembersAction({commit, dispatch}, payload) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.get(`/instructor/group-activity/${payload.group_activity_id}/groups/${payload.id}/members`).then(res => {
                resolve(res.data.data)
            }).catch(err => {
                reject(err)
            })
        })
    },

    async deleteGroupActivityGroupMembersAction({commit, dispatch}, payload) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.delete(`/instructor/group-activity/${payload.group_activity_id}/groups/${payload.id}`).then(res => {
                resolve(res.data.data)
            }).catch(err => {
                reject(err)
            })
        })
    },

    async generateGroupActivityGroupFileAction({commit, dispatch}, payload) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.post(`/instructor/group-activity/${payload.group_activity_id}/groups/${payload.id}/generate`).then(res => {
                resolve(res.data.data)
            }).catch(err => {
                reject(err)
            })
        })
    }

}