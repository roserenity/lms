var moment = require('moment')

const dateFormat = {
  fromNow(date) {
    return moment(date).fromNow()
  },
  mmDDyy(date) {
    return moment(date).format('ll');
  },
  calendar(date) {
    return moment(date).calendar();
  },
  chatTime(date) {
    return moment(date).format('LT');; 
  },
  hhMM(date) {
    return moment(date).format('LT')
  },
  hhMMss(date) {
    return moment(date).format('h:mm:ss');
  },
  dayDateTime(date) {
    return moment(date).format('MMMM DD YYYY, h:mm a');
  },
  timeAndPeriod(date) {
    return moment(date).format('h:mm a');
  },
  ymd(date) {
    return moment(date).format('YYYY-MM-DD');
  },
  MMYY(date) {
    return moment(date).format('MMMM YYYY');
  },
  duration(seconds) {
    const d = moment.duration(seconds, 'seconds');
    return [
      d.hours() ? `${d.hours()}h` : '',
      d.minutes() ? `${d.minutes()}m` : '',
      `${d.seconds()}s`
    ].filter(Boolean).join(' ');
  }
}

export default {
  install(Vue) {
    Vue.prototype.$dateFormat = dateFormat
  }
}