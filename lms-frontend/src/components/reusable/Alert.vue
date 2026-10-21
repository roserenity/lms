<template>
    <v-alert
        :value="show"
        border="left"
        :color="type"
        :type="type"
        class="mt-3"
        dense
        outlined
    >
        <div :class="`${type}--text fw500 primary-font f12 text-wrap`">
        {{ text }}
        </div>
    </v-alert>
</template>

<script>
export default {
  props: {
    show: Boolean,
    text: String,
    type: String
  },
};
</script>
