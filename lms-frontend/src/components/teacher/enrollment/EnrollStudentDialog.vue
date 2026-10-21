<template>
  <section>
    <v-dialog persistent v-model="success_dialog" max-width="400">
      <v-card class="pa-5">
        <div class="text-center primary--text poppins fw600 f18">Please wait for the approval of the Admin</div>
        <v-card-subtitle>
          <div class="roboto mt-1 f12 text-center">Once approved, an email notification will be sent to the user. You can also track the status on the 'Enrollment History' tab.</div>
        </v-card-subtitle>
        <div class="d-flex align-center justify-space-between">
          <v-btn
            @click="success_dialog = false, dialog = true"
            color="primary"
            class="text-capitalize poppins fw600 f12 small"
            dense
            outlined
          >
            Enroll Another
          </v-btn>
          <v-btn
              @click="() => {
                success_dialog = false
                $emit('close')
              }"
              color="primary"
              class="text-capitalize poppins fw600 f12 small"
              dense
            >
              Okay
            </v-btn>
        </div>
        <!-- <v-form ref="form">
            <v-card-text>
              <label class="poppins fw500 f12 secondary-1--text">EMAIL ADDRESS* </label>
              <v-text-field v-model="email" outlined dense hide-details="auto" type="email" :error-messages="errors.email"
                  class="general-custom-field roboto f14 secondary-1--text fw500"
                  required
                  :rules="[ v => /.+@.+\..+/.test(v) || 'E-mail must be valid', v => !!v || 'E-mail is required']"/>
            </v-card-text>
            <v-card-text>
              <label class="poppins fw500 f12 secondary-1--text pt-5">ADD COURSES*</label>
              <v-select 
              outlined 
              dense 
              class="general-custom-field roboto f12 secondary-1--text fw500"
              :items="courses"
              v-model="course_id"
              item-text="title"
              item-value="id"
              hide-details="auto"
              :error-messages="errors.course_id"
              multiple
              >
              </v-select>
            </v-card-text>
            <v-card-text class="d-flex flex-column">
              <div class="bulk-border pa-5" @dragover="dragover" @dragleave="dragleave" @drop="drop">
                <div class="text-center">
                  <v-icon color="rgba(63, 93, 208, 0.12)" size="68">mdi-cloud-upload-outline</v-icon>
                  <div class="secondary--text"><a href="#" @click="$refs.file.click()" class="primary--text text-decoration-none">Browse</a> or Drag file to upload</div>
                </div>
              </div>
              <section>
                <v-chip 
                    v-if="image"
                    class="ma-1"
                    color="primary"
                    small
                    outlined
                    close
                    @click:close="remove(i)"
                    >
                    <v-icon small left>
                        mdi-image
                    </v-icon>
                    {{ image.name }}
                </v-chip>

              </section>
                <input class="d-none" type="file" ref="file" accept=".jpeg,.png,.jpg" @change="addAttachment"/>
                <span class="danger--text f10" v-if="errors && errors.image">{{ errors.image[0] }}</span>
            </v-card-text>
            <v-card-text>
            <label class="poppins fw500 f12 secondary-1--text">OR NUMBER</label>
            <v-text-field v-model="or_number" outlined dense hide-details="auto" type="email" :error-messages="errors.or_number"
                class="general-custom-field roboto f14 secondary-1--text fw500"/>
            </v-card-text>
        </v-form>
        <v-card-actions class="">
          <v-spacer />
          <v-btn text class="poppins fw600 text-capitalize" @click="$emit('close')"
            >Cancel</v-btn
          >
          <v-btn
            v-if="!saving"
            class="poppins fw500 text-capitalize"
            color="primary"
            @click="submit"
          >
            Submit
          </v-btn>
          <v-btn v-else class="poppins fw500 text-capitalize" color="primary">
            Submitting...
          </v-btn>
        </v-card-actions> -->
      </v-card>
    </v-dialog>
    <v-dialog persistent v-model="dialog" max-width="500">
      <v-card>
        <v-card-title class="d-flex align-center justify-space-between">
          <h4 class="primary--text poppins fw600">Enroll a Student</h4>
          <v-btn icon @click="$emit('close')">
            <v-icon size="18">mdi-close</v-icon>
          </v-btn>
        </v-card-title>
        <v-form ref="form" class="px-3 pb-3">
            <v-card-text>
              <FormTextFieldRequired
                class="f12"
                :label="'EMAIL ADDRESS *'"
                :error="errors.email"
                :value.sync="email"
              />
              <!-- <label class="poppins fw500 f12 secondary-1--text"> </label>
              <v-text-field v-model="email" outlined dense hide-details="auto" type="email" :error-messages="errors.email"
                  class="general-custom-field roboto f14 secondary-1--text fw500"
                  required
                  :rules="[ v => /.+@.+\..+/.test(v) || 'E-mail must be valid', v => !!v || 'E-mail is required']"/> -->

                  <label class="caption">COURSE *</label>
                  <v-select 
                    outlined 
                    dense 
                    :items="courses"
                    v-model="coursesForm.course_id"
                    item-text="title"
                    item-value="id"
                    hide-details="auto"
                    :rules="[ v => !!v || 'This is required']"
                    :error-messages="errors.course_id"
                    required
                    class="general-custom-field mb-1 f12"
                    :loading="loading"
                  >
                    <template slot="item" slot-scope="data">
                      <v-list-item-content>
                        <v-list-item-title class="roboto f14 fw500" >
                          <div class="roboto f10">
                            {{  data.item.type_of_learning }}
                            <span v-if="data.item.available_until">
                                &nbsp; | &nbsp; 
                                <span v-if="data.item.available_until.start_date"> {{ $dateFormat.mmDDyy(data.item.available_until.start_date) }} </span>
                                <span v-if="data.item.available_until.end_date"> until {{ $dateFormat.mmDDyy(data.item.available_until.end_date) }} </span>
                            </span>
                          </div>
                          {{  data.item.title }}
                        </v-list-item-title>
                      </v-list-item-content>
                    </template>
                    <template slot="selection" slot-scope="data" >
                      <div class="roboto f14 fw500 my-1">
                        <div class="roboto f10">
                          {{  data.item.type_of_learning }}
                        </div>
                        {{  data.item.title }}
                      </div>
                    </template>
                  </v-select>

                  <FormSelectObject
                      :label="'TYPE OF PAYMENT'"
                      :items="type_of_payment"
                      :value.sync="coursesForm.type_of_payment"
                      class="mb-3"
                  />

                  <UploadCommitmentLetter 
                      v-if="['commitment_letter'].includes(coursesForm.type_of_payment)"
                      :form="true"
                      @setCommitmentLetter="(e) => commitmentLetter = e"
                      :error="errors.commitment_letter && errors.commitment_letter[0]"
                      class="mb-3"
                      :info="false"
                  />

                  <FormSelectObject
                      :label="'TYPE OF PARTICIPANT'"
                      :items="type_of_participant"
                      :value.sync="coursesForm.type_of_participant"
                      class="mb-3"
                  />

                  <UploadPrimeRequirements
                      v-if="['regular_payee_hrm'].includes(coursesForm.type_of_participant)"
                      :form="true"
                      @setEndorsementLetter="(e) => endorsementLetter = e"
                      @setPrimeResolution="(e) => primeResolution = e"
                      :error_endorsement_letter="errors.endorsement_letter && errors.endorsement_letter[0]"
                      :error_prime_resolution="errors.prime_resolution && errors.prime_resolution[0]"
                  />

                  <!-- <div class="d-flex align-center">
                      <v-checkbox v-model="own_account" />
                      <label class="caption">Is the learner's agency responsible for the payment?</label>
                  </div>

                  <UploadNomination
                      v-if="own_account"
                      @setNominationForm="(e) => nominationForm = e"
                      :form="true"
                      :error_nomination_form="errors.nomination_form && errors.nomination_form[0]"
                      :error_proof_of_payment="errors.proof_of_payment && errors.proof_of_payment[0]"
                  /> -->
                  
              <!-- <label class="caption">UPLOAD RECEIPT *</label>
              <FormUploadFile 
                :accepted_types="'(.jpeg, .png, .jpg, .pdf)'"
                :accept="'.jpeg,.png,.jpg,.pdf'"
                :accepted_types_list="['image/png', 'image/jpeg', 'image/jpg', 'application/pdf']"
                :multiple="false"
                @acceptFile="addAttachment"/>
                <v-chip 
                    v-if="image"
                    color="primary"
                    small
                    outlined
                    close
                    @click:close="remove(i)"
                    >
                    <v-icon small left>
                        mdi-image
                    </v-icon>
                    {{ image.name }}
                </v-chip>
                <div class="error--text f11 px-5" v-if="errors && errors.image">{{ errors.image[0] }}</div>
                <FormTextFieldRequired
                  class="mt-1"
                  :label="'OR NUMBER'"
                  :error="errors.or_number"
                  :value.sync="or_number"
                /> -->
            </v-card-text>
            <alert 
              v-if="msg.show"
              :show = "msg.show"
              :text = "msg.text"
              :type = "msg.type"
            />
        </v-form>
        <v-card-actions class="">
          <v-spacer />
          <v-btn text class="poppins fw600 text-capitalize" @click="$emit('close')"
            >Cancel</v-btn
          >
          <v-btn
            :loading="saving"
            class="poppins fw500 text-capitalize"
            color="primary"
            @click="submit"
          >
            Submit
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </section>
</template>

