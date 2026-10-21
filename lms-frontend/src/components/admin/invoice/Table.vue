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
      <td colspan="7"><v-divider/></td>
      <tbody>
        <tr v-for="(item, i) in 1010" :key="i">
          <td >2022-00{{i+1}}</td>
          <td >Student {{i+1}}</td>
          <td>Grade {{i+1}}</td>
          <td>1200</td>
          <td class="success--text">PAID</td>
          <td class="success--text">PAID</td>
          <td>Jan. 1, 2022</td>
        </tr>
      </tbody>
    </template>
  </v-simple-table>
</template>

<script>
export default {
  data: () => ({
    headers: [
      'ID #',
      'Fullname',
      'Grade Level',
      'Amount',
      'Reg. Fee',
      'Status',
      'Date Updated',
    ]
  })
}
</script>