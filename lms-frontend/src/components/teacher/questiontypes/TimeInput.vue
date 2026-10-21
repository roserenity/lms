<template>
    <v-menu
        ref="menu"
        v-model="menu"
        :close-on-content-click="false"
        :nudge-right="40"
        :return-value.sync="time"
        transition="scale-transition"
        offset-y
        max-width="290px"
        min-width="290px"
      >
        <template v-slot:activator="{ on, attrs }">
          <v-text-field
            v-model="time"
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
          v-model="time"
          full-width
          @click:minute="$refs.menu.save(time)"
          ampm-in-title
          color="primary"
        ></v-time-picker>
    </v-menu>
</template>

<script>
export default {
    data: () => ({
        menu: null,
        time: null,
    })
}
</script>