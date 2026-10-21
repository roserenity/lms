import { api } from '@/services/axios'

export default {
  async getAllCoursesAction({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.get('/instructor/courses/all').then(res => {
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

  async addCourseAction({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post('/instructor/courses', payload).then(res => {
        dispatch('addModuleAction', { uploader_id: res.data.data.uploader_id, course_id: res.data.data.id })
        commit('courseMutation', res.data.data)
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

  async getCoursesAction({commit}, payload) {
    let endpoint = `/instructor/courses`;
    if (payload) {
      endpoint=`/instructor/courses?status=${payload}`      
    }
    await api.get(endpoint)
    .then(res => {
      commit('coursesMutation', res.data.data)
    })
    .catch(err => {
      commit('errorsMutation', err.response.data.errors, { 
        root: true 
      })
    })
  },

  async getInstructorDashboardCoursesAction({commit}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post('/instructor/courses/dashboard', payload).then(res => {
        commit('coursesMutation', res.data.data.data)
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

  async getDashboardCoursesAction({commit}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post('/instructor/courses/dashboard', payload).then(res => {
        commit('coursesMutation', res.data.data.data)
        resolve(res.data.data)
      }).catch(err => {
        console.log(err)
        commit(
          'errorsMutation', 
          err.response.data.errors, 
          { root: true }
        )
        reject(err.response.data.errors)
      })
    })
  },

  async getPublishedCoursesAction({commit}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post('/instructor/courses/published', payload).then(res => {
        commit('coursesMutation', res.data.data.data)
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

  async getUnpublishedCoursesAction({commit}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post('/instructor/courses/unpublished', payload).then(res => {
        commit('coursesMutation', res.data.data.data)
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

  async getDraftsCoursesAction({commit}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post('/instructor/courses/draft', payload).then(res => {
        commit('coursesMutation', res.data.data.data)
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

  async updateCourseDescriptionAction ({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.put(`/instructor/courses/${payload.id}`, payload).then(res => {
        commit('courseMutation', payload)
        commit('courseUpdatedAtMutation', new Date())
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
    // await api.put(`/instructor/courses/${payload.id}`, payload)
    // .then(() => {
    //   commit('courseUpdatedAtMutation', new Date())
    // })
    // .catch(err => {
    //   commit('errorsMutation', err.response.data.errors, { 
    //     root: true 
    //   })
    // })
  },

  async showCourseAction({commit}, payload) {
    await api.get(`/instructor/courses/${payload}`)
    .then(res => {
      // console.log(res.data.data);
      commit('courseMutation', res.data.data)
      commit('courseEvaluationsMutation', res.data.data.course_evaluations)
      commit('modulesMutation', res.data.data.modules)
    })
    .catch(err => {
      commit('errorsMutation', err.response.data.errors, { 
        root: true 
      })
    })
  },

  async updateCourseCertificateTemplateAction({commit, dispatch}, payload) {
    return await new Promise((resolve, reject) => {
      api.put(`/instructor/course/${payload.id}/certificate-template`, payload).then(res => {
        resolve(res.data)
      }).catch(err => {
        reject(err)
      })
    })
  },

  async updateStatusCourseAction({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.put(`/instructor/course/${payload.id}/status`, payload).then(res => {
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

  async statusCourseAction({commit, dispatch}, payload) {
    await api.put(`/instructor/course/${payload.id}/status`, payload)
    .then(res => {
      dispatch('getCoursesAction')
      dispatch('showCourseAction', payload.id)
      dispatch('getAssessmentsAction', {
        course_id: payload.id,
      })
      dispatch('getClassesAction')
    })
    .catch(err => {
      commit('errorsMutation', err.response.data.errors, { 
        root: true 
      })
    })
  },

  async deleteCourseAction({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post(`/instructor/course/${payload.id}/permanent-delete`, payload).then(res => {
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

  async uploadThumbnailCourseAction({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post(`/instructor/course/${payload.id}/upload-image`, payload.form).then(res => {
        commit('courseUpdatedAtMutation', new Date())
        dispatch('updateCourseUpdatedAt', payload.id )
        dispatch('showCourseAction', payload.id)
        resolve(res.data.message)
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

  async tagClassCourseAction({commit, dispatch}, payload) {
    await api.put(`/instructor/course/${payload.id}/class-tags`, payload)
    .then(res => {
      // dispatch('updateCourseUpdatedAt', payload.id )
      commit('courseUpdatedAtMutation', new Date())
      console.log(res.data.data);
    })
    .catch(err => {
      commit('errorsMutation', err.response.data.errors, { 
        root: true 
      })
    })
  },

  async getCourseModulesAction({commit}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.get(`/instructor/course/${payload}/modules`, payload).then(res => {
        commit('modulesMutation', res.data.data)
        resolve(res.data.message)
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

  async updateCourseUpdatedAt({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.put(`/instructor/course/${payload}/updated-at`).then(res => {
        commit('courseUpdatedAtMutation', new Date())
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

  async getCourseSharedInstructors({commit}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.get('/instructor/share/course/users', { params: { course_id: payload }}).then(res => {
        commit('instructorsMutation', res.data.data)
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

  async shareCourseAction({commit, dispatch}, payload) {
    await api.put(`/instructor/share/course`, payload).then( res => {
      dispatch('getInstructors')
      dispatch('showCourseAction', payload.course_id)
    }).catch(err => {
      commit('errorsMutation', err.response.data.errors, { 
        root: true 
      })
    })
  },

  async updatePermissionAction({ commit, dispatch}, payload) {
    await api.put(`/instructor/share/course/permission`, payload).then( res => {
      dispatch('getInstructors')
    }).catch(err => {
      commit('errorsMutation', err.response.data.errors, { 
        root: true 
      })
    })
  },

  async removeCourseSharedIntructor({ commit, dispatch}, payload) {
    return await new Promise((resolve, reject) => {
      api.put(`/instructor/share/course/users/remove`, payload).then(res => {
        dispatch('getInstructors')
        dispatch('showCourseAction', payload.course_id)
        resolve(res.data.data)
      }).catch(err => {
        commit('errorsMutation', err.response.data.errors, { 
          root: true 
        })
        reject(err)
      })
    })
  },

  async removeCourseImage({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.delete(`/instructor/course/${payload.id}/delete-image`).then(res => {
        commit('courseUpdatedAtMutation', new Date())
        dispatch('updateCourseUpdatedAt', payload.id)
        dispatch('showCourseAction', payload.id)
        resolve(res.data.message)
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

  async courseProofOfPaymentAction({commit}, payload) {
    commit('errorsMutation', [], { root: true })

    const config = { 
      headers: {
          'Accept': 'application/json',
          'Content-Type': 'multipart/form-data',
      }
    }

    return await new Promise((resolve, reject) => {
      api.post(`/instructor/manual/enrollment/proof-of-payment`, payload, config).then(res => {
        resolve(res.data.data)
      }).catch(err => {

        console.log(err)
        commit(
          'errorsMutation', 
          err.response.data.errors, 
          { root: true }
        )
        reject(err.response.data.message)
      })
    })
  },
  
  async courseCommitmentLetterAction({commit}, payload) {
    commit('errorsMutation', [], { root: true })

    const config = { 
      headers: {
          'Accept': 'application/json',
          'Content-Type': 'multipart/form-data',
      }
    }

    return await new Promise((resolve, reject) => {
      api.post(`/instructor/manual/enrollment/commitment-letter`, payload, config).then(res => {
        resolve(res.data.data)
      }).catch(err => {
        commit(
          'errorsMutation', 
          err.response.data.errors, 
          { root: true }
        )
        reject(err.response.data.message)
      })
    })
  },

  async coursePrimeRequirementsAction({commit}, payload) {
    commit('errorsMutation', [], { root: true })

    const config = { 
      headers: {
          'Accept': 'application/json',
          'Content-Type': 'multipart/form-data',
      }
    }

    return await new Promise((resolve, reject) => {
      api.post(`/instructor/manual/enrollment/prime-requirements`, payload, config).then(res => {
        resolve(res.data.data)
      }).catch(err => {
        commit(
          'errorsMutation', 
          err.response.data.errors, 
          { root: true }
        )
        reject(err.response.data.message)
      })
    })
  },

  async duplicateCourseAction({commit}, payload) {
    commit('errorsMutation', [], { root: true })

    return await new Promise((resolve, reject) => {
      api.post(`/instructor/course/${payload}/duplicate`).then(res => {
        resolve(res)
      }).catch(err => {
        commit(
          'errorsMutation', 
          err.response.data.errors, 
          { root: true }
        )
        reject(err.response.data.message)
      })
    })
  },

  async courseExportAction({commit}, payload) {
    commit('errorsMutation', [], { root: true })

    return await new Promise((resolve, reject) => {
      api.post(`/instructor/course/${payload}/export`, payload, { responseType: 'blob' }).then(res => {
        resolve(res)
      }).catch(err => {
        commit(
          'errorsMutation', 
          err.response.data.errors, 
          { root: true }
        )
        reject(err.response.data.message)
      })
    })
  },

  async getTrainingCreditsAction({commit}, payload) {
    commit('errorsMutation', [], { root: true })

    return await new Promise((resolve, reject) => {
      api.get(`/instructor/course/training-credits`, payload).then(res => {
        commit('trainingCreditMutation', res.data.data)
        resolve(res)
      }).catch(err => {
        commit(
          'errorsMutation', 
          err.response.data.errors, 
          { root: true }
        )
        reject(err.response.data.message)
      })
    })
  },
  
  async getCourseGroupChatsAction({commit}, payload) {
    commit('errorsMutation', [], { root: true })

    return await new Promise((resolve, reject) => {
      api.get(`/instructor/course/${payload.course_id}/group-chats`, { params: payload }).then(res => {
        commit('groupListsMutation', res.data.data.data)
        resolve(res.data.data)
      }).catch(err => {
        commit(
          'errorsMutation', 
          err.response.data.errors, 
          { root: true }
        )
        reject(err.response.data.message)
      })
    })
  },

  async deleteCourseGroupChatAction({commit}, payload) {
    commit('errorsMutation', [], { root: true })

    return await new Promise((resolve, reject) => {
      api.delete(`/instructor/course/${payload.course_id}/group-chat/${payload.group_chat_id}`).then(res => {
        resolve(res.data.data)
      }).catch(err => {
        commit(
          'errorsMutation', 
          err.response.data.errors, 
          { root: true }
        )
        reject(err.response.data.message)
      })
    })
  },

  async generateCertificateAction({commit}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post(`/instructor/generate/certificate`, payload).then(res => {
        resolve(res.data.data)
      }).catch(err => {
        commit(
          'errorsMutation', 
          err.response.data.errors, 
          { root: true }
        )
        console.log(err)
        reject(err)
      })
    })
  },

}