<template>
  <section id="user-form">
    <v-form class="ma-5">
      <div class="d-flex ">
        <div class="">
          <label class="poppins grey--text">FULLNAME</label>
          <v-text-field outlined dense hide-details/> 
        </div>
        <div class="ml-5">
          <label class="poppins grey--text">EMAIL</label>
          <v-text-field outlined dense hide-details/> 
        </div>
      </div>
      <div class="d-flex my-5">
        <div class="">
          <label class="poppins grey--text">GRADE LEVEL</label>
          <v-select outlined dense hide-details/>
        </div>
        <div class="ml-5">
          <label class="poppins grey--text">CLASS SECTION</label>
          <v-select outlined dense hide-details/>
        </div>
      </div>
      <div class="d-flex">
        <div class="d-flex align-end">
          <v-btn text color="danger" class="text-capitalize">
            Upload .csv file
          </v-btn>
        </div>
      </div>
    </v-form>
    <v-divider/>
    <div class="d-flex justify-end align-center mt-3 mx-5">
      <v-btn text>Cancel</v-btn>
      <v-btn color="success">Save</v-btn>
    </div>
  </section>
</template>

<script>
export default {
  
}
</script>