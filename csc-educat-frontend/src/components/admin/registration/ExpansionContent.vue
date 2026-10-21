<template>
	<v-card outlined class="pa-2">
		<div class="col-md-6 col-xs-12">
			<FormTextFieldOptional
                  :label="`${column.input_field_label.toUpperCase()}${column.is_required ? ' *': ''}`"
				  :placeholder="column.input_field_type !== 'dropdown' ? column.input_field_type : column.is_multiple ? `${column.input_field_type} - multiple selection` : column.input_field_type"
                  :readonly="true"
                />
			<v-btn x-small text color="danger-1" class="f12 text-capitalize poppins" @click="$emit('deleteField', column.id)">Delete</v-btn>
			<v-btn x-small text color="primary" class="f12 text-capitalize poppins"
				@click="$emit('editField', {
				table: table,
				column: column
			})">Edit</v-btn>
			<v-menu v-if="table.custom_registration_fields.length > 1" :value="show" offset-y transition="slide-y-transition"
				:close-on-content-click="false" 
				content-class="elevation-2"
			>
				<template v-slot:activator="{ on, attrs }">
					<v-btn v-bind="attrs"
						v-on="on" 
						x-small text color="primary" class="f12 text-capitalize poppins">Move</v-btn>
				</template>
				<v-card class="pa-2" max-width="350">
					<label class="mx-2 poppins fw500 f12 secondary--text">Column</label>
					<div class="d-flex align-center mx-2">
						<div class="flex-grow-1">
							<v-select 
								:items="filtered" 
								dense 
								v-model="after"
								item-value="id"
								item-text="input_field_label"
								class="general-custom-field roboto f14 fw500 secondary--text"
								hide-details
							>
								<template slot="item" slot-scope="data">
									<v-list-item-content>
										<v-list-item-title class="roboto f14 fw500" >
											<span class="text-capitalize">{{data.item.input_field_label == 'first' ? '' : 'After'}} {{`${data.item.input_field_label}`}}</span>
										</v-list-item-title>
									</v-list-item-content>
								</template>
								<template slot="selection" slot-scope="data" >
									<div class="roboto f14 fw500 my-1">
										<span class="text-capitalize">{{data.item.input_field_label == 'first' ? '' : 'After'}} {{data.item.input_field_label}}</span>
									</div>
								</template>
							</v-select>
						</div>
						<div class="ml-2">
							<v-btn small color="primary" @click="move" v-if="!moving">Move</v-btn>
							<v-btn small color="primary" v-else>Moving please wait...</v-btn>
						</div>
					</div>
				</v-card>

			</v-menu>
		</div>
	</v-card>
</template>

<script>
import { mapActions, mapMutations } from 'vuex';


export default {
	props: ['column', 'columns', 'index', 'table'],
	data: () => ({
		after: '',
		show: null,
		moving: false
	}),
	computed: {
		filtered() {
			let filtered = [];

			this.columns.forEach((el, i) => {
				if(el.id != this.column.id) {
					if(this.index != 0) filtered.unshift({ input_field_label: 'first'})
					if(this.index && this.index-1 == i) {}
					else {
						filtered.push(el)	
					}
				}
			});
			return filtered
		}
	},
	methods: {
		...mapActions('admin', [
			'changeColumnPositionAction'
		]),

		...mapMutations(['alertMutation']),

		move() {
			let _sorted = []
			let _sort_key = -1

			if(!this.after) return
			this.moving=true

			if(this.after === 'first') {
				this.column.sort_no = _sort_key
				this.column.custom_registration_field_id = this.column.id
				_sorted.push(this.column)
				_sort_key++
			}

			this.columns.forEach(_col => {
				if(this.column.id !== _col.id && _col.id !== this.after) {
					_col.sort_no = _sort_key
					_col.custom_registration_field_id = _col.id
					_sorted.push(_col)
					_sort_key++
				} else if (this.column !== _col && this.after !== 'first' ) {
					_col.sort_no = _sort_key
					_col.custom_registration_field_id = _col.id
					_sorted.push(_col)
					_sort_key++
					this.column.sort_no = _sort_key
					this.column.custom_registration_field_id = this.column.id
					_sorted.push(this.column)
					_sort_key++
				}
			})

			this.changeColumnPositionAction({custom_registration_field_id: _sorted}).then(() => {
				this.show=false
				this.after=''
				this.moving=false
				this.alertMutation({
					show: true,
					text: 'Column has been successfully moved!',
              		type: "success"
				})
			})
		}
	},
}
</script>
