<template>
  <section style="height: 99% !important;">
    <v-sheet height="100%" :color="loading ? `${item.outline}11`:item.color" class="pa-3 custom-border border" :style="` color: ${item.outline}`">
      <section style="height: 99% !important;" class="fade d-flex flex-column justify-space-evenly" v-if="!loading">
        <div class="text-right f20 fw600">
          {{item.count}}
        </div>
        <div class="text-right f13 fw600">
          {{item.title}}
        </div>
      </section>
      <section v-else>
        <v-skeleton-loader
            width=50
            type="text"
            class="ml-auto mb-1"
          ></v-skeleton-loader>
          <v-skeleton-loader
            type="text"
            class="ml-auto"
          ></v-skeleton-loader>
      </section>
    </v-sheet>

  </section>
</template>

<script>
export default {
  props: ['item', 'loading']
}
</script>