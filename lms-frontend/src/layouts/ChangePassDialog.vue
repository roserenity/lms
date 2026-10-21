<template>
  <v-dialog persistent v-model="dialog" max-width="380">
    <v-card class="change-pass-modal border" v-if="!changed">
      <v-card-text class="px-7">
        <div class="pt-14">
          <h2 class="primary--text poppins">Welcome Aboard!</h2>
          <FormLabel :label="'Let’s secure your account by creating a new password.'"/>
          <h3 class="roboto secondary-1--text mt-2 font-weight-thin">
            
          </h3>
        </div>
        <v-form class="py-3" @submit.prevent="change" ref="changeForm">
          <FormLabel :label="'New Password *'"/>
          <label class="poppins fw500 f12 ml-1"></label>
          <v-text-field
            outlined
            dense
            :type="!show ? 'password' : 'text'"
            class="general-custom-field f14 roboto fw500 secondary-1--text"
            hide-details="auto"
            :append-icon="!show ? 'mdi-eye-outline' : 'mdi-eye-off-outline'"
            @click:append="show = !show"
            v-model="form.password"
            :error-messages="errs && errs.password"
            autocomplete="off"
          />
          <PasswordRules :password="form.password" class="mb-3"/>
          <FormLabel :label="'Confirm New Password *'"/>
          <v-text-field
            outlined
            dense
            :type="!show1 ? 'password' : 'text'"
            class="general-custom-field f14 roboto fw500 secondary-1--text mb-7"
            hide-details="auto"
            :append-icon="!show1 ? 'mdi-eye-outline' : 'mdi-eye-off-outline'"
            @click:append="show1 = !show1"
            v-model="form.password_confirmation"
            :error-messages="errs && errs.password_confirmation"
            autocomplete="off"
          />
          <v-btn
            :loading="loading"
            type="submit"
            large
            color="primary"
            block
            class="poppins text-capitalize f14"
            style="border-radius: 5px"
          >
            Save
          </v-btn>
        </v-form>
      </v-card-text>
    </v-card>
    <v-card class="success-pass-modal border" v-else>
      <v-card-text class="">
        <div class="text-center pt-16">
          <v-icon color="success" size="80">mdi-check-circle</v-icon>
          <h2 class="primary--text poppins fw600 my-5">
            Your password has been updated!
          </h2>
          <div class="secondary-1--text roboto fw500 f14">
            You can now explore your dashboard and view courses.
          </div>
          <v-btn
            @click="done"
            width="200"
            color="primary"
            large
            class="f16 poppins fw600 my-5 text-capitalize"
          >
            Dashboard
          </v-btn>
        </div>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>

<script>
import { mapActions, mapState } from "vuex";
import PasswordRules from "@/components/reusable/forms/PasswordRules.vue";

export default {
  components: {
    PasswordRules
  },
  props: ["dialog"],
  data: () => ({
    show: false,
    show1: false,
    errs: [],
    form: {
      password: "",
      password_confirmation: "",
    },
    changed: false,
    loading: false,
  }),
  computed: {
    ...mapState({
      errors: (state) => state.errors,
    }),
  },
  methods: {
    ...mapActions(["changePasswordAction"]),

    change() {
      this.loading = true;
      this.errs = [];
      this.changePasswordAction(this.form).then(() => {
        this.loading = false;
        // if (this.$errors()) return (this.errs = this.errors);

        this.changed = true;
        // this.errs = [];
        this.$refs.changeForm.reset();
      }).catch(e => {
        this.errs = e
        this.loading = false;
      })
    },

    done() {
      this.$emit("close");
      this.changed = false;
    },
  },
};
</script>
