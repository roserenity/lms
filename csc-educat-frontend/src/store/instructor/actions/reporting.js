import { api } from '@/services/axios'

export default {
  async getInstructorReportingStats({dispatch}, payload) {
    return await new Promise((resolve, reject) => {
      api.get(`/instructor/report/list/cards`, {params: payload }).then(res => {
        resolve(res.data.data)
      }).catch(err => {
        reject(err.response.data.errors)
      })
    })
  },

  async getInstructorAllCoursesReportingStatsAction({dispatch}, payload) {
    return await new Promise((resolve, reject) => {
      api.get(`/instructor/report/list/enrollees`, {params: payload }).then(res => {
        resolve(res.data.data)
      }).catch(err => {
        reject(err.response.data.errors)
      })
    })
  },

  async getInstructorCoursesReportingStats({dispatch}, payload) {
    return await new Promise((resolve, reject) => {
      api.get(`/instructor/report/list/courses`, {params: payload }).then(res => {
        resolve(res.data.data)
      }).catch(err => {
        reject(err.response.data.errors)
      })
    })
  },

  async exportInstructorCoursesReportingStats({dispatch, commit}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.get(`/instructor/report/list/courses/export`, {params: { ...payload }}).then(res => {
        resolve(res.data)
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

  async getInstructorCompleterUsersAction({dispatch, commit}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.get(`/instructor/report/list/course/${payload.course_id}/completers`, {params: { ...payload }} ).then(res => {
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

  async getInstructorEnrolledUsersAction({dispatch, commit}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.get(`/instructor/report/list/course/${payload.course_id}/users`, {params: { ...payload }} ).then(res => {
        res.data.data.data.map(item => {
          item.name = `${item.user.full_name}`,
          item.email = `${item.user.email ?? ''}`,
          item.contact_number = `${item.user.data?.['More Details']?.['contactnumber'] ?? ''}`
          item.date_completed = item.user.completed_course?.[0]?.created_at ?? null
          item.certificate = item.user.certificates?.[0]?.image ?? null
          item.date_enrolled = item.created_at ?? null
        })

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

  async exportInstructorCompleterUsersAction({dispatch, commit}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.get(`/instructor/report/list/course/${payload.course_id}/completers/export`, {params: { ...payload }, responseType: 'blob'} ).then(res => {
        resolve(res.data)
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

  async exportInstructorEnrolledUsersAction({dispatch, commit}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.get(`/instructor/report/list/course/${payload.course_id}/users/export`, {params: { ...payload }, responseType: 'blob'} ).then(res => {
        resolve(res.data);
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

  async getRegistrationFieldsAction(){
    return await new Promise((resolve, reject) => {
      api.get(`/instructor/report/custom-registration/field/lists`).then(res => {
        resolve(res.data.data)
      }).catch(err => {
        reject(err.response.data.errors)
      })
    })
  },
  
  async getRegistrationFilterAction(){
    return await new Promise((resolve, reject) => {
      api.get(`/instructor/report/custom-registration/field/lists`).then(res => {
        resolve(res.data.data)
      }).catch(err => {
        reject(err.response.data.errors)
      })
    })
  },

  async getInstructorFilteredEnrolledUsersAction({dispatch}, payload){
    return await new Promise((resolve, reject) => {
      api.get(`/instructor/report/total-users-per-course`, {params: { ...payload }} ).then(res => {
        resolve(res.data.data)
      }).catch(err => {
        reject(err.response.data.errors)
      })
    })
  },

  async getInstructorAssessmentsReportingStats({dispatch}, payload){
    return await new Promise((resolve, reject) => {
      api.get(`/instructor/report/list/${payload.course_id}/assessments`, {params: payload } ).then(res => {
        resolve(res.data.data)
      }).catch(err => {
        reject(err.response.data.errors)
      })
    })
  },

  async exportInstructorAssessmentsSummaryAction({dispatch, commit}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post(`/instructor/report/list/${payload.course_id}/assessments/export`, { ...payload }, { responseType: 'blob' } ).then(res => {
        resolve(res.data)
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
  
}