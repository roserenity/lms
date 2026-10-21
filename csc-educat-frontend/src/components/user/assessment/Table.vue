<template>
  <v-simple-table style="padding: 0px; margin: 0px">
    <template v-slot:default>
      <thead>
        <tr style="background-color: #fff">
          <th v-for="(item, i) in items" :key="i" 
          class="body-2 nunito font-weight-bold text-left text-uppercase">
            {{item}}
          </th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(item, i) in 5" :key="i" class="poppins">
          <td>Filipino</td>
          <td>
            <v-icon>mdi-format-list-numbered</v-icon>
            Quiz
          </td>
          <td>Jan 28 at 11:59 pm</td>
          <td class="grey--text">Locked</td>
          <td>
            <v-btn color="info" rounded class="white--text text-capitalize">
              Start Quiz
            </v-btn>
          </td>
        </tr>
      </tbody>
    </template>
  </v-simple-table>
</template>

<script>
export default {
  data: () => ({
    items:['Subject', 'Type', 'Due', 'Status', 'Action  '],
    
  })
}
</script>
