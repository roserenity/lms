import { api } from '../services/axios'
const tenantLogo = {
    
    async vertical (){
        return api.get('tenants/info').then(res => {
            let _logo = res.data.data.images.find(_item => _item.orientation === 'vertical')
            return _logo ? _logo.url : null
        })
    },

    async horizontal (){
        return api.get('tenants/info').then(res => {
            let _logo = res.data.data.images.find(_item => _item.orientation === 'horizontal')
            return _logo ? _logo.url : null
        })
    }
}

export default {
    install(Vue) {
        Vue.prototype.$tenantLogo = tenantLogo
    }
}