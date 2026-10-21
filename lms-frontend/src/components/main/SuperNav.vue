<template>
  <div class="mx-2">
    <v-list dense class="drawer">
      <section v-for="(item, i) in lists" :key="i">
        <div v-if="!mini" :class="[i==0?'mt-3':'mt-7', getColorByBgColor]" class="subtitle ml-7 text-uppercase">{{item.subheader}}</div>
        <v-list-item v-for="(list, j) in item.lists" :key="j" class="mx-2 li">
          <v-list-item-title class="roboto f14 d-flex align-center secondary-2--text">
            <v-checkbox v-model="filter[list.value]" dense hide-details color="secondary-2" @change="(e) => toggle(list.value, e)"/><div class="mt-2 fw500 f12">{{list.title}}</div>
          </v-list-item-title>
        </v-list-item>
      </section>
    </v-list>
    
  </div>
</template>

<script>
import { mapActions, mapState, mapMutations } from 'vuex'


export default {
  props: ['lists', 'mini'],
  data: () => ({
    filter: {
      general_education: true,
      corporate: true,
      ngo: true,
      active: true,
      inactive: false,
      today: false, 
      this_week: false, 
      this_month: false, 
      this_year: false
    }
  }),
  methods: {
    ...mapActions('superad',[
      'getTenantsAction', 
    ]),

    ...mapMutations(['alertMutation']),

    ...mapMutations('superad', ['loadingMutation','pageMutation']),

    toggle(filter, value) {
      this.filter[filter] = value
      this.loadingMutation(true)
      this.getTenantsAction({...this.filter}).then(()=>{
        this.loadingMutation(false)
      }).catch(() => {
        this.alertMutation({
            show: true,
            text: 'Something went wrong.',
            type: "error"
        })
        this.loadingMutation(false)
      })
    }
  },
  computed: {
    ...mapState({
      customization: (state) => state.customization
    }),

    getColorByBgColor() {
      if(!localStorage.getItem('customization')) return '#ffffff'
      if(!this.customization.navigation_background_color)  return '#ffffff'
      return (parseInt(this.customization.navigation_background_color.replace('#', ''), 16) > 0xffffff / 2) ? 'black--text' : 'white--text';
    }
  },
}
</script>