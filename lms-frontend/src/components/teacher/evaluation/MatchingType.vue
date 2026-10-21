<template>
    <v-container>
      <v-alert 
        v-for="(item, index) in matchingPairs" 
        :key="index" 
        shaped 
        outlined 
        color="secondary-2" 
        class="pa-0 col-md-8 col-12 my-2"
      >
        <div class="d-flex flex-row justify-space-between">
          <v-sheet class="rounded-tl-xl pa-5" width="100%">
            {{ item.source.title }}
          </v-sheet>
          <div class="d-flex flex-column align-center">
            |
            <v-icon small>
              mdi-rhombus-split
            </v-icon>
            |
          </div>
          <v-sheet class="rounded-br-xl pa-5" width="100%">
            {{ item.target.title }}
          </v-sheet>
        </div>
      </v-alert>
    </v-container>
</template>
  
<script>
export default {
    props: {
        matchingPairs: {
            type: Array,
            required: true,
            default: () => []
        }
    }
}
</script>
  