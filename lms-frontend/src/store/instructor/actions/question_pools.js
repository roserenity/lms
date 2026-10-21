import { api } from '@/services/axios'
export default {
    async addQuestionPoolAction({commit, dispatch}, payload) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.post(`/instructor/question-pools`, payload).then(res => {
                resolve(res.data.data)
            }).catch(err => {
                reject(err.response.data.errors)
            })
        })
    },

    async getQuestionPoolsAction({commit, dispatch}, payload) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.get(`/instructor/question-pools`, { params: payload}).then(res => {
                if(payload.paginate) {
                    commit('questionPoolsMutation', res.data.data.data)
                } else {
                    commit('questionPoolsMutation', res.data.data)
                }
                resolve(res.data.data)
            }).catch(err => {
                reject(err.response.data)
            })
        })
    },

    async deleteQuestionPoolAction({commit, dispatch}, payload) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.delete(`/instructor/question-pools/${payload}`).then(res => {
                resolve(res.data.data)
            }).catch(err => {
                reject(err.response.data.errors)
            })
        })
    },

    async createQuestionPoolAction({commit, dispatch}, payload) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.post(`/instructor/question-pools`, payload).then(res => {
                resolve(res.data.data)
            }).catch(err => {
                reject(err.response.data.errors)
            })
        })
    },

    async getQuestionPoolAction({commit, dispatch}, payload) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.get(`/instructor/question-pools/${payload}`).then(res => {
                commit('questionPoolsQuestionMutation', res.data.data.questions)
                resolve(res.data.data)
            }).catch(err => {
                reject(err.response.data)
            })
        })
    },

    async addQuestionPoolQuestionAction({commit, dispatch}, payload) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.post(`/instructor/question-pool/question`, payload).then(res => {
                resolve(res.data.data)
            }).catch(err => {
                reject(err.response.data.errors)
            })
        })
    },

    async deleteQuestionPoolQuestionAction({commit, dispatch}, payload) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.delete(`/instructor/question-pool/question/${payload}`).then(res => {
                resolve(res.data.data)
            }).catch(err => {
                reject(err.response.data.errors)
            })
        })
    },
  
    async updateQuestionPoolQuestionAction({commit, dispatch}, payload) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.put(`/instructor/question-pool/question/${payload.id}`, payload).then(res => {
                resolve(res.data.data)
            }).catch(err => {
                reject(err.response.data.errors)
            })
        })
    },

    async addQuestionPoolQuestionImage({commit, dispatch}, payload) {
        const config = { 
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'multipart/form-data',
            }
        }
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.post(`/instructor/question-pool/question/${payload.id}/image`, payload.form, config).then(res => {
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
    
    async deleteQuestionPoolQuestionImage({commit, dispatch}, payload) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.delete(`/instructor/question-pool/question/${payload}/image`, payload).then(res => {
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

    async getQuestionPoolQuestionImages({commit, dispatch}, payload) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.get(`/instructor/question-pool/question/${payload}/image`, payload).then(res => {
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

    async assignQuestionPoolToCourseEvaluationAction({commit, dispatch}, payload) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.post(`/instructor/question-pool/assign/course-evaluation`, payload).then(res => {
                resolve(res)
            }).catch(err => {
                    reject(err.response.data)
                })
            })
    },

    async assignQuestionPoolToModuleEvaluationAction({commit, dispatch}, payload) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.post(`/instructor/question-pool/assign/module-evaluation`, payload).then(res => {
                resolve(res)
            }).catch(err => {
                    reject(err.response.data)
                })
            })
    },

    async assignQuestionPoolToAssessmentAction({commit, dispatch}, payload) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.post(`/instructor/question-pool/assign/assessment`, payload).then(res => {
                resolve(res)
            }).catch(err => {
                    reject(err.response.data)
                })
            })
    },
    
}