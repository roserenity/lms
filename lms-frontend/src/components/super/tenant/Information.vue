<template>
  <section id="information" class="ma-5 pt-5">
    <v-expansion-panels hover flat class="accordion">
      <v-expansion-panel
        v-for="(item,i) in 1"
        :key="i"
        class="my-1"
        active-class="active"
      >
        <v-expansion-panel-header class="header">
          PERSONAL INFORMATION
        </v-expansion-panel-header>
        <v-divider></v-divider>
        <v-expansion-panel-content class="pt-5">
           <div class="">
            <v-form>
              <label class="caption">FIRSTNAME</label>
              <v-text-field dense outlined />
              <label class="caption">LASTNAME</label>
              <v-text-field dense outlined />
              <label class="caption">EMAIL</label>
              <v-text-field dense outlined />
              <label class="caption">CONTACT</label>
              <v-text-field dense outlined />

            </v-form>
          </div>
        </v-expansion-panel-content>
      </v-expansion-panel>
    </v-expansion-panels>
  </section>
</template>

<script>
export default {
  data: () => ({
    item: ['Personal Information']
  })
}
</script>