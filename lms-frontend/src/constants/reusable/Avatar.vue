<template>
  <v-avatar color="secondary" :size="size">
    <img
      alt="Avatar"
      :src="user.image?user.image.url
      :require('../../assets/default-photo.png')"
      v-on:error="require('../../assets/default-photo.png')"
    >
  </v-avatar>
</template>

<script>
export default {
  props: ['user', 'size'],
  data: () => ({
    asset_path: `${process.env.VUE_APP_ASSET_PATH}`,
  })
}
</script>