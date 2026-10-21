import { api } from '@/services/axios'
import { certificatesMutation } from '../mutations';

export default {
  async getCoursesAction({commit}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post('/user/courses', payload).then(res => {
        let _courses = []

        res.data.data.data.forEach((item) => {
          _courses.push(item.course)
        })
        commit('coursesMutation', _courses)
        resolve(res.data.data)
      }).catch(err => {
        commit(
          'errorsMutation', 
          err.response.data.errors, 
          { root: true }
        )
        reject(err)
      })
    })
  },

  async getModuleContentAction({commit, state}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.get(`/user/course/module/${payload}`).then(res => {
        resolve(res.data.data)
      }).catch(err => {
        reject(err)
      })
    })
  },

  async courseExportAction({commit}, payload) {
    commit('errorsMutation', [], { root: true })

    return await new Promise((resolve, reject) => {
      api.post(`/user/course/${payload}/export`, payload, { responseType: 'blob' }).then(res => {
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

  async setCourseLog({commit}, payload) {
    return await new Promise((resolve, reject) => {
      api.post(`/user/course/setlog`, payload ).then(res => {
        resolve(res.data.data)
      }).catch(err => {
        commit(
          'errorsMutation', 
          err.response.data.errors, 
          { root: true }
        )
        reject(err)
      })
    })
  },

  async showCourseTimelineAction({commit}, payload) {
    return await new Promise((resolve, reject) => {
      api.get(`/user/courses/${payload}`, { params: { learning_material: 0 }}).then(res => {
        let course = res.data.data
        commit('objectMutation', res.data.data)

        let modules = []
        let content = []

        res.data.data.modules.forEach((module, i) => {
          modules.push({prerequisite_set: i,...module, final: true})
          
          module.module_content.forEach(module_content => {
            if(module_content.contentable) {
              module_content.contentable.module_is_locked = module.module_is_locked
              content.push(module_content.contentable)

              if(module_content.contentable.hasOwnProperty('topic_has_assessments')) {
                content.push(...module_content.contentable.topic_has_assessments)
              }

              if(module_content.contentable.hasOwnProperty('sub_topics')) {
                module_content.contentable.sub_topics.forEach(module_subtopics => {
                  module_content.contentable.module_is_locked = module.module_is_locked
                  content.push(module_subtopics)
                  content.push(...module_subtopics.sub_topic_has_assessments)
                })
              }
            }
          })
          // module.topics.forEach(module_topics => {
          //   module_topics.module_is_locked = module.module_is_locked
          //   content.push(module_topics)
          //   content.push(...module_topics.topic_has_assessments)
          //   module_topics.sub_topics.forEach(module_subtopics => {
          //     module_topics.module_is_locked = module.module_is_locked
          //     content.push(module_subtopics)
          //     content.push(...module_subtopics.sub_topic_has_assessments)
          //   })
          // })
          content.push(...module.module_evaluations)
        })

        content.push(...res.data.data.course_evaluations)

        commit('modulesMutation', modules)
        commit('contentMutation', content)
        commit('evaluationsMutation', res.data.data.course_evaluations)
        resolve(res.data.data)
      }).catch(err => {
        console.log(err)
        reject(err)
      })
    })
  },

  async showCourseAction({commit}, payload) {
    return await new Promise((resolve, reject) => {
      api.get(`/user/courses/${payload}`, { params: { learning_material: 1 }}).then(res => {
        let course = res.data.data
        commit('objectMutation', res.data.data)

        let modules = []
        let content = []
        res.data.data.modules.forEach((module, i) => {
          modules.push({prerequisite_set: i,...module, final: true})
          
          module.module_content.forEach(module_content => {
            if(module_content.contentable) {
              module_content.contentable.module_is_locked = module.module_is_locked
              content.push(module_content.contentable)

              if(module_content.contentable.hasOwnProperty('topic_has_assessments')) {
                content.push(...module_content.contentable.topic_has_assessments)
              }

              if(module_content.contentable.hasOwnProperty('sub_topics')) {
                module_content.contentable.sub_topics.forEach(module_subtopics => {
                  module_content.contentable.module_is_locked = module.module_is_locked
                  content.push(module_subtopics)
                  content.push(...module_subtopics.sub_topic_has_assessments)
                })
              }
            }
          })
          content.push(...module.module_evaluations)
        })
        

        content.push(...res.data.data.course_evaluations)

        commit('modulesMutation', modules)
        commit('contentMutation', content)
        commit('evaluationsMutation', res.data.data.course_evaluations)
        resolve(res.data.data)
      }).catch(err => {
        commit(
          'errorsMutation', 
          err.response.data.errors, 
          { root: true }
        )
        reject(err)
      })
    })
  },

  async saveDoneProgressAction({commit, state}, payload) {
    return await new Promise((resolve, reject) => {
      api.post(`/user/course/save/content`, payload).then(res => {
        state.content.forEach(_content => {
          // if(_content.uuid === payload.model_uuid) {
          //   _content.student_done_status = true
          // }
        })
        resolve(res.data.data)
      }).catch(err => {
        reject(err)
      })
    })
  },

  async showNextContentAction({commit, dispatch}, payload) {
    await api.post(`/user/course/progress/controls`, payload)
    .then(res => {
      commit('materialMutation', res.data.data)
      dispatch('getCoursesAction')
    })
    .catch((err) => {
      console.log(err);
    });
  },

  async updateCourseColor({commit, dispatch}, payload) {
    await api.post(`/user/course-card/color`, payload)
    .then(res => {
      dispatch('getCoursesAction')
    }).catch(err => {
      commit('errorsMutation', err.response.data.errors, { 
        root: true 
      })
    })
  },

  async updateCourseFavorite({commit, dispatch}, payload) {
    await api.post(`/user/course/favorite`, payload)
    .then(res => {
      dispatch('getCoursesAction')
    }).catch(err => {
      commit('errorsMutation', err.response.data.errors, { 
        root: true 
      })
    })
  },

  async generateCertificateAction({commit}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post(`/user/generate/certificate`, payload).then(res => {
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

  async getTransactionHistory({commit}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post(`/user/transaction-history/invoices?page=${payload.page}&paginate=${payload.paginate}`, payload).then(res => {
        let _arr = []
        res.data.data.data.forEach(_course => {
          _arr.push({
            id: _course.id,
            course_id: _course.course.id,
            course_uuid: _course.course.uuid,
            user_id: _course.enrollee.id,
            email: _course.enrollee.email,
            reference_id: _course.reference_id,
            title: _course.course.title ? _course.course.title : 'Untitled',
            status: _course.status,
            date: _course.created_at,
            amount: _course.course.price,
            uuid: _course.course.uuid,
            nomination_form: _course.nomination_form,
            image: _course.image,
            proof_of_payment: _course.proof_of_payment,
            type_of_payment: _course.type_of_payment,
            type_of_participant: _course.type_of_participant,
            commitment_letter: _course.commitment_letter,
            prime_resolution: _course.prime_resolution,
            endorsement_letter: _course.endorsement_letter,
            mode_of_payment: _course.mode_of_payment,
            official_receipt_no: _course.official_receipt_no,
            date_enrolled: !_course.enrollment ? '' : _course.enrollment.created_at,
            enroll_by_id: _course.enroll_by_id,
            receipt_pick_up_option: _course.receipt_pick_up_option,
            receipt_pick_up_address: _course.receipt_pick_up_address,
            for_verification: _course.for_verification,
          })
        })
        commit('transactionHistoryMutation', _arr)
        resolve(res.data.data)
      }).catch(err => {
        commit(
          'errorsMutation', 
          err.response.data.errors, 
          { root: true }
        )
        reject(err)
      })
    })
  },

  async uploadProofOfPayment({commit}, payload){
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post(`/user/upload/nomination-form/invoice/${payload.invoice_id}`, payload.form, { headers: {
        'Accept': 'application/json',
        'Content-Type': 'multipart/form-data',
      }}).then(res => {
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

  async getCourseCertificatesAction({commit},  payload){
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post(`/user/certificates`, payload).then(res => {
        commit('certificatesMutation', res.data.data.data)
        resolve(res.data.data)
      }).catch(err => {
        commit(
          'errorsMutation', 
          err.response.data.errors, 
          { root: true }
        )
        reject(err)
      })
    })
  },

  async downloadCourseCertificateAction({commit},  payload){
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post(`/user/download/certificate`, payload).then(res => {
        resolve(res.data.downloadable_url)
      }).catch(err => {
        commit(
          'errorsMutation', 
          err.response.data.errors, 
          { root: true }
        )
        reject(err)
      })
    })
  },

  async markCourseAsCompletedAction({commit}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post(`/user/course/mark-as-complete`, payload).then(res => {
        resolve(res.data.data)
      }).catch(err => {
        commit(
          'errorsMutation', 
          err.response.data.errors, 
          { root: true }
        )
        reject(err)
      })
    })
  },

  async saveTimeSpentAction({commit}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post(`/user/course/time-spent`, payload).then(res => {
        resolve(res.data.data)
      }).catch(err => {
        console.log('saveTimeSpentAction ERR: ', err)
        reject(err)
      })
    })
  },

  async getCompletedCoursesAction({commit}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post(`/user/complete-courses`, payload).then(res => {
        let courses = []
        res.data.data.forEach((course) => {
          if(course.course){
            courses.push(course)
          }
        })
        commit('completedCoursesMutation', courses)
        resolve(res.data.data)
      }).catch(err => {
        commit(
          'errorsMutation', 
          err.response.data.errors, 
          { root: true }
        )
        reject(err)
      })
    })
  },

  async getClassCategoryAction({commit}, payload) {
    commit('errorsMutation', [], { root: true })

    return await new Promise((resolve, reject) => {
      api.get(`/user/course/class-categories`).then(res => {
        commit('classesMutation', res.data.data)
        resolve(res.data.data)
      }).catch(err => {
        commit(
          'errorsMutation', 
          err.response.data.errors, 
          { root: true }
        )
        reject(err)
      })
    })
  },

  async courseCheckoutOnlinePortal({commit}, payload) {
    commit('errorsMutation', [], { root: true })

    const config = { 
      headers: {
          'Accept': 'application/json',
          'Content-Type': 'multipart/form-data',
      }
    }

    return await new Promise((resolve, reject) => {
      api.post(`/user/checkout/online-portal`, payload, config).then(res => {
        resolve(res)
      }).catch(err => {
        if(err.response.data.errors) {
          commit(
            'errorsMutation', 
            err.response.data.errors, 
            { root: true }
          )

          reject(err.response.data.message)
        }
        reject(err)
      })
    })
  },

  async courseCheckoutNomination({commit}, payload) {
    commit('errorsMutation', [], { root: true })

    const config = { 
      headers: {
          'Accept': 'application/json',
          'Content-Type': 'multipart/form-data',
      }
    }

    return await new Promise((resolve, reject) => {
      api.post(`/user/checkout/nomination`, payload, config).then(res => {
        resolve(res.data.data)
      }).catch(err => {
        if(err.response.data.errors) {
          commit(
            'errorsMutation', 
            err.response.data.errors, 
            { root: true }
          )

          reject(err.response.data.message)
        }
        reject(err)
      })
    })
  },

  async courseCheckoutPRIME({commit}, payload) {
    commit('errorsMutation', [], { root: true })

    const config = { 
      headers: {
          'Accept': 'application/json',
          'Content-Type': 'multipart/form-data',
      }
    }

    return await new Promise((resolve, reject) => {
      api.post(`/user/checkout/prime`, payload, config).then(res => {
        resolve(res.data)
      }).catch(err => {
        if(err.response.data.errors) {
          commit(
            'errorsMutation', 
            err.response.data.errors, 
            { root: true }
          )

          reject(err.response.data.message)
        }
        reject(err)
      })
    })
  },

  async courseCheckoutOwnAccount({commit}, payload) {
    commit('errorsMutation', [], { root: true })

    const config = { 
      headers: {
          'Accept': 'application/json',
          'Content-Type': 'multipart/form-data',
      }
    }

    commit(
      'errorsMutation', 
      [], 
      { root: true }
    )

    return await new Promise((resolve, reject) => {
      api.post(`/user/checkout/own-account`, payload, config).then(res => {
        resolve(res.data.data)
      }).catch(err => {
        if(err.response.data.errors) {
          commit(
            'errorsMutation', 
            err.response.data.errors, 
            { root: true }
          )

          reject(err.response.data.message)
        }
        reject(err)
      })
    })
  },

  async courseCheckoutCommitmentLetter({commit}, payload) {
    commit('errorsMutation', [], { root: true })

    const config = { 
      headers: {
          'Accept': 'application/json',
          'Content-Type': 'multipart/form-data',
      }
    }

    return await new Promise((resolve, reject) => {
      api.post(`/user/checkout/commitment-letter`, payload, config).then(res => {
        resolve(res.data)
      }).catch(err => {
        if(err.response.data.errors) {
          commit(
            'errorsMutation', 
            err.response.data.errors, 
            { root: true }
          )

          reject(err.response.data.message)
        }
        reject(err)
      })
    })
  },

  async courseCheckoutProofOfPayment({commit}, payload) {
    commit('errorsMutation', [], { root: true })

    const config = { 
      headers: {
          'Accept': 'application/json',
          'Content-Type': 'multipart/form-data',
      }
    }

    return await new Promise((resolve, reject) => {
      api.post(`/user/checkout/offline`, payload, config).then(res => {
        resolve(res.data)
      }).catch(err => {
        if(err.response.data.errors) {
          commit(
            'errorsMutation', 
            err.response.data.errors, 
            { root: true }
          )

          reject(err.response.data.message)
        }
        reject(err)
      })
    })
  },
  
  async courseCheckout({commit}, payload) {
    commit('errorsMutation', [], { root: true })

    const config = { 
      headers: {
          'Accept': 'application/json',
          'Content-Type': 'multipart/form-data',
      }
    }

    return await new Promise((resolve, reject) => {
      api.post(`/user/checkout`, payload, config).then(res => {
        resolve(res.data)
      }).catch(err => {
        if(err.response.data.errors) {
          commit(
            'errorsMutation', 
            err.response.data.errors, 
            { root: true }
          )

          reject(err.response.data.message)
        }
        reject(err)
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
      api.post(`/user/upload/proof-of-payment/invoice`, payload, config).then(res => {
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

  async courseCancelTransaction({commit}, payload) {
    commit('errorsMutation', [], { root: true })
    console.log(payload)
    return await new Promise((resolve, reject) => {
      api.post(`/user/cancel/transaction/invoice/${payload.id}`).then(res => {
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

  async getCourseProgressAction({commit}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.get(`/user/course/${payload}/progress`).then(res => {
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

  async getCourseZoomMeetingsAction({commit}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.get(`/user/course/${payload}/zoom-meetings`).then(res => {
        commit('zoomMeetingsMutation', res.data.data)
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

  async requestOfficialReceipt({commit, dispatch}, payload){
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post(`/tenants/request/official-receipt`, payload, { responseType: 'blob' }).then(res => {
        resolve(res.data)
      }).catch(err => {
        reject(err)
      })
    })
  },

  async submitLearnerScormAnswer({commit, dispatch}, payload){
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post(`/user/scorm/submission`, payload).then(res => {
        resolve(res.data.data)
      }).catch(err => {
        reject(err)
      })
    })
  },

  async submitLearnerScormScore({commit, dispatch}, payload){
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post(`/user/scorm/score/set`, payload).then(res => {
        resolve(res.data.data)
      }).catch(err => {
        reject(err)
      })
    })
  },

  async submitLearnerScormProgress({commit, dispatch}, payload){
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post(`/user/scorm/progress`, payload).then(res => {
        resolve(res.data.data)
      }).catch(err => {
        reject(err)
      })
    })
  },

  async getScormProgressAction({commit}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.get(`/user/scorm/progress`,{ params: payload}).then(res => {
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

  async getScormRegistrationProgressAction({commit}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post(`/user/course/scorm/progress/check`, payload ).then(res => {
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

  async courseCommitmentLetterAction({commit}, payload) {
    commit('errorsMutation', [], { root: true })

    const config = { 
      headers: {
          'Accept': 'application/json',
          'Content-Type': 'multipart/form-data',
      }
    }

    return await new Promise((resolve, reject) => {
      api.post(`/user/upload/commitment-letter/invoice`, payload, config).then(res => {
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
      api.post(`/user/upload/prime-requirements/invoice`, payload, config).then(res => {
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

  async downloadCourseLearningMaterialAction({commit}, payload) {
    return await new Promise((resolve, reject) => {
      api.post(`/user/download/course/learning-material`, payload ).then(res => {
        if(res.data.result === 'success') {
          window.open(res.data.downloadable_url)
          resolve(res.data)
        } else reject('failed')
      }).catch(err => {
        reject(err)
      })
    })
  },

  async updateReceiptDeliveryOptionAction({commit}, payload) {
    return await new Promise((resolve, reject) => {
      api.post(`/user/update/delivery-receipt/invoice/${payload.invoice_id}`, payload ).then(res => {
        resolve(res.data)
      }).catch(err => {
        commit(
          'errorsMutation', 
          err.response.data.errors, 
          { root: true }
        )
        reject(err)
      })
    })
  },
}