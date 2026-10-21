<template>
  <v-dialog persistent v-model="dialog" max-width="530">
    <v-card rounded="lg">
			<v-card-text>
				<v-btn icon absolute right class="mt-3"  @click="close" :disabled="loading">
					<v-icon>mdi-close</v-icon>
				</v-btn>
				<div class="text-center py-5">
					<v-icon size="90" color="danger-1">mdi-alert-circle</v-icon>
					<h3 class="poppins black--text mt-5">
						{{`Are you sure you want to delete this ${data.type.toLowerCase()} ?`}}
					</h3>
					<div class="secondary--text roboto f14 col-lg-10 mx-auto">
						{{data.type == 'Page' ? `All data fields will be removed from the registration page.`
						: `This will be permanently removed on the registration page.`}}
					</div>	
				</div>
			</v-card-text>
			<v-divider/>
			<v-card-actions class="d-flex align-center justify-center mx-5 poppins">
				<v-btn text @click="close" :disabled="loading">Cancel</v-btn>
				<v-btn color="error" @click="remove" :loading="loading">Delete</v-btn>
			</v-card-actions>
		</v-card>
  </v-dialog>
</template>

<script>
import { mapActions, mapMutations } from 'vuex';
	export default {
		props:['dialog', 'data'],
		data: () => ({
			loading: false
		}),
		methods: {
			...mapActions('admin', [
				'deleteTableAction',
				'deleteFieldAction',
			]),
			...mapMutations(['alertMutation']),

			// add() {
			// 	// console.log(this.required);
			// 	this.addFieldAction({
			// 	}).then(() => {
			// 		if(this.$errors()) return;
			// 		this.alertMutation({
			// 			show: true,
			// 			text: 'Page deleted successfully.'
			// 		})
			// 		this.close()
			// 	})
			// },

			remove() {
				this.loading = true

				if(this.data.type === 'Page') {
					this.deleteTableAction({custom_registration_page_id: this.data.id}).then(() => {
						this.alertMutation({
							show: true,
							text: `${this.data.type} deleted successfully.`,
							type: "success"
						})
						this.loading = false
						this.close()
					}).catch(() => {
						this.alertMutation({
							show: true,
							text: `Something went wrong.`,
							type: "error"
						})
					})
				} else if (this.data.type === 'Field') {
					this.deleteFieldAction(this.data.id).then(() => {
						this.alertMutation({
							show: true,
							text: `${this.data.type} deleted successfully.`,
							type: "success"
						})
						this.loading = false
						this.close()
					}).catch(() => {
						this.alertMutation({
							show: true,
							text: `Something went wrong.`,
							type: "error"
						})
					})
				}
			},

			close() {
				this.$emit('close')
			}
		}
	}
</script>