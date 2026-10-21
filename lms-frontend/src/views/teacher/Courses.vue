<template>
  <router-view @createCourse="createCourse"/>
</template>

<script>

export default {
  props:['right'],
  components: {
  },
  methods: {
    createCourse() {
      this.$emit('createCourse')
    }
  }
}
</script>