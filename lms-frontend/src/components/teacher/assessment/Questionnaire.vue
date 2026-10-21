<template>
  <section id="questionnaier">
    <div class="ma-2">
      <v-expansion-panels hover flat class="accordion">
        <v-expansion-panel
          v-for="(item,i) in 5"
          :key="i"
          class="my-1"
          active-class="active"
        >
          <v-expansion-panel-header class="header">
            Question {{i+1}}
          </v-expansion-panel-header>
          <v-divider></v-divider>
          <v-expansion-panel-content class="pt-5">
            
          </v-expansion-panel-content>
        </v-expansion-panel>
      </v-expansion-panels>
    </div>
  </section>
</template>

<script>
export default {

}
</script>