<script>
import UploadNomination from '@/components/user/course/tocart/UploadNomination.vue';
import UploadPrimeRequirements from '@/components/user/course/tocart/UploadPrimeRequirements.vue';
import UploadCommitmentLetter from '@/components/user/course/tocart/UploadCommitmentLetter.vue';
import { mapActions, mapState } from "vuex";

export default {
  components: { UploadPrimeRequirements, UploadNomination, UploadCommitmentLetter },
  props: ["dialog"],
  data: () => ({
    email: null,
    or_number: null,
    coursesForm : {
        course_id: null,
        type_of_payment: null,
        type_of_participant: null
    },
    image: null,
    saving: false,
    success_dialog: false,
    courses: [],
    errors: {},
    search: '',
    loading: false,
    msg: {
      show: false,
      text: '',
      type: ''
    },
    type_of_payment: [
        { text: '-', value: null },
        { text: 'CASH DEPOSIT', value: 'cash_deposit' },
        // { text: 'CASH-CASHIER', value: 'cash-cashier' },
        { text: 'CHECK DEPOSIT', value: 'check_deposit' },
        // { text: 'CHECK-CASHIER', value: 'check-cashier' },
        { text: 'FUND TRANSFER', value: 'fund_transfer' },
        { text: 'COMMITMENT LETTER (CL)', value: 'commitment_letter' },
        // { text: 'LDDAP-ADA', value: 'lddap_ada' },
    ], 
    type_of_participant: [
        { text: '-', value: null },
        { text: 'INTERNAL EMPLOYEE', value: 'internal_employee' },
        { text: 'REGULAR PAYEE', value: 'regular_payee' },
        { text: 'REGULAR PAYEE (Partner Awardee)', value: 'regular_payee_hrm' },
    ],
    own_account: false,
    primeResolution: null, 
    endorsementLetter: null,
    nominationForm: null,
    commitmentLetter: null
  }),
  methods: {
    ...mapActions("instructor", ["enrollStudentAction", "getEnrollStudentAction", "getAllCoursesAction"]),
    
    submit() {
      this.errors = {}
      this.msg = {
        show: false,
        text: "",
        type: ""
      }

      if (this.$refs.form.validate()) {
          this.saving = true

          const params = new FormData()
          params.append('_method', "POST")
          params.append('email', this.email)
          params.append(`course_id`, this.coursesForm.course_id)
          params.append('type_of_payment', this.coursesForm.type_of_payment)
          params.append('type_of_participant', this.coursesForm.type_of_participant ?? '')

          if(this.primeResolution) {
            params.append('prime_resolution', this.primeResolution)
            }

            if(this.endorsementLetter) {
              params.append('endorsement_letter', this.endorsementLetter)
            }
            
            if(this.nominationForm) {
              params.append('nomination_form', this.nominationForm)
            }

            if(this.commitmentLetter) {
              params.append('commitment_letter', this.commitmentLetter)
            }

          // this.course_id.forEach((item, i) => {
          //     params.append(`course_id[${i}]`, item)
          // })

          // this.image.forEach((item, i) => {
          //     params.append(`image[${i}]`, item)
          // })

          // params.append(`image`, this.image)
          // params.append('official_receipt_no', this.or_number)

          this.enrollStudentAction(params).then(res => {
            this.dialog = false
            this.success_dialog = true
            this.resetForm()
            this.saving = false
          }).catch(err => {
            console.log(err)
            if(err.errors) {
              this.errors = err.errors
            } else if(err.data.length === 0) {
              this.msg = {
                show: true,
                text: err.message,
                type: "error"
              }
            }
          }).finally(() => {
            this.saving = false
          })
        }
      },
      resetForm() {
        this.$refs.form.reset()
        this.image = null
      },
      addAttachment(e) {
        // this.image.push(this.$refs.file.files[0])
        this.image = e
      },
      dragover(e) {
        e.preventDefault()
        e.currentTarget.classList.add('bulk-border-drag');
      },
      dragleave(e) {
        e.preventDefault()
        e.currentTarget.classList.remove('bulk-border-drag');
      },
      drop(e) {
        e.preventDefault();
        this.$refs.file.files = e.dataTransfer.files;
        this.accept()
        e.currentTarget.classList.remove('bulk-border-drag');
      },
      accept() {
        this.image = this.$refs.file.files[0]
      },
      remove(i = 0){
        // this.image.splice(i, 1)
        this.image = null
      }
    },
    watch: {
      // error(val) {
      //   if(Object.keys(val).length) {
      //     this.errors = val
      //   }
      // },
      dialog(val) {

        if(val) {
          this.loading = true
          this.getAllCoursesAction().then((res) => {
            this.courses = res
          }).finally(() => {
            this.loading = false
          })
        }

        this.image = null
        this.$refs.file.files = []
        this.resetForm()
        this.errors = {}
        this.msg = {
          show: false,
          text: "",
          type: ""
        }
      }
    },
};
</script>