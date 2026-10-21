<template>
  <v-sheet style="background-color: transparent; border-right: 2px solid #DEDEDE">
    <v-list id="app" style="background-color: transparent;" width="">
      <section v-for="(item, i) in items" :key="i">
        <v-list-item-group color="transparent">
          <v-subheader class="caption font-weight-bold text-uppercase">{{item.header}}</v-subheader>
          <v-list-item exact v-for="(list, j) in item.lists" :key="j" :to="{ name: list.route }"
          :style="$route.name === list.route ? 'border-right: 4px solid #85C751' : ''">
            <v-list-item-icon>
              <v-icon color="success">{{list.icon}}</v-icon>
            </v-list-item-icon>
            <v-list-item-content>
              <v-list-item-title class="" :class="$route.name === list.route ? 'black--text font-weight-bold' : 'grey--text'">
                {{list.title}}
              </v-list-item-title>
            </v-list-item-content>
          </v-list-item>
        </v-list-item-group>
      </section>
    </v-list>
  </v-sheet>
</template>

<script>
export default {
  props: ['items']
}
</script>