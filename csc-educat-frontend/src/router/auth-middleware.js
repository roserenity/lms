import { role } from "../services/axios";
var CryptoJS = require("crypto-js");

function auth_middleware({ next }) {
    let obj = JSON.parse(localStorage.getItem('access_payload'))
    if (obj && obj.token && !['admin', 'instructor', 'user'].includes(localStorage.getItem('roles'))) {
        
        if(role() === 'user') {
            next('/dashboard')
        } else if (role() === 'admin') {
            next('/admin/dashboard')
        } else if (role() === 'instructor') {
            next('/instructor/dashboard')
        }
    }
    next()
}

export {
    auth_middleware
}