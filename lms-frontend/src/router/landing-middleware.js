function landing({next}) {

    /** tenant cant access landing */
    if(window.location.origin !== process.env.VUE_APP_DOMAIN) return next("/404");
    return next()
}

export { landing }