<template>
  <section id="course-card">
    <v-card outlined max-width="230"  :class="$vuetify.breakpoint.lgAndDown ? 'mx-auto' : ''">
      <v-img height="150" aspect-ratio="1" :src="`https://picsum.photos/500/300?image=${i}`"/>
      <v-card-text>
        <div class="d-flex justify-space-between align-center">
          <v-list-item-title class="poppins font-weight-bold black--text">
            Filipino
          </v-list-item-title>
          <v-list-item-subtitle class="caption nunito text-right">
            3 days ago
          </v-list-item-subtitle>
        </div>
        <v-list-item-subtitle class="poppins">
          Lorem ipsum dolor sit amet consectetur adipisicing elit. Animi non ipsa laborum, officiis quas voluptates iusto voluptate praesentium nihil quae ea alias mollitia unde. Maxime numquam error quisquam provident amet?
        </v-list-item-subtitle>
      </v-card-text>
      <v-card-actions>
        <v-btn 
          class="text-capitalize" 
          block 
          color="success"
          :to="{name: 'User Course Details', params: { id: 112 }}"
        >Overview</v-btn>
      </v-card-actions>
    </v-card>
  </section>
</template>

<script>
export default {
  props: ['i']
}
</script>
