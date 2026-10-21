import { api } from "@/services/axios"

export default {
    async getGradingAction({commit}, payload) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.post(`/instructor/grading/check-assessment-answers`, payload).then(res => {
                commit('gradingMutation', res.data.data)
                resolve(res.data.data)
                // commit('gradingAssessmentUsersMutation', res.data.data.assessment_scores)
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
    async updateGradingAssessmentStatusAction({dispatch, commit}, payload){
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.put('/instructor/grading/assessment/update/checked', payload).then(res => {
                resolve(res)
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
    async updateGradingCourseEvaluationStatusAction({dispatch, commit}, payload){
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.put('/instructor/grading/course-evaluation/update/checked', payload).then(res => {
                resolve(res)
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
    async updateGradingModuleEvaluationStatusAction({dispatch, commit}, payload){
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.put('/instructor/grading/module-evaluation/update/checked', payload).then(res => {
                resolve(res)
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
    async updateGradingAction({dispatch, commit}, payload){
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.put('/instructor/grading/update/score', payload).then(res => {
                dispatch('getGradingAction', {
                    assessment_id: payload.assessment_id,
                    user_id: payload.user_id,
                    user_assessment_id: payload.user_assessment_id
                })
                resolve(res.data.data)
                // commit('gradingAssessmentUsersMutation', res.data.data.assessment_scores)
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
    async getGradingStudentList({commit}, payload){
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.get(`/instructor/grading/assessment/${payload.id}`, { params: payload }).then(res => {
                commit('gradingMutation', res.data.data.assessment)
                commit('gradingAssessmentUsersMutation', res.data.data.assessment_scores.data)
                resolve(res.data.data)
                // commit('gradingAssessmentUsersMutation', res.data.data.assessment_scores)
            }).catch(err => {
                commit(
                    'errorsMutation', 
                    err.response.data.message, 
                    { root: true }
                )
                reject(err.response.data.message)
            })
        })
        // await api.get(`/instructor/grading/assessment/${payload}`)
        // .then(res => {
        //     commit('gradingMutation', res.data.data)
        //     commit('gradingAssessmentUsersMutation', res.data.data.assessment_scores)
        // })
    },
    
    async getGradingGroupActivitiesAction({commit}, payload){
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.get(`/instructor/grading/group-activity/${payload.group_activity_id}/groups?paginate=${payload.paginate}&page=${payload.page}&search=${payload.search}`).then(res => {
                commit('gradingMutation', res.data.data.groups.data)
                resolve(res.data.data)
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

    async getGradingAssessmentsEvaluations({commit}, payload){
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.get(`/instructor/grading/${payload.id}?paginate=${payload.paginate}&page=${payload.page}&search=${payload.search}&type=${payload.type}`).then(res => {
                commit('gradingMutation', res.data.data.paginated_result.data)
                resolve(res.data.data)
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

    async getCoursesGrading({commit}, payload) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.get(`/instructor/grading`, { params: payload }).then(res => {
                commit('gradingMutation', res.data.data.data)
                resolve(res.data.data)
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
    async getGradingStudent({commit}, payload) {
        commit('errorsMutation', [], { root: true })
        return await new Promise((resolve, reject) => {
            api.get(`/instructor/grading/${payload}/students`).then(res => {
                commit('gradingAssessmentUsersMutation', res.data.data.assessment_scores)
                resolve(res.data.data)
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