<template>
  <v-row no-gutters>
    <v-col cols="12" lg="3">
      <v-text-field solo flat placeholder="Search" prepend-inner-icon="mdi-magnify"/>
    </v-col>
    <v-col cols="12" lg="3">
      <v-select solo flat placeholder="Filter by" prepend-inner-icon="mdi-calendar"/>
    </v-col>
    <v-col cols="12" lg="3">
      <v-select solo flat placeholder="Grade Level"/>
    </v-col>
    <v-col cols="12" lg="3">
      <v-select solo flat placeholder="School Year"/>
    </v-col>
  </v-row>
</template>

<script>
export default {

}
</script>
