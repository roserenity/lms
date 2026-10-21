import router from "@/router"
import Vuetify from '../plugins/vuetify'
var CryptoJS = require("crypto-js");

const setRole = (payload) => {
  let route, role;

  role = CryptoJS.AES.encrypt(payload.role, "lms-role").toString();
  switch (payload.role) {
    case 'ADMIN':
      route = 'Admin Layout'
    break;
    case 'INSTRUCTOR':
      route = 'Instructor Layout'
    break;
    default:
      route = 'User Layouts'
    break;
  }

  return { route, role }
}

export function signInToken(state, payload){
  let res = setRole(payload);
  let keyObject = { token: payload.access_token, role: res.role }
	localStorage.setItem('access_payload', JSON.stringify(keyObject))

  if(payload.role === 'ADMIN') {
    localStorage.setItem('roles', CryptoJS.AES.encrypt(JSON.stringify(['admin', 'instructor', 'user']), "lms-role").toString())
  } else if(payload.role === 'INSTRUCTOR') {
    localStorage.setItem('roles', CryptoJS.AES.encrypt(JSON.stringify(['instructor', 'user']), "lms-role").toString())
  } else {
    localStorage.setItem('roles', CryptoJS.AES.encrypt(JSON.stringify(['user']), "lms-role").toString())
  }
  
  localStorage.setItem('current', CryptoJS.AES.encrypt(payload.role.toLowerCase(), "lms-role").toString())
}

export function signInMutation(state, payload) {
  let res = setRole(payload);
  let keyObject = { token: payload.access_token, role: res.role }
	localStorage.setItem('access_payload', JSON.stringify(keyObject))
  state.user = payload

  if(payload.role === 'ADMIN') {
    localStorage.setItem('roles', CryptoJS.AES.encrypt(JSON.stringify(['admin', 'instructor', 'user']), "lms-role").toString())
  } else if(payload.role === 'INSTRUCTOR') {
    localStorage.setItem('roles', CryptoJS.AES.encrypt(JSON.stringify(['instructor', 'user']), "lms-role").toString())
  } else {
    localStorage.setItem('roles', CryptoJS.AES.encrypt(JSON.stringify(['user']), "lms-role").toString())
  }

  localStorage.setItem('current', CryptoJS.AES.encrypt(payload.role.toLowerCase(), "lms-role").toString())
  
  if(payload.email_verified_at) {
    router.push({ name: res.route })    
  } else {
    router.push({ name: 'Unverified' })    
  }
}

export function errorsMutation(state, payload) {
  state.errors = payload

  // setTimeout(() => {
  //   state.errors = []
  // },1000)

}

export function authMutation(state, payload) {
  state.user = payload
  if(localStorage.getItem('access_payload') && !state.user.hasOwnProperty('access_payload')) {
    state.user.access_payload = localStorage.getItem('access_payload')
  }
}

export function signoutMutation(state) {
  localStorage.removeItem('roles')
  localStorage.removeItem('current')
  localStorage.removeItem('access_payload')
  localStorage.removeItem('access_payload_register')
  localStorage.removeItem('cart')
  state.user = null
  location.href = '/' 
}

export function removeUserMutation(state) {
  state.user = null
}

export function showMutation(state, payload) {
  state.show = payload
}

export function customizationMutation(state, payload) {
  state.customization = payload

  let color_keys = ['navigation_background_color', 'navigation_banner_color', 'navigation_link_color', 'navigation_text_icon_color', 'navigation_link_inactive_color', 'sidebar_color', 'mini_navigation_background_color', 'mini_navigation_link_color', 'mini_navigation_icon_color', 'mini_navigation_inactive_link_color', 'primary_buttons_color']

  color_keys.forEach(key => {
    if(key === 'primary_buttons_color') {
      if(payload.hasOwnProperty('primary_buttons_color') && payload.primary_buttons_color !== '' && payload.primary_buttons_color ) {
        Vuetify.framework.theme.themes.light.primary = payload.primary_buttons_color
      }
    } else {
      if(payload.hasOwnProperty(key) && payload[key] !== '') {
        Vuetify.framework.theme.themes.light[key] = payload[key]
      }
    }

    if(key === 'navigation_link_color') {
      if(payload.hasOwnProperty('navigation_link_color') && payload.navigation_link_color !== '' && payload.navigation_link_color ) {
        Vuetify.framework.theme.themes.light.navigation_link_background_color = payload.navigation_link_color.concat('33')
      }
    }

    if(key === 'mini_navigation_link_color') {
      if(payload.hasOwnProperty('mini_navigation_link_color') && payload.mini_navigation_link_color !== '' && payload.mini_navigation_link_color) {
        Vuetify.framework.theme.themes.light.mini_navigation_link_background_color = payload.mini_navigation_link_color.concat('33')
      }
    }
  })
}

export function usersMutation(state, payload) {
  state.users = payload
}

export function classesMutation(state, payload) {
  state.classes = payload
}

export function questionsMutation(state, payload) {
  state.questions = payload
}

export function successMutation(state, payload) {
  state.errors = payload
}

export function drawerMutation(state, payload) {
  state.drawer = payload
}

export function replyboxMutation(state, payload) {
  state.replybox = payload
}

export function alertMutation(state, payload) {
  state.alert = payload
  setTimeout(() => {
    state.alert = { show: false, text: '', type: ''}
  }, 5000)
}

export function connectionMutation(state, payload) {
  state.connection = payload
}

export function cleanupNotification(state) {
  setInterval(() => {
    clearNotifications(state);
  }, 120000);
};

export function clearNotifications(state) {
  if(state.notifications.length > 0) {
    const currentTime = Date.now();
    const oneMinute = 60000;
  
    state.notifications = state.notifications.filter(notification => {
      if (notification.active) {
        return currentTime - notification.timestamp < oneMinute;
      }
      return true;
    });
  }
}

export function removeNotificationMutation(state, payload) {
  state.notifications = state.notifications.filter(notification => notification.id !== payload);
}

export function notificationMutation(state, payload) {
  console.log(payload.id)
  let index = state.notifications.findIndex(notif => notif.id === payload.id )
  if( index === -1 ) { 
    state.notifications.push(payload)
  } else {
    state.notifications.splice(index, 1, payload)
  }

  console.log(state.notifications)
}

export function playerMutation(state, payload) {
  state.player = payload
  setTimeout(() => {
    state.player = { action: null, duration: ''}
  }, 1000)
}

export function otherCoursesMutation(state, payload) {
  state.other_courses = payload
}

export function courseMutation(state, payload) {
  state.course = payload
}

export function customRegistrationMutation(state, payload) {
  state.registration_tables = payload
}

export function freeCoursesMutation(state, payload) {
  state.free_courses = payload
}

export function paidCoursesMutation(state, payload) {
  state.paid_courses = payload
}

export function coursesMutation(state, payload) {
  state.courses = payload
}

export function cartMutation(state, payload) {
  state.cart = payload
}

export function userMutation(state, payload) {
  state.user = payload
}

export function advisoryMutation(state, payload) {
  state.advisories = payload
}