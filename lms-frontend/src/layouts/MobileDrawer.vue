<template>
  <v-bottom-navigation v-model="value" fixed>
    <v-btn value="recent">
      <span>Users</span>
      <v-icon>mdi-account-multiple-outline</v-icon>
    </v-btn>

    <v-btn value="favorites">
      <span>Customization</span>
      <v-icon>mdi-cog-outline</v-icon>
    </v-btn>

    <v-btn value="nearby">
      <span>Account</span>
      <v-icon>mdi-account-cog-outline</v-icon>
    </v-btn>
  </v-bottom-navigation>
</template>

<script>
export default {
 data: () => ({ value: 'recent' }),
}
</script>
