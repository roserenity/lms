import { api } from '@/services/axios'

export default {

    async getScormCourseUploadProgressAction({commit}, payload) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.get(`/instructor/scorm/course/upload/progress`, { params: payload }).then(res => {
                resolve(res.data.data)
            }).catch(err => {
                reject(err.response.data)
            })
        })
    },

    async getScormCourseSummaryAction({commit}, payload) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.get(`/instructor/scorm/course/summary`, { params: payload }).then(res => {
                resolve(res.data.data)
            }).catch(err => {
                reject(err.response.data)
            })
        })
    },

    async updateScormCoursePreviewLinkAction({commit}, payload) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.post(`/instructor/scorm/course/build/preview-link`, payload).then(res => {
                resolve(res.data.data)
            }).catch(err => {
                reject(err.response.data)
            })
        })
    },

    async getScormCourseAllLearnerDetailsAction({commit}, payload) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.get(`/instructor/scorm/course/learners/details`, { params: payload }).then(res => {
                resolve(res.data.data)
            }).catch(err => {
                reject(err.response.data)
            })
        })
    },

    async getScormCourseAllLearnersAction({commit}, payload) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.get(`/instructor/scorm/course/learners`, { params: payload }).then(res => {
                commit('enrolleesMutation', res.data.data.learners.data)
                resolve(res.data.data)
            }).catch(err => {
                reject(err.response.data)
            })
        })
    },

    async getScormCourseAllLearnersExportAction({commit}, payload) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.post(`/instructor/scorm/course/learners/export`, payload, { responseType: 'blob' } ).then(res => {
                resolve(res.data)
            }).catch(err => {
                reject(err.response.data)
            })
        })
    },

    async deleteScormCourseLearnersProgressAction({commit}, payload) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.delete(`/instructor/scorm/registration/${payload}`).then(res => {
                resolve(res.data.data)
            }).catch(err => {
                reject(err.response.data)
            })
        })
    },
    
    async getScormCourseAllLearnerProgressAction({commit}, payload) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.get(`/instructor/scorm/course/learner/progress`, { params: payload }).then(res => {
                resolve(res.data.data)
            }).catch(err => {
                reject(err.response.data)
            })
        })
    },
    
    async getScormCourseAllLearnersSummaryAction({commit}, payload) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.get(`/instructor/scorm/course/learners/submissions`, { params: payload }).then(res => {
                resolve(res.data.data)
            }).catch(err => {
                reject(err.response.data)
            })
        })
    },

    async getScormLearnerRegistrationSummaryAction({commit}, payload) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.get(`/instructor/scorm/registration/summary`, { params: payload }).then(res => {
                resolve(res.data.data)
            }).catch(err => {
                reject(err.response.data)
            })
        })
    },

    async getScormCourseAllLearnerInteractionAction({commit}, payload) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.get(`/instructor/scorm/course/learner/interactions`, { params: payload }).then(res => {
                resolve(res.data.data)
            }).catch(err => {
                reject(err.response.data)
            })
        })
    },

}