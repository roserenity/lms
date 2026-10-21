<template>
  <section id="ticket-form">
    <v-form class="ma-5">
      <label class="caption">EMAIL ADDRESS</label>
      <v-text-field class="mb-5" outlined dense hide-details/>
      <label class="caption">CONCERN CATEGORY</label>
      <v-select class="mb-5" outlined dense hide-details/>
      <label class="caption">CONCERN DETAILS</label>
      <v-textarea class="mb-5" outlined dense hide-details/>
      <div class="col-xl-6 col-lg-8 mx-auto">
        <v-btn large block color="success" class="body-1 font-weight-bold">
          Submit
        </v-btn>
      </div>
    </v-form>
  </section>
</template>

<script>
export default {

}
</script>