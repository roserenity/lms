import { api } from '@/services/axios'

export default {
  async getContactsAction({commit}, payload) {
    return await new Promise((resolve, reject) => {
      api.get(`/admin/contact/list?search=${payload.search}&paginate=${payload.paginate}&page=${payload.page}`).then(res => {
        commit('contactsMutation', res.data.data.data)
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

  async getGroupContactsAction({commit}, payload) {
    return await new Promise((resolve, reject) => {
      api.get(`/admin/group/chat/${payload.group_chat_uuid}/contact/list?search=${payload.search}&paginate=${payload.paginate}&page=${payload.page}`).then(res => {
        commit('contactsMutation', res.data.data.data)
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

  async getChatListsAction({commit, state}, payload) {
    return await new Promise((resolve, reject) => {
      api.get(`/admin/private/chat/list?page=${payload}`).then(res => {
        if(payload === 1 || !payload) {
          commit('chatListsMutation', res.data.data.data)
        } else {
          commit('chatListsMutation', [...state.chat_lists, ...res.data.data.data])
        }
        resolve(res.data.data)
      }).catch((err) => {
        reject(err);
      });
    })
  },

  async showMessagesAction({commit, state}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.get(`/admin/private/chat/${payload.uuid}?page=${payload.page}`).then(res => {
        if(payload.page === 1 || !payload.page) {
          commit('messagesMutation', res.data.data.private_messages.reverse())
        } else {
          commit('messagesMutation', [ ...res.data.data.private_messages.reverse(), ...state.messages, ])
        }
        resolve(res.data.data)
      }).catch((err) => {
        reject(err);
      });
    })
  },

  async sendMessageAction({commit}, payload) {
    const config = { 
      headers: {
          'Accept': 'application/json',
          'Content-Type': 'multipart/form-data',
      }
    }

    return await new Promise((resolve, reject) => {
      api.post(`/admin/private/chat/send`, payload, config).then(res => {
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

  async addGroupAction({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post(`/admin/group/chat`, payload).then(res => {
        dispatch('getGroupListsAction')
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

  async getGroupListsAction({commit, state}, payload) {
    return await new Promise((resolve, reject) => {
      api.get(`/admin/group/chat/list?page=${payload}`, payload).then(res => {
        if(payload === 1 || !payload) {
          commit('groupListsMutation', res.data.data.data)
        } else {
          commit('groupListsMutation', [...state.group_lists, ...res.data.data.data])
        }
        resolve(res.data.data)
      }).catch((err) => {
        reject(err);
      });
    })
  },

  async showGroupChatAction({commit}, payload) {
    return await new Promise((resolve, reject) => {
      api.post(`/admin/group/chat/${payload}`).then(res => {
        commit('groupListsMutation', res.data.data)
        resolve(res)
      }).catch((err) => {
        reject(err);
      });
    })
  },

  async updateGroupPhotoAction({commit, dispatch}, payload) {
    return await new Promise((resolve, reject) => {
      api.post(`/admin/group/chat/photo/${payload.id}`, payload.form).then(res => {
        dispatch('getGroupListsAction')
        resolve(payload.id)
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

  async removeGroupPhotoAction({commit, dispatch}, payload) {
    return await new Promise((resolve, reject) => {
      api.delete(`/admin/group/chat/photo/${payload.id}/remove`).then(res => {
        dispatch('getGroupListsAction')
        resolve(payload.id)
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

  async addUsersGroupAction({commit, dispatch}, payload) {
    return await new Promise((resolve, reject) => {
      api.post(`/admin/add/users/group/chat/${payload.group_chat_uuid}`, payload).then(res => {
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

  async sendGroupChatAction({commit, dispatch}, payload) {
    const config = { 
      headers: {
          'Accept': 'application/json',
          'Content-Type': 'multipart/form-data',
      }
    }

    return await new Promise((resolve, reject) => {
      api.post(`/admin/group/chat/send`, payload, config).then(res => {
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

  async showGroupChatAction({commit, state}, payload) {
    return await new Promise((resolve, reject) => {
      api.get(`/admin/group/chat/${payload.uuid}?page=${payload.page}`).then(res => {
        if(payload.page === 1 || !payload.page) {
          commit('messagesMutation', res.data.data.group_messages.reverse())
        } else {
          commit('messagesMutation', [ ...res.data.data.group_messages.reverse(), ...state.messages, ])
        }
        resolve(res.data.data)
      }).catch(err => {
        reject(err)
      })
    })
  },
  async downloadPrivateChatAttachmentAction({commit}, payload) {
    return await new Promise((resolve, reject) => {
      api.get(`/admin/private/chat/attachment/download`).then(res => {
        window.open(res.data.downloadable_url)
      }).catch(err => {
        reject(err)
      })
    })
  },

  async downloadGroupChatAttachmentAction({commit}, payload) {
    return await new Promise((resolve, reject) => {
      api.get(`/admin/group/chat/attachment/download`).then(res => {
        window.open(res.data.downloadable_url)
      }).catch(err => {
        reject(err)
      })
    })
  }
}