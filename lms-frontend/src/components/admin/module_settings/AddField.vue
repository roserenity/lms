<template>
	<v-dialog persistent v-model="dialog" max-width="450">
    <v-card class="rounded-lg">
		<section class="d-flex flex-row align-center justify-center mx-5 pt-3">
			<h5 class="primary--text poppins fw600 text-wrap">{{ action === 'add' ? 'Add' : 'Update' }} Field</h5>
			<v-spacer/>
			<v-btn icon @click="close" :disabled="loading" dense>
			    <v-icon small>mdi-close</v-icon>
			</v-btn>
		</section >
		<v-form @submit.prevent="submit_form" ref="form" class="mx-5 pb-3">
            <FormTextFieldRequired
                :label="'FIELD LABEL *'"
                :value.sync="field_label"
                :error="error && error.input_field_label"
                :class_="'f14'"
            />
            <FormSelectObject
                :label="'TYPE OF FIELD *'"
                :items="types"
                :value.sync="field_type"
                :required="true"
                :error="error && error.input_field_type"
                :class_="'f14'"
            />
            
            <div v-if="field_type === 'dropdown'" class="mt-2">
                <label class="caption mb-1">ADD OPTIONS FOR THE DROPDOWN:</label>
                <v-row v-for="(option, i) in data" :key="i" align="center" no-gutters class="mb-1">
                    <v-col cols="10">
                        <FormTextFieldRequired
                            :value.sync="data[i]"
                        />
                    </v-col>
                    <v-col cols="2" class="d-flex flex-row">
                        <v-btn dense icon @click="addOption()">
                            <v-icon>
                                mdi-plus-circle
                            </v-icon>
                        </v-btn>
                        <v-btn dense icon @click="deleteOption(i)">
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
			<v-card-actions class="d-flex align-center mt-3 px-0">
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
        props: {
            dialog: {
                type: Boolean,
                default: false
            },
            field: {
                type: Object || null,
                default: null
            },
            action: {
                type: String,
                default: 'add'
            }
        },
		data: () => ({
			loading: false,
			field_label: '',
			field_type: '',
			required: false,
			multiple: false,
			types: [ 
                { text: 'TEXT AREA', value: 'text_area'},
                { text: 'TEXT FIELD', value: 'text_field'},
                { text: 'NUMBER', value: 'number'},
                { text: 'CALENDAR', value: 'calendar'},
                { text: 'DROPDOWN', value: 'dropdown'},
			],
			data: [],
			error: null
		}),
		watch: {
			dialog(val) {
                if(val) {
                    if(this.action === 'add') {
                        this.field_label = ''
                        this.field_type = ''
                        this.multiple = false
                        this.data = []
                    }
                    if(this.action === 'edit') {
                        this.field_label = this.field.input_field_label
                        this.field_type = this.field.input_field_type
                        this.multiple = this.field.is_multiple
                        this.data = JSON.parse(this.field.data)
                    }
                }
			},
			field_type(val) {
				if(val === 'dropdown') {
					this.data = []
					this.data.push('Type option here 1')
					this.data.push('Type option here 2')
				} else {
                    this.data = []
                }
			}
		},
		methods: {
			...mapActions('admin', [
				'addModuleFieldsAction', 'updateModuleFieldsAction'
			]),
			...mapMutations(['alertMutation']),

			submit_form() {
				this.error = null

				if(this.$refs.form.validate()) {
					this.loading = true
                    if(this.field_type === 'dropdown') {
                        if(this.data.length < 2) {
                            this.error = "There should be atleast 2 options"
                            this.loading =  false
                            return
                        }
                    }

                    if(this.action === 'add') {
                        this.addModuleFieldsAction({
                            input_field_label: this.field_label,
                            input_field_name: this.field_label.toLowerCase().replaceAll(' ', '_'),
                            input_field_type: this.field_type,
                            is_multiple: this.multiple,
                            data: JSON.stringify(this.data),
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
                    }
                    
                    if(this.action === 'edit') {
                        this.updateModuleFieldsAction({
                            id: this.field.id,
                            input_field_label: this.field_label,
                            input_field_name: this.field.input_field_name,
                            input_field_type: this.field_type,
                            is_multiple: this.multiple,
                            data: JSON.stringify(this.data),
                        }).then(() => {
                            this.loading=false
                            if(this.$errors()) return;
                            this.alertMutation({
                                show: true,
                                text: 'Field Updated Successfully.',
                                type: 'success'
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
				this.data.push('Type option here')
			},
			deleteOption(i){
				this.data.splice(i, 1)
			}
		}
	}
</script>