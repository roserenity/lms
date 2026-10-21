import DOMPurify from 'dompurify';


const dompurifier = (text) => {
    return DOMPurify.sanitize(text)
}

export default {
    install(Vue) {
        Vue.prototype.$dompurifier = dompurifier
    }
}