<template>
  <transition name="slide-fade">
    <v-alert
      :value="show"
      :color="type === 'error' ? 'error' : 'success-1'"
      elevation="2"
      top
      right
      class="toast border"
      prominent 
      absolute
      min-width="200"
    >
      <div :class="`white--text fw500 poppins f14 text-wrap`">
        <v-icon v-if="type === 'success'" color="white" left>mdi-check-circle</v-icon>
        <v-icon v-if="type === 'error'" color="white" left>mdi-alert-circle</v-icon>
        {{ text }}
      </div>
    </v-alert>
  </transition>
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
<style scoped>
.v-snack__content {
  border-left: 5px solid #85c751 !important;
  border-radius: inherit !important;
}

.slide-fade-enter-active {
  transition: all 8s ease-in;
}
.slide-fade-leave-active {
  transition: all .3s cubic-bezier(1.0, 0.5, 0.8, 1.0);
}
.slide-fade-enter, .slide-fade-leave-to
/* .slide-fade-leave-active below version 2.1.8 */ {
  transform: translateY(30px);
  opacity: 0;
}
</style>
