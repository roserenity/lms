<template>
    <v-card class="pa-5 box-shadow">
        <Dialog :dialog="success" :msg="'Your inquiry has been sent!'" @close="success = false"/>
        <h2 class="primary-font">
            Hi there! How can we help?
        </h2>
        <v-divider class="l-primary my-5" width="70%" />
        <v-form ref="form" :class="$vuetify.breakpoint.mobile ? 'pa-5': ''">
            <FormTextFieldRequired
                :label="'FULL NAME *'"
                :value.sync="form.name"
                :error="errs.name"
            />
            <FormTextFieldRequired
                :label="'EMAIL ADDRESS *'"
                :value.sync="form.email"
                :error="errs.email"
            />
            <FormTextFieldRequired
                :label="'SUBJECT *'"
                :value.sync="form.subject"
                :error="errs.subject"
            />
            <FormTextFieldRequired
                :label="'MESSAGE *'"
                :value.sync="form.message"
                :error="errs.message"
                class="mb-3"
            />
            <v-btn 
                large 
                block 
                :loading="loading"
                color="brand_gradient_bg white--text"
                style="border-radius: 10px" 
                @click="send">
                SUBMIT
            </v-btn>
        </v-form>
    </v-card>
</template>

<script>
import { mapActions } from 'vuex';
import Dialog from '@/components/landing/common/dialog.vue'
export default {
    name: 'ContactView',
    components: {
        Dialog
    },
    data: () => ({
        form: {
            name: null,
            email: null,
            subject: null,
            message: null,
        },
        loading: false,
        success: false,
        errs: [],
    }),
    methods: {
        ...mapActions(['sendContactUs']),

        send(){
            if(this.$refs.form.validate()){
                this.loading = true
                let _form = new FormData()

                _form.append("name", this.form.name)
                _form.append("email", this.form.email)
                _form.append("subject", this.form.subject)
                _form.append("message", this.form.message)
                this.sendContactUs(_form).then(() => {
                    this.loading = false
                    this.success = true
                    this.$refs.form.reset()
                }).catch(e => {
                    this.errs = e
                    this.loading = false
                    this.success = false
                })
            }
        }
    }
}
</script>