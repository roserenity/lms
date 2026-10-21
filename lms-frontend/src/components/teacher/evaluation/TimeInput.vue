<template>
  <v-container class="px-5 f12">
    ANSWER: <input type="time" readonly v-model="answer"/>
  </v-container>
    <!-- <v-menu
        ref="menu"
        v-model="menu"
        :close-on-content-click="false"
        :nudge-right="40"
        :return-value.sync="answer"
        transition="scale-transition"
        offset-y
        max-width="290px"
        min-width="290px"
      >
        <template v-slot:activator="{ on, attrs }">
          <v-text-field
            v-model="answer"
            prepend-icon="mdi-clock-time-four-outline"
            readonly
            v-bind="attrs"
            v-on="on"
            class=" col-sm-6 f12 poppins"
            placeholder="hh:mm"
          ></v-text-field>
        </template>
        <v-time-picker
          v-if="menu"
          v-model="answer"
          full-width
          @click:minute="$refs.menu.save(answer)"
          ampm-in-title
          color="primary"
        ></v-time-picker>
    </v-menu> -->
</template>

<script>
export default {
  props: ['answer'],
  data: () => ({
      menu: null,
  })
}
</script>