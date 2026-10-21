<template>
  <section id="assessment-builder">
    <v-card>
      <div class="d-flex justify-space-between align-center">
        <h5 class="ma-2 text-uppercase">Assessment Builder</h5>
        <h5 class=" mx-2"></h5>
      </div>
      <v-divider/>
      <v-card-text class="mt-5">
        <div class="caption text-uppercase">
          Select Assessment Type
        </div>
        <div class="col-xl-6 col-lg-8">
          <v-item-group mandatory>
            <v-row no-gutters>
              <v-col cols="3" v-for="(item, i) in items" :key="i">
                <v-item v-slot="{ active, toggle }">
                  <v-sheet flat max-width="80" @click="toggle">
                    <v-card class="text-center py-2" :color="active ? 'secondary' : ''">
                      <v-icon size="50" :color="item.color">{{item.icon}}</v-icon>
                    </v-card>
                    <div class="text-center my-5">
                      <h4 class="">{{item.title}}</h4>
                    </div>
                  </v-sheet>
                </v-item>
              </v-col>
            </v-row>
          </v-item-group>
        </div>
        <v-form class="">
          <label class="caption">ASSESSMENT TITLE</label>
          <v-text-field outlined  dense  class="col-xl-4 col-lg-6"/>
          <label class="caption">GRADE LEVEL</label>
          <v-select outlined  dense  class="col-xl-4 col-lg-6"/>
          <label class="caption">DESCRIPTION</label>
          <vue-editor class="mb-7" style="width: 600px"/>
          <label class="caption">NO. OF QUESTIONS</label>
          <v-text-field outlined  dense  class="col-xl-4 col-lg-6"/>
        </v-form>
        
      </v-card-text>
    </v-card>
  </section>
</template>

<script>
export default {
  data: () => ({
    items: [
      {
        title: 'Quiz',
        icon: 'mdi-numeric-1-box-multiple',
        color: '#FFC522'
      },
      {
        title: 'Assignment',
        icon: 'mdi-account-box',
        color: '#0386FF'
      },
      {
        title: 'Activity',
        icon: 'mdi-view-compact',
        color: '#61B221'
      },
      {
        title: 'Long Test',
        icon: 'mdi-clipboard-list',
        color: '#FD5E2C'
      },
    ]
  })
}
</script>
