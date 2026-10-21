<template>
  <v-dialog persistent v-model="dialog" max-width="380">
    <v-card rounded="lg">
      <v-card-text>
        <v-btn icon absolute right class="mt-3" @click="$emit('close')">
          <v-icon>mdi-close</v-icon>
        </v-btn>
        <div class="text-center pt-10">
          <v-icon size="90" color="danger-1">mdi-alert-circle</v-icon>
          <h3 class="poppins black--text mt-5">
            {{ `Draft this course ?` }}
          </h3>
          <div class="secondary--text roboto f14 col-lg-11 mx-auto">
            {{ `You will not be able to undo this action.` }}
          </div>
        </div>
      </v-card-text>
      <v-divider />
      <v-card-actions class="d-flex align-center justify-center">
        <v-btn text class="text-capitalize" width="100" @click="$emit('close')"
          >Cancel</v-btn
        >
        <v-btn
          :loading="loading"
          outlined
          color="danger-1"
          class="text-capitalize"
          width="150"
          @click="draft()"
          >Save to drafts</v-btn
        >
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
import { mapActions } from 'vuex'
export default {
	props: ['dialog','course'],
	data: () => ({
		loading: false
	}),
	methods: {
		...mapActions('instructor', ['statusCourseAction', 'showCourseAction']),

		draft() {
			
			this.loading=true
			this.statusCourseAction({
				id: this.course.id,
        status: "draft",
			}).then(() => {
				this.showCourseAction(this.course.id)
				this.loading	=false
				this.$emit('close')
			})
		}
	}
}
</script>

<style>

</style>