<template>
    <v-dialog persistent v-model="dialog" max-width="530">
      <v-card rounded="lg">
        <v-card-text>
          <v-btn icon absolute right class="mt-3" @click="close">
            <v-icon>mdi-close</v-icon>
          </v-btn>
          <div class="text-center py-5">
            <v-icon size="90" color="danger-1">mdi-alert-circle</v-icon>
            <h3 class="poppins black--text mt-5">
              {{ `Are you sure you want to cancel this transaction ?` }}
            </h3>
            <div class="secondary--text roboto f14 col-lg-9 mx-auto">
              {{
                `This action cannot be undone. All of the data under this will be permanently deleted.`
              }}
            </div>
            <div class="roboto f4 fw600 mb-5">Type ‘cancel’ to confirm</div>
            <v-form @submit.prevent="detach">
              <v-text-field
                v-model="text"
                class="col-lg-9 mx-auto general-custom-field roboto f14 secondary-1--text fw500 centered-input"
                outlined
                dense
                autofocus
                v-on:input="checkInput($event)"
              />
              <div v-if="err" class="error--text text--center poppins f12 fw500 mb-3">
                Something went wrong
              </div>
              <v-btn
                v-if="!deleting"
                type="submit"
                :color="remove ? 'danger-1' : '#FFA0A0'"
                class="text-capitalize poppins f12 white--text"
                elevation="0"
              >
                Proceed
              </v-btn>
              <v-btn
                v-else
                color="danger-1"
                dark
                class="text-capitalize poppins f12"
                elevation="0"
              >
                Cancelling...
              </v-btn>
            </v-form>
          </div>
        </v-card-text>
      </v-card>
    </v-dialog>
  </template>
  
  <script>
  import { mapActions, mapMutations } from "vuex";
  export default {
    props: ["dialog", "item"],
    data: () => ({
      remove: false,
      text: "",
      deleting: false,
    }),
    methods: {
      ...mapMutations(["alertMutation"]),
  
      ...mapActions("usr", [
        "courseCancelTransaction",
      ]),
  
      checkInput(e) {
        this.remove = false;
        if (e === "cancel") this.remove = true;
      },
  
      detach() {
        if (this.text === "cancel") {
          this.deleting = true;
          this.err = false
          this.courseCancelTransaction(this.item).then(() => {
            this.alertMutation({
              show: true,
              text: `Transaction Successfully Cancelled`,
              type: "success"
            });

            this.text = "";
            this.$emit("close");
          }).catch(()=> {
            this.err = true
          }).finally(() => {
            this.deleting = false
          })
        }
      },
  
      close() {
        this.text = "";
        this.$emit("close");
      },
    },
  };
  </script>
  
  <style scoped>
  .centered-input >>> input {
    text-align: center;
  }
  </style>
  