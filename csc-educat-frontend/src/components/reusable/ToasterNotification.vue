<template>
    <transition name="slide-fade">
      <v-alert
        elevation="1"
        class="border cursor-pointer"
        prominent 
        min-width="180"
        @click="$emit('click')"
      >
        <div :class="`d-flex flex-row align-center`">
          <v-sheet color="#C9410433" class="px-2 py-1 rounded-circle mr-2">
            <v-icon color="primary" v-if="header === 'Messages'" small>
              mdi-message-processing-outline
            </v-icon>
          </v-sheet>
          <div class="d-flex flex-column">
            <span class="f15 fw500">{{ header }}</span>
            <span class="poppins f14">{{ body }}</span>
          </div>
        </div>
        <template v-slot:close="{ toggle }">
          <v-btn icon class="ml-auto" dense small @click="$emit('close')"><v-icon small>mdi-close</v-icon></v-btn>
        </template>
      </v-alert>
    </transition>
</template>
  
<script>
import { mapState } from 'vuex'
  export default {
    props: {
      show: Boolean,
      header: String,
      body: String
    },
    computed: {
      ...mapState({
          customization: (state) => state.customization,
      }),
    }
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
  