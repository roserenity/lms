<template>
  <section>
    <div class="d-flex align-center my-2">
      <v-radio color="success" class="" :value="`choice_${index}`" readonly/>
      <div class="roboto f14 fw400 secondary-3--text mt-n1 mx-1">
        {{ item[`choice_${index}`]}}
      </div>
    </div>
  </section>
</template>

<script>
export default {
  props: ["item", "index"],
};
</script>

<style></style>
