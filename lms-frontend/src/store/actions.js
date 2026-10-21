import { api, role } from '@/services/axios'
import axios from 'axios'
const endPoint = () => {
  let end_point = '/'
  if(role() == 'super-admin') end_point = '/super-admin/'
  return end_point;
}

export async function registerUserAction({commit}, payload) {
  commit('errorsMutation', [], { root: true })
  return await new Promise((resolve, reject) => {
    api.post(`/register`, payload.form).then(res => {
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
}

export async function authAction({commit}) {  
  commit('errorsMutation', [], { root: true })
  return await new Promise((resolve, reject) => {
    api.get(`${endPoint()}user`).then(res => {
      commit('authMutation', res.data)
      resolve(res.data.data)
    }).catch(err => {
      reject(err.response.status)
    //   return location.href='/forbidden'}
    // console.log(err.response.data.errors);
    })
  })
}

export async function loginUserAction({ commit }, payload) {
  commit('errorsMutation', [], { root: true })

  const axiosInstance = axios.create({
    baseURL: process.env.VUE_APP_TENANT_API,
    withCredentials: true, // Allow cookies for CSRF
    headers: {
      'X-Requested-With': 'XMLHttpRequest'
    }
  })

  try {
    // Step 1: Get CSRF cookie from Laravel
    // await axiosInstance.get('/csrf-token')

    // Step 2: Send login request
    const res = await axiosInstance.post('/login', payload.form, {
      headers: {
        Authorization: `Bearer ${payload.token}`
      }
    })

    return res.data.data
  } catch (err) {
    commit('errorsMutation', err.response?.data?.errors || [], { root: true })
    throw err.response?.data?.errors || err
  }
}


export async function loginAction({commit}, payload) {
  commit('errorsMutation', [], { root: true })

  // await api.get('/csrf-token', {
  //   withCredentials: true
  // })
  return await new Promise((resolve, reject) => {
    api.post(`/login`, payload).then(res => {
      commit('signInMutation', res.data.data)
      commit('otherCoursesMutation', [])
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
}

export async function logoutAction({commit}) {
  commit('errorsMutation', [], { root: true })
  return await new Promise((resolve, reject) => {
    api.post(`${endPoint()}logout`).then(res => {
      commit('signoutMutation') 
      resolve(res.data.data)
    }).catch(err => {
      //console.log(err.response.status);
      reject(err.response.status)
    // if(err.response.status === 403) {
    //   return location.href='/forbidden'}
    // console.log(err.response.data.errors);
    })
  })
}

export async function sendVerificationMailAction({commit}) {
  commit('errorsMutation', [], { root: true })
  return await new Promise((resolve, reject) => {
    api.post(`/email/verification-notification`).then(res => {
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
}

export async function resendverificationMailAction({commit}, payload) {
  commit('errorsMutation', [], { root: true })
  return await new Promise((resolve, reject) => {
    let axiosInstance = axios.create({
      headers: {
        Authorization: `Bearer ${payload.token}`
      },
      baseURL: process.env.VUE_APP_TENANT_API
    })

    axiosInstance.defaults.headers.common = {
      'X-Requested-With': 'XMLHttpRequest',
      'Access-Control-Allow-Origin': '*'
    }

    axiosInstance.post(`/email/verification-notification`).then(res => {
      resolve(res.data.message)
    }).catch(err => {

      console.log(err)
      commit(
        'errorsMutation', 
        err.response.data.errors, 
        { root: true }
      )
      reject(err)
    })
  })
}

export async function verifyEmailAction({commit}, payload) {
  commit('errorsMutation', [], { root: true })
  return await new Promise((resolve, reject) => {
    let axiosInstance = axios.create({
      // headers: {
      //   Authorization: `Bearer ${payload.token}`
      // },
      baseURL: process.env.VUE_APP_TENANT_API
    })

    axiosInstance.defaults.headers.common = {
      'X-Requested-With': 'XMLHttpRequest',
      'Access-Control-Allow-Origin': '*'
    }

    axiosInstance.get(`/email/verify`, { params: { ...payload } }).then(res => {
      resolve(res.data.message)
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
}

export async function changeUserPasswordAction({commit}, payload) {
  commit('errorsMutation', [], { root: true })
  return await new Promise((resolve, reject) => {
    api.put(`/update/password`, payload.form).then(res => {
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
}


export async function changeUserNeedsInfoUpdateFlagAction({commit}, payload) {
  commit('errorsMutation', [], { root: true })
  return await new Promise((resolve, reject) => {
    api.put(`/update/info-update-flag`).then(res => {
      resolve(res.data)
    }).catch(err => {
      reject(err.response)
    })
  })
}

export async function changeUserAcceptTermsAction({commit}, payload) {
  commit('errorsMutation', [], { root: true })
  return await new Promise((resolve, reject) => {
    api.put(`/update/accepted-terms`).then(res => {
      resolve(res.data.message)
    }).catch(err => {
      reject(err.response)
    })
  })
}

export async function changePasswordAction({commit, dispatch}, payload) {  
  commit('errorsMutation', [], { root: true })
  return await new Promise((resolve, reject) => {
    api.put(`${endPoint()}update/password`, payload).then(res => {
      dispatch('authAction')
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
  // await api.put(`${endPoint()}update/password`, payload)
  // .then(res => {
  //   // console.log(res.data);
  //   dispatch('authAction')
  // })
  // .catch(err => {
  //   commit('errorsMutation', err.response.data.errors)
    
  // })
}

export async function forgotPasswordAction({commit}, payload) {
  commit('errorsMutation', [], { root: true })
  return await new Promise((resolve, reject) => {
    api.post(`/forgot-password`, payload).then(res => {
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
}

export async function resetPasswordAction({commit}, payload) {
  commit('errorsMutation', [], { root: true })
  return await new Promise((resolve, reject) => {
    api.post(`/reset-password`, payload).then(res => {
      resolve(res.data.message)
    }).catch(err => {
      commit(
        'errorsMutation', 
        err.response.data.errors, 
        { root: true }
      )
      reject(err.response.status)
    })
  })
}

export async function getCustomizationAction({commit}){
  return await new Promise((resolve, reject) => {
    let image_keys = ['horizontal_logo', 'vertical_logo', 'sidebar_logo', 'login_logo', 'sidebar_photo', 'favicon', 'side_carousel']
    let color_keys = ['navigation_background_color', 'navigation_banner_color', 'navigation_link_color', 'navigation_text_icon_color', 'navigation_link_inactive_color', 'sidebar_color', 'mini_navigation_background_color', 'mini_navigation_link_color', 'mini_navigation_icon_color', 'mini_navigation_inactive_link_color','primary_buttons_color']
    let customization = {}
    api.get('tenants/info').then(res => {

      commit('tenantMutation', res.data.data.info.organization)

      image_keys.forEach(key => {
        if(res.data.data.images.findIndex(_item => _item.image_key === key) != -1) {
          if(key === 'favicon'){
            var favicon = document.querySelector('link[rel="icon"][sizes="16x16"]');
            favicon.href = res.data.data.images.find(_item => _item.image_key === key).url
            var favicon = document.querySelector('link[rel="icon"][sizes="32x32"]');
            favicon.href = res.data.data.images.find(_item => _item.image_key === key).url
          }
          customization[key] = res.data.data.images.find(_item => _item.image_key === key).url
        }
        if(key === 'side_carousel'){
          res.data.data.images.filter(_item => _item.image_key.includes('side_carousel')).forEach(_item => {
            customization[_item.image_key] = _item.url
          })
        }
      })

      color_keys.forEach(key => {
        if(res.data.data.hasOwnProperty(key)) {
          if(res.data.data[key] !== '' || !res.data.data[key]) {
            customization[key] = res.data.data[key]
          }
        }
      })

      if(Object.keys(customization).length !== 0) {
        localStorage.setItem('customization', JSON.stringify(customization) )
        commit('customizationMutation', customization)
      }

      localStorage.setItem('organization', res.data.data.info.organization )

      resolve(customization)
    }).catch(err => {
      reject(err)
    })
  })
}

export async function otherCoursesAction({commit, dispatch}, payload) {
  let courses = []
  commit('errorsMutation', [], { root: true })
  return await new Promise((resolve, reject) => {
    api('tenants/courses', { params: { is_free: false} }).then(res => {
      courses = res.data.data

      //if(courses.length === 0) {
      //  resolve(res.data.data)
      //} else {
      api('tenants/courses', { params: { is_free: true} }).then(res1 => {
        res1.data.data.forEach(item => {
          if(courses.every(course => course.id !== item.id)) {
            courses.push(item)
          }
        })
        commit('otherCoursesMutation', courses)
        resolve(res.data.data)
      })
      //}
    }).catch(err => {
      commit(
        'errorsMutation', 
        err.response.data.errors, 
        { root: true }
      )
      reject(err.response.data.errors)
    })
  })
}

export async function otherAllCoursesAction({commit, dispatch}, payload) {
  let courses = []
  commit('errorsMutation', [], { root: true })
  return await new Promise((resolve, reject) => {
    api.post(`tenants/courses/all?page=${payload.page}`, payload ).then(res => {
      if(payload.paginate) {
        commit('otherCoursesMutation', res.data.data.data)
      } else {
        commit('coursesMutation', res.data.data)
      }
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
}

export async function getCourseAvailabilityDatesAction({commit}, payload){
  commit('errorsMutation', [], { root: true })
  return await new Promise((resolve, reject) => {
    api(`/tenants/course-availability-dates`).then(res => {
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
}

export async function getTrainingCreditsAction({commit}, payload){
  commit('errorsMutation', [], { root: true })
  return await new Promise((resolve, reject) => {
    api(`/tenants/training-credits`).then(res => {
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
}

export async function getGroupedOtherCoursesAction({commit}, payload){
  commit('errorsMutation', [], { root: true })
  return await new Promise((resolve, reject) => {
    api(`/tenants/browse-courses`, { params: payload }).then(res => {
      commit('otherCoursesMutation', res.data.data.data)
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
}

export async function getOtherCourseAction({commit}, payload) { 
  commit('errorsMutation', [], { root: true })
  return await new Promise((resolve, reject) => {
    api(`tenants/courses/${payload}`).then(res => {
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
}

export async function getRegistrationTables({commit}) {
  commit('errorsMutation', [], { root: true })
  return await new Promise((resolve, reject) => {
    api(`/tenants/custom-registration/pages`).then(res => {
      commit('customRegistrationMutation', res.data.data)
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
}

export async function getTenantFreeCoursesAction({commit}, payload) { 
  commit('errorsMutation', [], { root: true })
  return await new Promise((resolve, reject) => {
    api.get('tenants/courses/free', {params: payload }).then(res => {
      commit('freeCoursesMutation', res.data.data)
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
}

export async function getTenantCoursesAction({commit}, payload) { 
  commit('errorsMutation', [], { root: true })
  return await new Promise((resolve, reject) => {
    api.get('tenants/courses', {params: { ...payload }}).then(res => {
      if(payload) {
        if(payload.is_free === true) {
          commit('freeCoursesMutation', res.data.data.filter(item=> {return item.is_free}))
        } else if (payload.is_free === false) {
          commit('paidCoursesMutation', res.data.data.filter(item=> {return !item.is_free}))
        }
      } else  {
        commit('coursesMutation', res.data.data)
      }
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
}

export async function getTenantCourseAction({commit}, payload) { 
  commit('errorsMutation', [], { root: true })
  return await new Promise((resolve, reject) => {
    api(`tenants/courses/${payload}`).then(res => {
      commit('courseMutation', res.data.data)
      resolve(res.data)
    }).catch(err => {
      commit(
        'errorsMutation', 
        err.response.data.errors, 
        { root: true }
      )
      reject(err.response)
    })
  })
}

export async function getHomepageCourses({commit}){
  commit('errorsMutation', [], { root: true })
  return await new Promise((resolve, reject) => {
    api(`tenants/random/class-categories`).then(res => {
      commit('otherCoursesMutation', res.data.data)
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
}

export async function getLoginHistory({commit}, payload){
  commit('errorsMutation', [], { root: true })
  return await new Promise((resolve, reject) => {
    api.get(`/login/history`, { params: payload }).then(res => {
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
}


export async function getActivityLogAction({commit}, payload){
  commit('errorsMutation', [], { root: true })
  return await new Promise((resolve, reject) => {
    api.get(`/activity-log`, { params: payload }).then(res => {
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
}

export async function sendContactUs({commit}, payload){
  commit('errorsMutation', [], { root: true })
  const config = { 
    headers: {
        'Accept': 'application/json',
        'Content-Type': 'multipart/form-data',
    }
  } 
  return await new Promise((resolve, reject) => {
    api.post(`tenants/send/contact/mail`, payload, config).then(res => {
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
}

export async function updateProfileDataAction({commit}, payload){
  commit('errorsMutation', [], { root: true })
  return await new Promise((resolve, reject) => {
    api.put(`/update/details`, payload).then(res => {
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
}


export async function getHomeAdvisories({commit}, payload){
  commit('errorsMutation', [], { root: true })
  return await new Promise((resolve, reject) => {
    api('tenants/advisories').then(res => {
      commit('advisoryMutation', res.data.data)
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
}

export async function getHomeBackgroundsAction({commit}, payload){
  commit('errorsMutation', [], { root: true })
  return await new Promise((resolve, reject) => {
    api('tenants/backgrounds').then(res => {
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
}
