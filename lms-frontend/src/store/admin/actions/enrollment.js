import { api } from '@/services/axios'

export default {

  async getEnrolleeCountAction({commit}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.get(`/admin/enrollment/courses`, { params: payload }).then(res => {
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
      api.post(`/admin/enrollment/courses/export`, payload, { responseType: 'blob' }).then(res => {
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
      api.get(`/admin/enrollment/course/${payload.course_uuid}/enrollees?paginate=${payload.paginate}`, {params: payload }).then(res => {
        const invoices = res.data.data.invoices.data.map(element => ({
          for_verification: element.for_verification,
          user_id: element.user_id,
          email: element.enrollee.email,
          name: `${element.enrollee.full_name}`,
          contact_number: `${element.enrollee.data?.['More Details']?.['contactnumber'] ?? ''}`,
          invoice_id: element.id,
          enrolled_by_id: element.enroll_by ? element.enroll_by.id : null,
          enrolled_by: element.enroll_by ? `${element.enroll_by.first_name ?? ''} ${element.enroll_by.last_name ?? ''} ${element.enroll_by.suffix ?? ''}` : '',
          date_enrolled: element.created_at,
          invoice_status: element.status,
          enrollment_status: element.enrollee.enrollments.length > 0 ? 1 : 2,
          enrollment_id: element.enrollee.enrollments.length > 0 ? element.enrollee.enrollments[0].id : null,
          date_approved: element.updated_at,
          official_receipt: element.image,
          nomination_form: element.nomination_form,
          proof_of_payment: element.proof_of_payment,
          mode_of_payment: element.mode_of_payment,
          type_of_payment: element.type_of_payment,
          reference_id: element.reference_id,
          course_id: element.course_id,
          amount: element.amount,
          commitment_letter: element.commitment_letter,
          prime_resolution: element.prime_resolution,
          endorsement_letter: element.endorsement_letter,
          type_of_participant: element.type_of_participant,
          receipt_pick_up_option: element.receipt_pick_up_option,
          receipt_pick_up_address: element.receipt_pick_up_address
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
  
  async updateInvoiceStatus({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.put(`/admin/enrollment/invoice/status`, payload ).then(res => {
        resolve(res)
      })
      .catch(errs => {
        commit(
          'alertMutation',
          {
            show: true,
            text: errs.response.data.message,
            type: "error"
          },
          { root: true }
        )
        reject(errs.response.data.message)
      })
    })
  },

  async updateTypeOfPaymentAction({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.put(`/admin/enrollment/invoice/type_of_payment`, payload ).then(res => {
        commit(
          'alertMutation',
          {
            show: true,
            text: "Successfully updated!",
            type: "success"
          },
          { root: true }
        )
        resolve('ye')
      })
      .catch(errs => {
        commit(
          'alertMutation',
          {
            show: true,
            text: errs.response.data.message,
            type: "error"
          },
          { root: true }
        )
        reject(errs.response.data.message)
      })
    })
  },

  async updateTypeOfParticipantAction({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.put(`/admin/enrollment/invoice/type_of_participant`, payload ).then(res => {
        commit(
          'alertMutation',
          {
            show: true,
            text: "Successfully updated!",
            type: "success"
          },
          { root: true }
        )
        resolve('ye')
      })
      .catch(errs => {
        commit(
          'alertMutation',
          {
            show: true,
            text: errs.response.data.message,
            type: "error"
          },
          { root: true }
        )
        reject(errs.response.data.message)
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

  async paymentStatusCheckAction({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post(`/admin/payment-status/check`, payload ).then(res => {

        if (res.data.data) {
          let { status } = res.data.data
          commit(
            'alertMutation',
            {
              show: true,
              text: `${status === 1 ? 'Payment successfully verified.' : 'Pending. Payment under verification from the payment portal.'}`,
              type: "success"
            },
            { root: true }
          )
        } else {
          commit(
            'alertMutation',
            {
              show: true,
              text: `Pending. Payment under verification from the payment portal.`,
              type: "success"
            },
            { root: true }
          )
        }
        resolve(res.data.data ? res.data.data.status : 2)
      })
      .catch(errs => {
        // console.log(errs.response.data.message)
        commit(
          'alertMutation',
          {
            show: true,
            text: errs.response.data.message,
            type: "error"
          },
          { root: true }
        )
        reject(errs.response.data.message)
      })
    })
  },

  
  async paymentExportAction({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post(`/admin/enrollment/course/${payload.course_uuid}/invoice/export`, payload).then(res => {
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
  }
}
