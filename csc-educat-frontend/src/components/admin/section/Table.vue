<template>
  <section id="section-table">
    <v-simple-table>
      <template v-slot:default>
        <thead>
          <tr style="background-color: #F1F1F1">
            <th v-for="(item, i) in header" :key="i" class="body-2 nunito font-weight-bold text-left text-uppercase">
              {{item}}
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(item, i) in 10" :key="i">
            <td>Class Section {{i+1}}</td>
            <td><v-chip>Mr. Dela Cruz</v-chip></td>
            <td>Code102{{i+1}}</td>
            <td>129</td>
            <td>
              <v-btn text color="primary poppins" class="text-capitalize">
                View
              </v-btn>
            </td>
          </tr>
        </tbody>
      </template>
    </v-simple-table>
  </section>
</template>

<script>
export default {
  data: () => ({
    header: ['Class Name', 'Teacher/s', 'Join Code', 'Enrolled', 'Action'],
  })
}
</script>
