var moment = require('moment')

const dateFormat = {
  fromNow(date) {
    return moment(date).fromNow()
  },
  mmDDyy(date) {
    return moment(date).format('ll');
  }

}

export default {
  install(Vue) {
    Vue.prototype.$dateFormat = dateFormat
  }
}