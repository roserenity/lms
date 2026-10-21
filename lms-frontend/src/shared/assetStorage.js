export default {
    install(Vue) {
        Vue.prototype.$assetStorage = `${process.env.VUE_APP_ASSET_PATH}/images`
    }
}