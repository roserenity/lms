<template>
  <section>
    <v-btn color="danger" 
      dark 
      absolute 
      right 
      elevation="0"
      fixed
      v-show="btnNotif"
      @click="$emit('collapse')"
      style="z-index: 1;border-radius: 0px"
      :style="right ? 'margin-right: 280px' : 'right: 0'"
      v-click-outside="{handler: outsideClick}"
    >
      <v-icon size="32">
        {{right ? 'mdi-chevron-right' : 'mdi-chevron-left'}}
      </v-icon>
    </v-btn>
  </section>
</template>

<script>
export default {
  props: ['right', 'btnNotif', 'overlay'],
  methods: {
    outsideClick() {
      if(!this.overlay) this.$emit('clickOut')
    },
  }
}
</script>