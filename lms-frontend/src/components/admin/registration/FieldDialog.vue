<template>
	<v-dialog persistent v-model="dialog" max-width="450">
    <v-card class="rounded-lg">
		<v-card-title class="d-flex flex-row align-center justify-center mb-2">
			<v-spacer/>
			<h5 class="primary--text poppins fw600 text-wrap" v-if="type == 'add'">Add Field : <span class="text-capitalize secondary-2--text">{{table.title}}</span></h5>
			<h5 class="primary--text poppins fw600 text-wrap" v-if="type == 'edit'">Edit Field</h5>
			<v-spacer/>
			<v-btn icon @click="close" :disabled="loading">
			<v-icon size="22">mdi-close</v-icon>
			</v-btn>
		</v-card-title>
		<v-form @submit.prevent="submit_form" ref="form">
			<v-card-text>
				<FormTextFieldRequired
					:label="'FIELD LABEL *'"
					:value.sync="field_name"
					:error="error && error.input_field_name"
				/>
				<FormSelectObject
					:label="'TYPE OF FIELD *'"
					:items="types"
					:value.sync="field_type"
					:required="true"
					:error="error && error.input_field_type"
				/>
				
				<div v-if="field_type === 'dropdown'" class="ml-5">
					<label class="caption mb-1">ADD OPTIONS FOR THE DROPDOWN:</label>
					<v-row v-for="(option, i) in options" :key="i" align="center" no-gutters class="mb-1">
						<v-col cols="8">
							<FormTextFieldRequired
								:value.sync="options[i]"
							/>
						</v-col>
						<v-col cols="2" class="d-flex flex-row">
							<v-btn icon @click="addOption">
								<v-icon>
									mdi-plus-circle
								</v-icon>
							</v-btn>
							<v-btn icon @click="deleteOption(i)">
								<v-icon>
									mdi-delete-circle
								</v-icon>
							</v-btn>
						</v-col>
					</v-row>
					<div class="d-flex align-center">
						<v-checkbox v-model="multiple" />
						<label class="caption">ALLOW MULTIPLE CHOICES</label>
					</div>
				</div>
			</v-card-text>
			<v-divider/>
			<v-card-actions class="d-flex align-center mx-2">
				<div class="d-flex align-center">
					<v-checkbox v-model="required" />
					<label class="caption">REQUIRED</label>
				</div>
				<v-spacer></v-spacer>
				<section>
					<v-btn class="text-capitalize poppins" text @click="close" :disabled="loading" >Cancel</v-btn>
					<v-btn class="text-capitalize poppins" width="140" color="primary" type="submit" :loading="loading" >Save</v-btn>
				</section>
			</v-card-actions>
		</v-form>
    </v-card>
  </v-dialog>
</template>

<script>
import { mapActions, mapMutations } from 'vuex';
	export default {
		props:['type', 'dialog', 'table'],
		data: () => ({
			loading: false,
			field_name: '',
			field_type: '',
			required: false,
			multiple: false,
			types: [ 
				{ text: 'Text', value: 'text' },
				{ text: 'Email', value: 'email' },
				{ text: 'Password', value: 'password' },
				{ text: 'Number', value: 'number' },
				{ text: 'Date', value: 'date' },
				{ text: 'Dropdown', value: 'dropdown' },
			],
			options: [],
			error: null
		}),
		watch: {
			dialog(val) {
				if(this.type == 'edit') {
					this.field_type = this.table.column.input_field_type
					this.field_name = this.table.column.input_field_label
					this.required = this.table.column.is_required,
					this.multiple = this.table.column.is_multiple,
					this.options = [...this.table.column.data]
				} else {
					this.field_name = ''
					this.field_type = ''
					this.required = false
				}
			},
			field_type(val) {
				if(val === 'dropdown' && this.type === 'add') {
					this.options = []
					this.options.push('Type option here 1')
					this.options.push('Type option here 2')
				}
			}
		},
		methods: {
			...mapActions('admin', [
				'addFieldAction',
				'updateFieldAction'
			]),
			...mapMutations(['alertMutation']),

			submit_form() {
				this.error = null

				if(this.$refs.form.validate()) {
					this.loading = true
					if(this.type == 'add') {
						//let after = this.table.columns.length ? this.table.columns[this.table.columns.length-1].name : '';
						if(this.field_type === 'dropdown') {
							if(this.options.length < 2) {
								this.error = "There should be atleast 2 options"
								this.loading =  false
								return
							}
						}
						this.addFieldAction({
							custom_registration_page_id: this.table.id,
							input_field_label: this.field_name,
							input_field_name: this.field_name.replace(' ', '_'),
							input_field_type: this.field_type,
							is_multiple: this.multiple,
							is_required: this.required,
							data: this.options,
						}).then(() => {
							this.loading=false
							if(this.$errors()) return;
							this.alertMutation({
								show: true,
								text: 'Field Added Successfully.',
								type: 'success'
							})
							this.close()
						}).catch(e => {

							this.error = e
							this.loading = false
						})
					} else {
						this.updateFieldAction({
							custom_registration_field_id: this.table.column.id,
							input_field_label: this.field_name,
							input_field_name: this.field_name.toLowerCase().replace(' ', '_'),
							input_field_type: this.field_type,
							is_multiple: this.multiple,
							is_required: this.required,
							sort_no: this.table.column.sort_no,
							data: this.options,
						}).then(() => {
							this.loading=false
							if(this.$errors()) return;
							this.alertMutation({
								show: true,
								text: 'Field Updated Successfully.',
								type: "success"
							})
							this.close()
						}).catch(e => {
							this.error = e
							this.loading = false
						})
					}
				}
			},
			close() {
				this.$emit('close')
			},
			addOption() {
				this.options.push('Type option here')
			},
			deleteOption(i){
				this.options.splice(i, 1)
			}
		}
	}
</script>