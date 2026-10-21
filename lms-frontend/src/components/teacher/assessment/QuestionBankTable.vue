<template>
  <section id="question-bank-table">
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
            <td>Juan Dela Cruz</td>
            <td>Assessment {{i+1}}</td>
            <td>
              {{desc.length > 10 ? desc.slice(0, 20) + '...' : desc}}
            </td>
            <td>Answer {{i+1}}</td>
            <td>['TRUE', 'FALSE']</td>
            <td>Jan 20, 2020 by 11:59pm</td>
            <td>
              <v-btn @click="$router.push({name: 'Teacher Assessment Details', params: { id: 1010 }})" 
              text class="text-capitalize">
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
    tab: null,
    dialog: false,
    items: ['My Assessments', 'Assessment Bank'],
    header: ['Uploader', 'Type', 'Question', 'Answer', 'Choices', 'Date Added', ''],
    desc: 'Lorem ipsum dolor sit amet consectetur, adipisicing elit. Ipsa ea esse quae quisquam, doloremque harum unde incidunt nam dolorum earum tempore nesciunt nostrum obcaecati, veniam quam, aut vero. Autem, asperiores.',
    url: 'https://www.youtube.com/watch?v=M5bkt4afkaE&list=RDM5bkt4afkaE',
  })
}
</script>