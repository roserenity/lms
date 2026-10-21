import axios from 'axios'
var CryptoJS = require("crypto-js");

const api = axios.create({ baseURL: process.env.VUE_APP_API })

api.defaults.headers.common = {
  'X-Requested-With': 'XMLHttpRequest',
  'Access-Control-Allow-Origin': '*'
}

api.interceptors.request.use(request => {
  const obj = JSON.parse(localStorage.getItem('access_payload'))
  if (obj) request.headers.common['Authorization'] = `Bearer ${obj.token}`

  return request
})

const role = () => {
  const obj = localStorage.getItem('current')

  if(obj) {
    let role = CryptoJS.AES.decrypt(obj, 'lms-role').toString(CryptoJS.enc.Utf8)
    return role.toLowerCase()
  }

  return null

}

export default {
  install(Vue) {
    Vue.prototype.$api = api
  }
}

export { api, role }
