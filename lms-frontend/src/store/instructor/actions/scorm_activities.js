import { api } from '@/services/axios'

export default {
    async getScormActivityAction({commit}, payload) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.get(`/instructor/${payload.course_id}/scorm-activity/${payload.scorm_activity_id}`).then(res => {
                resolve(res.data.data)
            }).catch(err => {
                commit('errorsMutation',err.response.data.errors, { root: true })
                reject(err.response.data.errors)
            })
        })
    },

    async getScormActivityQuestionsAction({commit}, payload) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.get(`/instructor/${payload.course_id}/scorm-activity/${payload.scorm_activity_id}/questions`).then(res => {
                commit('scormActivityQuestionMutation', res.data.data)
                resolve(res.data.data)
            }).catch(err => {
                commit('errorsMutation',err.response.data.errors, { root: true })
                reject(err.response.data.errors)
            })
        })
    },

    async getScormActivityQuestionAnswersAction({commit}, payload) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.get(`/instructor/${payload.course_id}/scorm-activity/${payload.scorm_activity_id}/question/${payload.question_id}/answers`).then(res => {
                commit('scormActivityAnswersMutation', res.data.data)
                resolve(res.data.data)
            }).catch(err => {
                commit('errorsMutation',err.response.data.errors, { root: true })
                reject(err.response.data.errors)
            })
        })
    },

    async getScormActivitiesRespondentsAction({commit}, payload) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.post(`/instructor/${payload.course_id}/scorm-activity/${payload.scorm_activity_id}/respondents`, payload ).then(res => {
                resolve(res.data.data)
            }).catch(err => {
                commit('errorsMutation',err.response.data.errors, { root: true })
                reject(err.response.data.errors)
            })
        })
    },

    async getScormActivitiesRespondentAnswerAction({commit}, payload) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.get(`/instructor/${payload.course_id}/scorm-activity/${payload.scorm_activity_id}/learner/${payload.user_id}`).then(res => {
                commit('scormActivityAnswersMutation', res.data.data.answers)
                resolve(res.data.data)
            }).catch(err => {
                commit('errorsMutation',err.response.data.errors, { root: true })
                reject(err.response.data.errors)
            })
        })
    },

    async getScormActivitiesRespondentListExportAction({commit}, payload) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
          api.post(`/instructor/${payload.course_id}/scorm-activity/${payload.scorm_activity_id}/respondents/list/export`, { ...payload }, { responseType: 'blob' }).then(res => {
            resolve(res.data)
          }).catch(err => {
            commit(
              'errorsMutation', 
              err.response.data.message, 
              { root: true }
            )
            reject(err.response.data.message)
          })
        })
    },

    async getScormActivitiesRespondentAnswersExportAction({commit}, payload) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
          api.post(`/instructor/${payload.course_id}/scorm-activity/${payload.scorm_activity_id}/respondents/answers/export`, { ...payload }, { responseType: 'blob' }).then(res => {
            resolve(res.data)
          }).catch(err => {
            commit(
              'errorsMutation', 
              err.response.data.message, 
              { root: true }
            )
            reject(err.response.data.message)
          })
        })
    }
}