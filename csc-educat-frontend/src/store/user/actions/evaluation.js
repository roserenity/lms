import { api } from '@/services/axios'

export default {
    async submitCourseEvaluationAnswersAction({commit}, payload) {
        commit('errorsMutation', [], { root: true })

        const config = { 
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'multipart/form-data',
            }
        }

        return await new Promise((resolve, reject) => {
          api.post(`user/course/evaluations`, payload, config).then(res => {
            resolve(res)
          }).catch(err => {
            reject(err)
          })
        })
    },

    async submitModuleEvaluationAnswersAction({commit}, payload) {
        commit('errorsMutation', [], { root: true })

        const config = { 
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'multipart/form-data',
            }
        }

        return await new Promise((resolve, reject) => {
          api.post(`user/module/evaluations`, payload, config).then(res => {
            resolve(res)
          }).catch(err => {
            reject(err)
          })
        })
    },

    async getCourseEvaluationContentAction({commit}, payload){
        commit('errorsMutation', [], { root: true })

        return await new Promise((resolve, reject) => {
          api.get(`/user/course/evaluations/${payload}`).then(res => {
            resolve(res.data.data)
          }).catch(err => {
            reject(err)
          })
        })
    },

    async getModuleEvaluationContentAction({commit}, payload){
        commit('errorsMutation', [], { root: true })

        return await new Promise((resolve, reject) => {
          api.get(`/user/module/evaluations/${payload}`).then(res => {
            resolve(res.data.data)
          }).catch(err => {
            reject(err)
          })
        })
    },

    async getCourseEvaluationAction({commit}, payload){
        commit('errorsMutation', [], { root: true })

        return await new Promise((resolve, reject) => {
          api.get(`/user/course/evaluation/${payload}`).then(res => {
            resolve(res.data.data)
          }).catch(err => {
            reject(err)
          })
        })
    },

    async getModuleEvaluationAction({commit}, payload){
        commit('errorsMutation', [], { root: true })

        return await new Promise((resolve, reject) => {
          api.get(`/user/module/evaluation/${payload}`).then(res => {
            resolve(res.data.data)
          }).catch(err => {
            reject(err)
          })
        })
    },

    async getCourseEvaluationsAction({commit}, payload){
      commit('errorsMutation', [], { root: true })

      return await new Promise((resolve, reject) => {
        api.get(`/user/course/${payload}/course-evaluations`).then(res => {
          commit('evaluationsMutation', res.data.data.course_evaluations)
          resolve(res.data.data)
        }).catch(err => {
          reject(err)
        })
      })
  },
}