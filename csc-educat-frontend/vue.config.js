module.exports = {
  productionSourceMap: false,
  outputDir: 'dist',
  filenameHashing: true, 

  chainWebpack: config => {
    config.performance
      .maxEntrypointSize(400000)
      .maxAssetSize(400000)
  },

  transpileDependencies: [
    'vuetify'
  ],

  chainWebpack: (config) => {
    config.module
      .rule('ico')
      .test(/\.ico$/)
      .use('file-loader')
      .loader('file-loader')
      .end();
  },

  pwa: {
    name: 'Aster Academy LMS',
    theme_color:"#4DBA87",
    msTileColor: '#ffffff',

    workboxPluginMode: 'GenerateSW'
  },

  runtimeCompiler: true
}
