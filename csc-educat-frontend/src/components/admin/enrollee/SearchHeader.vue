<template>
  <v-row no-gutters>
    <v-col cols="12" lg="6">
      <v-text-field solo flat placeholder="Search student" prepend-inner-icon="mdi-magnify"/>
    </v-col>
    <v-col cols="12" lg="2">
      <v-select solo flat placeholder="Filter Date" prepend-inner-icon="mdi-calendar"/>
    </v-col>
    <v-col cols="12" lg="2">
      <v-select solo flat placeholder="School Year"/>
    </v-col>
    <v-col cols="12" lg="2">
      <v-select solo flat placeholder="Show"/>
    </v-col>
  </v-row>
</template>

<script>
export default {

}
</script>
