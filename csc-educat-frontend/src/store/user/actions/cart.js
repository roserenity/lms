import { api } from '@/services/axios'

export default {

  async addToCart({commit, dispatch}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post(`/user/add-to-cart`, payload).then(res => {
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
  
  async getAllCartItemsAction({commit, state}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.get(`/user/add-to-cart/items`).then(res => {
        commit('cartMutation', res.data.data)
        resolve(res.data.data)
      }).catch(err => {
        reject(err)
      })
    })
  },

  async getCartItemsAction({commit, state}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.get(`/user/add-to-cart/items?page=${payload.page}`).then(res => {
        resolve(res.data.data)
      }).catch(err => {
        reject(err)
      })
    })
  },

  async removeFromCartAction({commit}, payload) {
    commit('errorsMutation', [], { root: true })
    return await new Promise((resolve, reject) => {
      api.post('/user/remove-from-cart', payload).then(res => {
        resolve(res)
      }).catch(err => {
        reject(err)
      })
    })
  },

  async emptyCart({commit}) {
    await api.post('/user/empty-cart')
    .then((res) => {
        commit('cartMutation', [])
    }).catch((err) => {
        console.log(err);
    });
  },
}