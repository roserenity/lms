<template>
  <section id="search-header">
    <v-divider/>
      <v-sheet>
        <v-row no-gutters>
          <v-col cols="12" lg="4">
            <v-text-field solo flat placeholder="Search" hide-details prepend-inner-icon="mdi-magnify"/>
          </v-col>
          <v-col cols="12" lg="2">
            <v-select solo flat hide-details placeholder="Payment Type"/>
          </v-col>
          <v-col cols="12" lg="2">
            <v-select solo flat hide-details placeholder="School Year"/>
          </v-col>
          <v-col cols="12" lg="2">
            <v-select solo flat hide-details placeholder="Date" prepend-inner-icon="mdi-calendar"/>
          </v-col>
          <v-col cols="12" lg="2">
            <v-select solo flat hide-details placeholder="Show"/>
          </v-col>
        </v-row>
      </v-sheet>
    <v-divider/>
  </section>
</template>

<script>
export default {

}
</script>

<style>

</style>