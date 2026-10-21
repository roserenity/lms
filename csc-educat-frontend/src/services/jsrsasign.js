var KJUR = require('jsrsasign')

export const generateMeetingSDK = (meetingNumber, role) => {
    const iat = Math.round((new Date().getTime() - 30000) / 1000)
    const exp = iat + 60 * 60 * 2
    const oHeader = { alg: 'HS256', typ: 'JWT' }

    const oPayload = {
      sdkKey: process.env.VUE_APP_ZOOM_SDK_KEY,
      mn: meetingNumber,
      role: role,
      iat: iat,
      exp: exp,
      appKey: process.env.VUE_APP_ZOOM_SDK_KEY,
      tokenExp: iat + 60 * 60 * 2
    }

    const sHeader = JSON.stringify(oHeader)
    const sPayload = JSON.stringify(oPayload)

    const sdkJWT = KJUR.jws.JWS.sign('HS256', sHeader, sPayload, process.env.VUE_APP_ZOOM_SDK_SECRET)
    return sdkJWT
  }

export default generateMeetingSDK