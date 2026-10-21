<template>
  <div class="d-flex align-center">
    <div class="mb-1" v-for="(item, i) in links" :key="i">
      <section
          v-if="item.link">
        <v-btn
          text
          exact
          class="text-capitalize poppins fw600 f14"
          :to="item.route"
          dense
          small
        >
          <div style="max-width: 30vw !important;" class=" no-space ellipsis">
            {{ item.label }}
          </div>
        </v-btn>
        <b v-if="links.length > 1"> / </b>
      </section>
      <div v-else class="poppins fw600 f14 no-space primary--text mx-5 ellipsis" style="max-width: 30vw !important;">
         {{ item.label }}
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: ["links"],
};
</script>

<style>
.ellipsis {
    overflow: hidden !important;
    white-space: nowrap !important;
    text-overflow: ellipsis !important;
}
</style>
