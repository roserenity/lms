<template>
  <v-card class="custom-border border fade">
    <SkeletonLoaderCard height="85vh" v-if="loading"/>
    <v-sheet v-else min-height="85vh" max-height="85vh" class="fade overflow-y-auto scroller d-flex align-stretch flex-column border">
      <div class="ma-5">
        <h3 class="poppins">Account Settings</h3>
        <h4 class="poppins secondary-1--text mt-10 mx-5">Profile Information</h4>
        <div class="mx-5 poppins f14 primary--text fw500" v-if="form.email !== user.email">
          Changing your email address will require verification. You will be logged out, and a verification link will be sent to your new email.
        </div>
      </div>
      <PhotoUpload :modal="photoUploadDialog" @close="photoUploadDialog = false" :type="'profile'" :role="'usr'"/>
      <v-form  ref="form" class="ma-5">
        <v-row no-gutters>
          <v-col cols="12" md="3" class="d-flex flex-column align-center justify-center">
            <div class="group-avatar-uploader d-flex flex-column align-center justify-center"
            style="width: 150px; height: 150px">
              <a href="#" class="text-decoration-none">
                <v-avatar  size="140" @click="photoUploadDialog = true"> 
                  <v-img :src="form.image ? form.image.url : require('@/assets/default-photo.png')" v-on:error="form.image = null" />
                </v-avatar>
              </a>
            </div>
            <v-btn color="primary" dense small @click="photoUploadDialog = true" class="fw500 f12 my-2">
              Choose Profile Photo
            </v-btn>
            <!-- <a href="#" class="text-decoration-none error--text fw500 f12 mb-3">Remove</a> -->
          </v-col>
          <v-col cols="12" md="8">
            <v-row dense>
              <v-col cols="12" md="3">
                <FormTextFieldRequired
                  :label="'FIRST NAME *'"
                  :value.sync="form.first_name"
                  :error="errors.first_name"/>
              </v-col>
              <v-col cols="12" md="3">
                <FormTextFieldOptional
                  :label="'MIDDLE NAME'"
                  :value.sync="form.middle_name" />
              </v-col>
              <v-col cols="12" md="3">
                <FormTextFieldRequired
                  :label="'LAST NAME *'"
                  :value.sync="form.last_name"
                  :error="errors.last_name"/>
              </v-col>
              <v-col cols="12" md="3">
                <FormTextFieldOptional
                  :label="'SUFFIX'"
                  :value.sync="form.suffix" />
              </v-col>
            </v-row>
            <v-row dense>
              <v-col cols="12" md="3">
                <FormSelectObject 
                  :label="'GENDER'"
                  :items="items"
                  :value.sync="form.gender"
                />
              </v-col>
              <v-col cols="12" md="4">
                <FormTextFieldRequired
                  :label="'EMAIL ADDRESS *'"
                  :value.sync="form.email"
                  :error="errors.email"/>
              </v-col>
            </v-row>
          </v-col>
        </v-row>
        <section class="mx-5" v-if="form.role === 'USER'">
          <section v-for="(tbl,  j) in registration_tables" :key="j" >
            <v-divider class="my-5"></v-divider>
            <h4 class="poppins secondary-1--text my-5">{{ tbl.title }}</h4>
            <div v-for="(column, i) in tbl.custom_registration_fields" :key="i" class="my-1 d-flex flex-column">
              <!-- <label class="roboto secondary-2--text f12">{{column.input_field_label}} <span v-if="column.is_required">*</span></label> -->
              <!-- <input
                v-if="column.input_field_type === 'file'"
                :type="column.input_field_type"
                dense
                disabled
                class="roboto f14 fw500 secondary-2--text"
                :hide-details="type=='preview'"
              /> -->
              <FormSelectArray
                v-if="column.input_field_type === 'dropdown'"
                :label="(`${column.input_field_label}${column.is_required === 1 ? ' *' : ''}`).toLocaleUpperCase()"
                :items="column.data"
                :multiple="Boolean(column.is_multiple)"
                :value.sync="form.data[tbl.title][column.input_field_name]"
                :required="column.is_required === 1"
              />
              <div v-else-if="column.input_field_type === 'date' && column.is_required">
                <label class="caption">{{ (`${column.input_field_label}${column.is_required === 1 ? ' *' : ''}`).toLocaleUpperCase() }}</label>
                <FormDatePickerRequired
                    :val="form.data[tbl.title][column.input_field_name]"
                    @apply="(e) => {
                        form.data[tbl.title][column.input_field_name] = e
                    }"/>
              </div>
              <div v-else-if="column.input_field_type === 'date'">
                <label class="caption">{{ (`${column.input_field_label}${column.is_required === 1 ? ' *' : ''}`).toLocaleUpperCase() }}</label>
                <FormDatePicker 
                    :val="form.data[tbl.title][column.input_field_name]"
                    @apply="(e) => {
                        form.data[tbl.title][column.input_field_name] = e
                    }"/>
              </div>
              <FormTextFieldRequired
                v-else-if="column.input_field_type !== 'dropdown' && column.is_required"
                :label="(`${column.input_field_label}${column.is_required === 1 ? ' *' : ''}`).toLocaleUpperCase()"
                :value.sync="form.data[tbl.title][column.input_field_name]"
                :text="column.input_field_type"/>
              <FormTextFieldOptional
                v-else-if="column.input_field_type !== 'dropdown'"
                :label="(`${column.input_field_label}${column.is_required === 1 ? ' *' : ''}`).toLocaleUpperCase()"
                :value.sync="form.data[tbl.title][column.input_field_name]"
                :text="column.input_field_type"/>
            </div>
          </section>
        </section>
      </v-form>
      <v-spacer />
      <v-divider class="mt-auto"/>
      <div class="ma-5">
        <v-btn @click="update" color="primary" class="poppins text-capitalize fw600" :loading="updateLoading">
          Save changes
        </v-btn>
        <v-btn @click="resetForm()" color="secondary-4" text class="poppins text-capitalize fw600">Cancel</v-btn>
      </div>
    </v-sheet>
    
    <v-dialog v-model="dialog" width="auto">
      <v-card class="pa-5 d-flex align-center justify-center">
        <circular class="mr-2" />
        <h3 class="text-center poppins">Logging you out...</h3>
      </v-card>
    </v-dialog>
  </v-card>
