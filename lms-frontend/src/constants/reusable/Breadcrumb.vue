<template>
  <v-breadcrumbs :items="items" class="mb-0 pb-1">
    <template v-slot:item="{ item }">
      <v-breadcrumbs-item
        class="poppins f14 fw600 text-capitalize"
        :disabled="item.disabled"
      >
      
        <router-link :to="item.to" v-if="item.hasOwnProperty('link') && item.link && !item.disabled" class="primary--text" exact>
          {{ item.text }}
        </router-link>
        <span v-else class="secondary--text">{{ item.text }}</span>
      </v-breadcrumbs-item>
    </template>
    <template v-slot:divider>
      <v-icon size="20">reremdi-chevron-right</v-icon>
    </template>
  </v-breadcrumbs>
</template>

<script>
export default {
  props: {
    items: {
        type: Array,
        default: []
    },
  },
};
</script>

<style></style>
