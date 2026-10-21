import store from '@/store'
var CryptoJS = require("crypto-js");

function middleware({ next }) {
  let obj = JSON.parse(localStorage.getItem('access_payload'))
  
  if (!obj || !obj.token) {
    localStorage.removeItem('access_payload')
    localStorage.removeItem('curent')
    localStorage.removeItem('roles')
    return next('/login')
  }

  // store.dispatch('authAction').then(() => {
  //   if(!store.getters.userGetters.email_verified_at) return next ('/unverified')
  //   if(store.getters.userGetters.role=='USER') {
  //     if(window.location.pathname.includes('instructor') || window.location.pathname.includes('admin')) return next('/forbidden') 
  //   } else if (store.getters[`userGetters`].hasOwnProperty('role')) {
  //     setTimeout(() => {
  //       if(!window.location.pathname.includes(store.getters[`userGetters`].role.toLowerCase())) return next('/forbidden') 
  //     }, 5000)
  //   }
  // }).catch(err => {
  //   if(err == '403') {
  //     next('/403')
  //   }

  //   if(err == '419') {
  //     next('/419')
  //   }
  // })

  store.dispatch('authAction').then(async () => {
    const user = store.getters.userGetters;
    
    let currentRole = null

    if (!user.email_verified_at) {
        return next('/unverified');
    }

    if (localStorage.getItem('current') && localStorage.getItem('roles')) {
      if(['admin', 'instructor', 'user'].includes(localStorage.getItem('roles'))){
        return next('/forbidden');
      }

      currentRole = CryptoJS.AES.decrypt(localStorage.getItem('current'), 'lms-role').toString(CryptoJS.enc.Utf8)
      let roles = JSON.parse(CryptoJS.AES.decrypt(localStorage.getItem('roles'), 'lms-role').toString(CryptoJS.enc.Utf8)) || []

      if (!roles.includes(user.role.toLowerCase() || !roles.includes(currentRole) )) {
          return next('/forbidden'); // Ensure the user has a valid role
      }

      // Restrict 'USER' role from accessing instructor/admin routes
      if (user.role === 'USER' && (window.location.pathname.includes('instructor') || window.location.pathname.includes('admin'))) {
          return next('/forbidden');
      }

      // Restrict 'USER' role from accessing instructor/admin routes
      if (user.role === 'INSTRUCTOR' && window.location.pathname.includes('admin')) {
          return next('/forbidden');
      }

      // Ensure role-based access for ADMIN and INSTRUCTOR
      // if (user.hasOwnProperty('role')) {
      //     const rolePath = currentRole.toLowerCase();
      //     if (!window.location.pathname.includes(rolePath)) {
      //         return next('/forbidden');
      //     }
      // }

    }
  }).catch(err => {
    console.log(err)
      if (err == '403') return next('/403');
      if (err == '419') return next('/419');
  });

  
  return next()
}

export {
  middleware,
}