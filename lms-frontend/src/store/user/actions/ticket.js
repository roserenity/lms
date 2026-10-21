import { api } from '@/services/axios'

export default {
  async getTicketsAction({commit}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.get(`/user/tickets?category=${payload.category}&status=${payload.status}&paginate=${payload.paginate}&page=${payload.page}`).then(res => {
        commit('ticketMutation', res.data.data.data)
        resolve(res.data.data)
      }).catch(err => {
        commit(
          'errorsMutation', 
          err.response, 
          { root: true }
        )
        reject(err.response)
      })
    })
  },

  async submitTicketAction({commit, dispatch}, payload) {
    const config = { 
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'multipart/form-data',
        }
    }
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post(`/user/tickets`, payload, config).then(res => {
        dispatch('getTicketsAction')
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

  async getTicketMessages({commit}, payload){
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post(`/user/ticket-messages`, { ticket: payload})
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
      api.post(`/user/ticket`, payload).then(res => {
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

}