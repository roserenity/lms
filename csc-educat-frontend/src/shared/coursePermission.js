const isReadOnly = {
    isReadOnly(status, permission) {
        if(status === 'PUBLISH'){
            return true
        } else {
            if(permission === 'viewer') {
                return true
            } else return false
        }
    },

    isReadOnlyNotCourseOwner(status, permission) {
        if(status === 'PUBLISH') {
            return true
        } else {
            if(permission === 'viewer' || permission === 'owner' ) {
                return false
            } else return true
        }
    },

    isReadOnlyNotCourseOwnerPublishedUnpublished(status, permission) {
        if(status === 'PUBLISH' || status === 'UNPUBLISH' ) {
            return true
        } else {
            if(permission === 'viewer' || permission === 'owner' ) {
                return false
            } else return true
        }
    },


    showCardAction(status, permission) {
        if(status === 'PUBLISH') {
            if(permission === 'editor' || permission === 'owner' ) {
                return true
            } else return false
        } else {
            if(permission === 'viewer') {
                return false
            } else return true
        }
    },

    showOnlyToOwner(permission) {
        if(permission === 'owner' ) {
            return true
        } else return false
    }
}

export default {
    install(Vue) {
      Vue.prototype.$isReadOnly = isReadOnly
    }
  }