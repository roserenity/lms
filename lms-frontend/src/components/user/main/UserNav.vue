<template>
  <div class="mt-10 fade">
	<v-list dense class=" dense pa-0 mt-0" v-for="(list, i) in lists" :key="i">
      	<v-subheader dense v-if="list.subheader && !mini" class="f12 caption fw500 text-uppercase px-3 secondary-4--text">{{ list.subheader }}</v-subheader>
		<section v-for="(item, j) in list.lists" :key="j">
			<v-list-item v-if="item.sublinks.length === 0" 
				class="px-4 li" 
				dense
				exact
				:to="{ name: item.route }"
				:class="$route.meta.parent === item.route ? 'navigation_link_color--text':'' "
				:style="mini ? ($route.meta.parent === item.route ? {  background: getlinkBackgroundColor, color: getlinkColor, borderRight: `5px solid ${getlinkColorMini}` } : '') : ($route.meta.parent === item.route ? {  background: getlinkBackgroundColor, color: getlinkColor, borderRight: `5px solid ${getlinkColor} !important`} : '')"
			>
				<v-list-item-content >
					<v-list-item-title class="roboto f14 align-center "
						:class="[$route.meta.parent === item.route ? 'navigation_link_color--text' : 'secondary-2--text', mini && 'd-flex justify-center']">
						
						<v-icon v-if="mini" class="px-1" size="20" :class="$route.meta.parent === item.route  ? 'mini_navigation_icon_color--text' : 'secondary-2`--text'">
							{{item.icon}}
						</v-icon>
						<v-icon v-else class="px-1" size="20" :class="$route.meta.parent === item.route ? 'navigation_link_color--text' : 'secondary-2--text'">{{item.icon}}</v-icon>
						
						<span v-if="!mini" class="fw500 ">
								{{ item.title }}
						</span>
					</v-list-item-title>
				</v-list-item-content>
			</v-list-item>
		</section>
	</v-list>
  </div>
</template>

<script>
import { mapState } from "vuex";

export default {
  props: ['lists', 'mini'],
	methods: {
		collapse() {
			if(this.$route.name == 'User Assessment') return
			this.$emit('collapse', true)
		}
	},
	watch: {
		$route (to, from){
			if(['User Course Details', 'User Course Content'].includes(to.name)) {
				this.$emit('collapse', true)
			} else this.$emit('collapse', false)
    	}
	},
	computed: {
		...mapState({
			customization: (state) => state.customization,
			tenant: (state) => state.tenant,
		}),

		...mapState("usr", {
			cart: (state) => state.cart
		}),

		getlinkColor(){
			return this.customization.hasOwnProperty('navigation_text_icon_color') ? this.customization.navigation_text_icon_color ? this.customization.navigation_text_icon_color : '#b52210' : '#b52210'
		},
    
		getlinkColorMini(){
			return this.customization.hasOwnProperty('mini_navigation_link_color') ? this.customization.mini_navigation_link_color ? this.customization.mini_navigation_link_color : '#b52210' : '#b52210'
		},

		getlinkBackgroundColor(){
			return this.customization.hasOwnProperty('mini_navigation_link_color') ? this.customization.mini_navigation_link_color ? this.customization.mini_navigation_link_color.concat('33') : '#b5221033' : '#B5221033'
		},

		getColorByBgColorMini() {
			if(this.customization.mini_navigation_background_color === '#b52210') return 'secondary-2--text'
			return this.customization.mini_navigation_background_color ? ((parseInt(this.customization.mini_navigation_background_color.replace('#', ''), 16) > 0xffffff / 2) ? 'secondary-2' : 'grey') : 'grey';
		}
	}
}
</script>