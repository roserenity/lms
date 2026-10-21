<template>
  <v-sheet class="mx-5 my-10 d-flex align-start">
    <div class="mr-5 mt-2">
      <v-avatar>
        <v-img src="https://avatars0.githubusercontent.com/u/9064066?v=4&s=460"/>
      </v-avatar>
    </div>
    <div class="flex-grow-1">
      <div class="d-flex align-center justify-space-between">
        <div class="">
          <h3 class="nunito ">Ms. Darlene Robertson</h3>
          <div class="caption grey--text">TEACHER</div>
        </div>
        <div class="d-flex align-center">
          <v-btn text class=" nunito text-capitalize" style="font-size: 14px">
            <v-icon color="danger">mdi-pin</v-icon>
            Pinned
          </v-btn>
          <div class="ml-5 nunito " style="font-size: 14px">1 day ago</div>
        </div>
      </div>
      <div class="my-3">
        <h4 class="nunito">
          Lorem ipsum dolor sit amet, consectetur adipiscing elit. Est massa posuere vitae viverra. Volutpat, varius elementum turpis egestas lorem sed ipsum nibh dictum.
        </h4>
        <div class="my-1">
          <v-btn text small color="info">
            <v-icon small left>mdi-eye</v-icon>50
          </v-btn>
          <v-btn text small color="info">
            <v-icon small left>mdi-message</v-icon>25
          </v-btn>
        </div>
      </div>
      <v-divider />
    </div>
  </v-sheet>
</template>

<script>
export default {

}
</script>