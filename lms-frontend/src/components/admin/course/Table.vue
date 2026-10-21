<template>
  <v-simple-table>
    <template v-slot:default>
      <thead>
        <tr style="">
          <th v-for="(item, i) in headers" :key="i" class="body-2 nunito font-weight-bold text-left text-uppercase">
            {{item}}
          </th>
        </tr>
      </thead>
      <td colspan="6"><v-divider/></td>
      <tbody>
        <tr v-for="(item, i) in 1010" :key="i">
          <td >Filipino {{i+1}}</td>
          <td >Teacher {{i+1}}</td>
          <td>Grade {{i+1}}</td>
          <td>Code 000-{{i+1}}</td>
          <td>
            <v-btn text color="success" class="nunito text-capitalize font-weight-bold">
              Course Overview
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
    headers: [
      'Course',
      'Intructor',
      'Grade Level',
      'Class Code',
      'Action',
    ]
  })
}
</script>