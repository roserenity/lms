const userFormat = {
    name(name) {
        let _name = name.split(' ')
        let new_name = []
        _name.forEach( item => {
            new_name.push(item.charAt(0).toUpperCase() + item.slice(1).toLowerCase())
        })

        return new_name.join(' ')
    },

    middle_initial(middle_name) {
        if(middle_name) {
            let initials = []
            let _names = middle_name.split(' ')
            _names.forEach(item => {
                initials.push(item[0])
            })
            let _middle_initial = initials.join('. ')
            return  `${_middle_initial}.`
        } else {
            return ''
        }
    },
}

export default {
  install(Vue) {
    Vue.prototype.$userFormat = userFormat
  }
}