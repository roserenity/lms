<template>
  <div class="mt-10">
    <v-list dense class="drawer">
      <section v-for="(item, i) in lists" :key="i">
        <v-list-item 
        v-for="(list, j) in item.lists" :key="j"
        :to="{ name: list.route }"
        exact
        class="px-7 li" 
        :class="$route.meta.collapse ? '' : $route.name === list.route ? 'li-active' 
        : $route.meta.parent === list.route ? 'li-active' :  ''">
          <v-list-item-content>
            <v-list-item-title class="roboto f14 d-flex align-center " :class="$route.name === list.route ? 'primary--text'  : $route.meta.parent === list.route ? 'primary--text' : 'secondary-1--text'">
              <v-icon v-if="mini" size="20" :color="$route.meta.parent == list.route ? '#ffff' : '#b2bec3'">{{list.icon}}</v-icon>
              <v-icon v-else size="20" >{{list.icon}}</v-icon>
              <div v-if="!mini" class="mx-3 fw500">{{list.title}}</div>
            </v-list-item-title>
          </v-list-item-content>
        </v-list-item>
      </section>
    </v-list>
  </div>
</template>

<script>
export default {
  props: ['lists', 'mini']
}
</script>