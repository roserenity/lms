<template>
  <v-sheet width="200" class="">
    <h3 class="ma-5 text-center nunito">Grade Levels</h3>
    <v-divider/>
    <v-list class="mx-5" dense>
      <v-list-item v-for="(item, i) in levels" :key="i">
        <v-list-item-content>
          <v-list-item-title class="poppins text-center primary--text " style="font-weight: 600; font-size: 14px">
            {{item}}
          </v-list-item-title>
        </v-list-item-content>
      </v-list-item>
    </v-list>
  </v-sheet>
</template>

<script>
export default {
  data: () => ({
    levels: [
      'All',
      'Nursery',
      'Kinder',
      'Grade 1',
      'Grade 2',
      'Grade 3',
      'Grade 4',
      'Grade 5',
      'Grade 6',
      'Grade 7',
      'Grade 8',
      'Grade 9',
      'Grade 10',
      'Grade 11',
      'Grade 12',
    ]
  })
}
</script>