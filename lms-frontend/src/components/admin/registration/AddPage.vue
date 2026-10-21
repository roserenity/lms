<template>
	  <v-dialog persistent v-model="dialog" max-width="450">
    <v-card class="rounded-lg">
      <v-card-title class="d-flex align-center justify-center mb-2">
				<v-spacer/>
        <h5 class="primary--text poppins fw600 text-center">Add Page</h5>
				<v-spacer/>
        <v-btn icon @click="close">
          <v-icon size="22">mdi-close</v-icon>
        </v-btn>
      </v-card-title>
			 <v-form @submit.prevent="add" ref="form">
      <v-card-text>
		<FormTextFieldRequired
			:label="'PAGE LABEL NAME *'"
			:value.sync="name"
		/>
      </v-card-text>
      <v-divider/>
      <v-card-actions class="d-flex align-center justify-end mx-2">
        <v-btn class="text-capitalize poppins" text @click="close">Cancel</v-btn>
        <v-btn class="text-capitalize poppins" width="140" color="primary" type="submit" v-if="!adding">Save</v-btn>
        <v-btn class="text-capitalize poppins" width="140" color="primary" v-else>Saving...</v-btn>
      </v-card-actions>
			</v-form>
    </v-card>
  </v-dialog>
</template>

<script>
import { mapActions, mapMutations } from 'vuex';
	export default {
		props:['dialog'],
		data: () => ({
			adding: false,
			name: ''
		}),
		methods: {
			...mapActions('admin', [
				'addRegistrationTableAction'
			]),
			...mapMutations(['alertMutation']),

			add() {
				if(this.$refs.form.validate()){
					this.adding=true
					this.addRegistrationTableAction({
						title: this.name
					}).then(() => {
						this.adding=false
						if(this.$errors()) return;
						this.alertMutation({
							show: true,
							text: 'Page Added Successfully.',
							type: "success"
						})
						this.close()
					}).catch(() => {
						this.adding=false
					})
				}
			},
			close() {
				this.name=''
				this.$emit('close')
			}
		}
	}
</script>