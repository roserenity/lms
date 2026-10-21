import { api } from '@/services/axios'

export default {
  async enrollStudentAction({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    const config = { 
      headers: {
          'Accept': 'application/json',
          'Content-Type': 'multipart/form-data',
      }
    }
    return await new Promise((resolve, reject) => {
      api.post('/instructor/manual/enrollment', payload, config)
      .then(() => {
        resolve('yes')
      }).catch(err => {
        commit('errorsMutation', err.response.data.errors, { 
          root: true 
        })
        reject(err.response.data)
      })
    })

    // await api.post('/instructor/manual/enrollments', payload, config)
    // .then(res => {

    // })
    // .catch(err => {
    //   commit('errorsMutation', err.response.data.errors, { 
    //     root: true 
    //   })
    // })
  },

  async getEnrolleeCountAction({commit}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.get(`/instructor/enrollment/courses`, { params: payload }).then(res => {
        commit('enrolleesMutation', res.data.data.data)
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

  async getEnrolleeCountExportAction({commit}, payload){
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post(`/instructor/enrollment/courses/export`, payload, { responseType: 'blob' }).then(res => {
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

  async getCourseEnrolleesAction({commit}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.get(`/instructor/enrollment/course/${payload.course_uuid}/enrollees?paginate=${payload.paginate}`, {params: payload }).then(res => {
        const invoices = res.data.data.invoices.data.map(element => ({
          for_verification: element.for_verification,
          user_id: element.user_id,
          email: element.enrollee.email,
          name: `${element.enrollee.full_name}`,
          contact_number: `${element.enrollee.data?.['More Details']?.['contactnumber'] ?? ''}`,
          enrolled_by: element.enroll_by ? `${element.enroll_by.first_name ?? ''} ${element.enroll_by.last_name ?? ''} ${element.enroll_by.suffix ?? ''}` : '',
          invoice_id: element.id,
          date_enrolled: element.created_at,
          invoice_status: element.status,
          enrollment_status: element.enrollee.enrollments.length > 0 ? 1 : 2,
          date_approved: element.updated_at,
          official_receipt: element.image,
          nomination_form: element.nomination_form,
          proof_of_payment: element.proof_of_payment,
          mode_of_payment: element.mode_of_payment,
          commitment_letter: element.commitment_letter,
          prime_resolution: element.prime_resolution,
          endorsement_letter: element.endorsement_letter,
          type_of_payment: element.type_of_payment.replace('_', ' '),
          type_of_participant: element.type_of_participant,
          reference_id: element.reference_id,
          course_id: element.course_id,
          amount: element.amount,
          enrolled_by_id: element.enroll_by ? element.enroll_by.id : null,
          receipt_pick_up_option: element.receipt_pick_up_option,
          receipt_pick_up_address: element.receipt_pick_up_address,
        }));      
        commit('enrolleesMutation', invoices)
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

  async requestOfficialReceipt({commit, dispatch}, payload){
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post(`/tenants/request/official-receipt`, payload, { responseType: 'blob' }).then(res => {
        resolve(res.data)
      }).catch(err => {
        commit(
          'errorsMutation', 
          err.response.message, 
          { root: true }
        )
        reject(err)
      })
    })
  },

  async paymentExportAction({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post(`/instructor/enrollment/course/${payload.course_uuid}/invoice/export`, payload).then(res => {
        resolve(res.data)
      }).catch(err => {
        commit(
          'errorsMutation', 
          err.response.message, 
          { root: true }
        )
        reject(err.response.message)
      })
    })
  },

  async progressExportAction({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post(`/instructor/enrollment/course/${payload.course_uuid}/enrollees/progress/export`, payload, { responseType: 'blob' }).then(res => {
        resolve(res.data)
      }).catch(err => {
        commit(
          'errorsMutation', 
          err.response.message, 
          { root: true }
        )
        reject(err.response.message)
      })
    })
  },
}