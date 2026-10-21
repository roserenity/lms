import { api } from '@/services/axios'

export default {
  async getTicketsAction({commit}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post(`/admin/tickets?paginate=${payload.paginate}&page=${payload.page}&search=${payload.search}`, payload)
        .then(res => {
          res.data.data.data.data.forEach(ticket => {
            if(ticket.user) {
              ticket.submitted_by = `${ticket.user.first_name ? ticket.user.first_name : 'Learner'} ${ticket.user.last_name ? ticket.user.last_name : 'Account'} ${ticket.user.suffix ? ticket.user.suffix : ''}`
            }
          });
          commit('ticketsMutation', res.data.data.data.data)
          commit('ticketCountMutation', res.data.data.count)
          resolve(res.data.data.data)
        })
      })
      .catch(err => {
        commit(
          'errorsMutation', 
          err.response.data.errors, 
          { root: true }
        )
        reject(err)
      })
  },

  async getTicketCountAction({commit}, payload){
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post(`/admin/ticket-count`, payload)
        .then(res => {
          resolve(res.data.data)
        })
      })
      .catch(err => {
        commit(
          'errorsMutation', 
          err.response.data.errors, 
          { root: true }
        )
        reject(err)
      })
  },

  async getTicketMessages({commit}, payload){
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post(`/admin/ticket-messages`, { ticket: payload})
        .then(res => {
          resolve(res.data.data)
        })
      })
      .catch(err => {
        commit(
          'errorsMutation', 
          err.response.data.errors, 
          { root: true }
        )
        reject(err)
      })
  },

  async submitTicketResponseAction({commit, dispatch}, payload) {
    const config = { 
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'multipart/form-data',
        }
    }
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post(`/admin/ticket`, payload).then(res => {
        commit(
          'alertMutation',
          {
            show: true,
            text: "Successfully replied!",
            type: "success"
          },
          { root: true }
        )
        resolve(res.data.data)
      }).catch(err => {
        commit(
          'errorsMutation', 
          err.response.data.errors, 
          { root: true }
        )
        reject(err.response.errors)
      })
    })
  },

  async updateTicketResponseAction({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post(`/admin/ticket/status`, payload).then(res => {
        commit(
          'alertMutation',
          {
            show: true,
            text: "Ticket successfully updated!",
            type: "success"
          },
          { root: true }
        )
        resolve(res.data.data)
      }).catch(err => {
        commit(
          'errorsMutation', 
          err.response.data.errors, 
          { root: true }
        )
        reject(err.response.errors)
      })
    })
  },

  async ticketExportAction({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post(`/admin/ticket/export`, payload).then(res => {
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