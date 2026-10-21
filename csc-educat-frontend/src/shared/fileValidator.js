function fileValidate (types, file) {
    if(types.includes(file.type)) {
        return true
    } else return false
}

export default {
  install(Vue) {
    Vue.prototype.$fileValidate = fileValidate
  }
}