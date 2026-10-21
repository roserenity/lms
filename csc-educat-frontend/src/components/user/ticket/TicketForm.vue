<template>
  <v-card id="ticket-form">
    <v-form ref="form" class="pa-5">
      <FormTextFieldRequired
        :label="'EMAIL ADDRESS (already registered in the system)'"
        :value.sync="form.email"
        :error="error.email"
        class="mb-3"/>

      <label class="caption">CONCERN CATEGORY</label>
      <v-select 
        outlined 
        dense 
        :items="categories"
        item-text="text"
        item-value="value"
        v-model="form.concern_category"
        :rules="[v => !!v || 'This is required']"
        hide-details="auto"
        :error-messages="error.concern_category"
        required
        class="mb-3"/>

      <FormTextAreaRequired
        :label="'CONCERN DETAILS'"
        :value.sync="form.content"
        :error="error.content"
        class="mb-3"/>

      <FormUploadFile 
        :accepted_types="'(.jpg, .jpeg, .png)'"
        :accept="'.jpeg,.png,.jpg'"
        :accepted_types_list="['image/png', 'image/jpeg', 'image/jpg']"
        :multiple="true"
        @acceptFile="acceptFile"
        class="mb-3"/>
      
      <div class="mb-5">
        <v-chip 
          v-for="(item, i) in attachments"
          class="ma-1"
          color="primary"
          :key="i"
          small
          outlined
          close
          @click:close="remove(i)"
          >
          <v-icon small left>
            mdi-image
          </v-icon>
          {{ item.name }}
        </v-chip>
      </div>
      <div class="d-flex flex-column align-center">
        <span v-if="error" class="red--text mb-3">{{ error.message }}</span>
        <v-btn large block color="primary" class="body-1 font-weight-bold poppins" @click="submit" :loading="loading">
          Submit
        </v-btn>
      </div>
    </v-form>
    <SuccessDialog :dialog="dialog" :title="title" :message="message" :icon="icon" />
  </v-card>
</template>

<script>
import { mapActions, mapMutations, mapState } from 'vuex'
import SuccessDialog from '@/constants/reusable/SuccessDialog.vue';

export default {
  computed: {
    ...mapState({
        error: (state) => state.errors,
    }),
  },
  data: () => ({
      categories: [
          { text: "Platform", value: "platform" },
          { text: "Content", value: "content" },
          { text: "Assessment", value: "assessment" },
          { text: "Others", value: "others" },
      ],
      form: {
        email: "",
        concern_category: "",
        content: "",
      },
      attachments: [],
      dialog: false,
      loading: false,
      icon: true,
      title: "Ticket submitted!",
      message: "Thank you for submitting your concern with us. Track the status of your ticket in My Tickets.",
  }),
  methods: {
    ...mapActions('usr', ['getTicketsAction', 'submitTicketAction']),

    async submit() {
      if (this.$refs.form.validate()) {
        const params = new FormData()
        this.loading = true
        params.append('email', this.form.email)
        params.append('concern_category', this.form.concern_category)
        params.append('content', this.form.content)
        this.attachments.forEach(item => {
          params.append('file_attachment[]', item)
        })

        this.submitTicketAction(params).then(() => {
          this.dialog = true
          this.$refs.form.reset()
          this.attachments = []
          setInterval(() => {
            this.dialog = false
          }, 2000);
        }).finally(() => {
          this.loading = false
        })
      }
    },
    
    acceptFile(e) {
      this.attachments.push(e)
    },
    
    remove(i){
      this.attachments.splice(i, 1)
    },
  },
  components: { SuccessDialog }
}
</script>