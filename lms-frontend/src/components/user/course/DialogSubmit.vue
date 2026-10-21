<template>
    <section>
      <v-dialog persistent max-width="400" v-model="dialog">
       <v-card rounded="lg">
          <v-btn icon absolute right class="mt-3" @click="$emit('cancel')">
            <v-icon>mdi-close</v-icon>
          </v-btn>
          <v-card-text>
            <div class="text-center pt-5">
              <v-icon size="90" color="danger-1">mdi-alert</v-icon>
              <h3 class="poppins black--text mt-5">
                {{ `Are you sure you want to submit your answers ?` }}
              </h3>
            </div>
          </v-card-text>
          <v-divider/>
          <v-card-actions class="d-flex align-center justify-center">
              <!-- :disabled="data.lock_question_after_answering"  -->
            <v-btn 
              class="text-capitalize poppins fw600 mr-3" 
              outlined 
              color="primary"
              @click="$emit('review')">
              Review
            </v-btn>
            <v-btn @click="$emit('submit')" class="text-capitalize poppins fw600" color="primary" :loading="loading">Submit</v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
    </section>
  </template>
  
  <script>
  export default {
    props: ['dialog', 'data', 'loading'],
    data: () => ({
      
    })
  } 
  </script>