</template>

<script>
import PhotoUpload from '@/components/main/PhotoUpload.vue'
import { mapState, mapMutations, mapActions } from 'vuex'
import SkeletonLoaderCard from '@/components/reusable/SkeletonLoaderCard.vue'

export default {
  components: { 
    PhotoUpload,
    SkeletonLoaderCard
  },
  data: () => ({
    items: [
      {text: 'Male', value: 'M'},
      {text: 'Female', value: 'F'},
    ],
    image: null,
    dialog: false,
    errors: [],
    loading: false,
    updateLoading: false,
    file: null,
    photoUploadDialog: false,
    form: null,
    emailChanged: false
  }),
  computed: {
    ...mapState({
      user: (state) => state.user,
      registration_tables: (state) => state.registration_tables,
      errors: (state) => state.errors,
    }),
  },

  mounted() {
    this.loading = true
    this.getRegistrationTables().then(res => {
      let _data = {}
      this.registration_tables.forEach(tbl => {
        tbl.custom_registration_fields.forEach(column => {
          if (!_data[tbl.title]) {
            _data[tbl.title] = {};
          }
          _data[tbl.title][column.input_field_name] = '';
        })
      })

      this.user.data = Object.assign({}, { ..._data, ...this.user.data });

      this.form = JSON.parse(JSON.stringify(this.user));
      this.form.data = this.replaceNulls(this.form.data);

    }).finally(() => {
      this.loading = false
    })

  },

  methods: {
    
    ...mapMutations(['alertMutation', 'userMutation', 'signoutMutation']),
    ...mapActions(['getRegistrationTables', 'updateProfileDataAction']),

    replaceNulls(obj) {
      for (let key in obj) {
        if (!obj.hasOwnProperty(key)) continue;

        if (obj[key] === null) {
          obj[key] = '';
        } else if (typeof obj[key] === 'object' && obj[key] !== null) {
          this.replaceNulls(obj[key]);
        }
      }
      return obj;
    },

    resetForm(){
      this.form = JSON.parse(JSON.stringify(this.user));
    },
    
    update() {
      this.updateLoading = true

      // save details

      if(this.$refs.form.validate()) {
        this.updateProfileDataAction(this.form).then(res => {
          setTimeout(() => {
            this.updateLoading = false
            this.alertMutation({
              show: true,
              text: 'Profile has been successfully updated!',
              type: "success"
            });
            this.userMutation(this.form)
            if(this.emailChanged) {
              this.dialog = true
              this.emailChanged = false
              setTimeout(() => {
                this.signoutMutation()
              }, 2000)
            }
          }, 2000);
        })
        .catch(errs => {
          this.updateLoading = false
          this.errors = errs.response.data.errors
        })
      } else {
        this.updateLoading = false
      }
    }
  },
  watch: {
      form: {
          handler(newForm, oldForm) {
              if(this.form.email !== this.user.email) {
                this.emailChanged = true
              } 
          },
          deep: true
      },
  }
}
</script>