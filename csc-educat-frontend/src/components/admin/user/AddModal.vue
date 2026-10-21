<template>
  <v-dialog v-model="dialog" persistent max-width="550">
    <v-card >
      <v-card-text class="poppins f18 fw600 d-flex align-center pt-3">
        <div class="primary--text">
          Upload User
        </div>
        <v-spacer/>
        <v-btn icon @click="cancel">
          <v-icon>mdi-close</v-icon>
        </v-btn>
      </v-card-text>
      <v-card-text>
        <Bulk v-if="!manual" :manual="manual" @onbulk="onbulk" :dialog="dialog"/>
        <div class="mx-5" v-if="!manual">
          <div class="" v-for="(err, i) in errorList" :key="i">
            <div class="d-flex align-start">
              <v-icon color="danger-1" size="18" class="mr-2">mdi-alert-circle</v-icon>
              <div class="f12">{{err[0]}}</div>
            </div>
          </div>
          <div class="d-flex flex-wrap justify-space-around align-center">
            <div class="">
              <v-btn text color="primary" small target="_blank" :href="`${base_path.replace('/v1', '')}/import_users_template.csv`" class="poppins text-capitalize fw600 cursor-pointer" download>
                <v-icon left size="24">mdi-download</v-icon>
                Bulk-upload template
              </v-btn>
            </div>
            <div class="poppins f14 fw600">
              or manually <span @click="manual=true" class="text-decoration-underline primary--text cursor-pointer">register user</span>
            </div>
          </div>
        </div>
        <v-alert
          v-if="errorList.length !== 0 && manual"
          dense
          type="error"
          class="roboto f12"
          id="error-alert"
          transition="scale-transition"
          dismissible
        >
          There are still some items left to correct.
        </v-alert>
        <v-sheet v-if="manual" height="450" class="overflow-y-auto overflow-hidden">
          <v-form ref="form">
            <v-slide-y-transition v-for="(input, i) in inputs" :key="i" >
              <ManualInput 
                :i="i"
                :input="input"
                @removeInput="removeInput"
                :error="errorList.length === 0 ? null : errorList[i]"
              />
            </v-slide-y-transition >
          </v-form>
        </v-sheet>
      </v-card-text>
      <v-divider/>
      <v-card-actions class="d-flex justify-space-between align-center py-3 px-5">
        <v-btn outlined color="primary" class="text-capitalize poppins fw600" v-if="manual" 
        @click="addInput">
          <v-icon left>mdi-plus</v-icon>
          Add
        </v-btn>
        <v-spacer/>
        <div class="">
          <!-- <v-btn text class="text-capitalize poppins fw600" @click="cancel">
            Cancel
          </v-btn> -->
          <ButtonPrimary 
            @click="submit"
            :label="'Upload'"
            :loading="saving"
            :disabled="!manual ? !file : false"
          />
        </div>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<style>
.cursor-pointer {
  cursor: pointer;
}
</style>

<script>
import { mapActions, mapState, mapMutations } from 'vuex'
import Bulk from './Bulk.vue'
import ManualInput from './Manual.vue'

export default {
  props: ['dialog'],
  components: { 
    Bulk,
    ManualInput,
  },
  computed: {
    ...mapState({
      errors: (state) => state.errors,
      tenant: (state) => state.tenant
    }),
  },
  data: () => ({
    base_path: `${process.env.VUE_APP_API}`,
    manual: false,
    inputs: [{ last_name: '', first_name: '', middle_name: '', suffix: '', role: 'user', email: '', class_category_id: [], course_id:[], index: 0}],
    saving: false,
    form: new FormData(),
    file: null,
    errorList: [],
    userLength: 0,
    disable: false
  }),
  mounted() {
    this.getClassesAction().catch(() => {
      this.alertMutation({
        show: true,
        text: 'Something went wrong',
        type: "error"
      })
    })
  },
  methods: {
    ...mapMutations(['alertMutation', 'errorsMutation']),

    ...mapActions('admin', [
      'addUserAction',
      'importUsersAction',
      'getClassesAction',
      'getCoursesAction',
      'getUsersAction'
    ]),

    addInput() {
      this.inputs.push({ last_name: '', first_name: '', middle_name: '', suffix: '', role: 'user', email: '', class_category_id: [], course_id:[], index: this.inputs.length === 0 ? 0 : this.inputs[this.inputs.length-1].index + 1 })
      if(this.errorList.length > 0) {
        this.errorList = []
      }
    },

    removeInput(e) {
      this.inputs.splice(e, 1);
      if(this.inputs.length === 0) {
        this.addInput()
      }

      if(this.errorList.length > 0) {
        this.errorList = []
      }
    },
    submit() {
      this.errorList = []
      if(this.manual) return this.uploadManual()

      else {
        this.saving = true
        this.importUsersAction(this.form).then(() => {
          this.errorList = []
          this.reset()
          this.$emit('close')
          this.$emit('saveUser')
          this.saving = false
        }).catch(e => {
          this.saving = false
        })
      }
    },

    uploadManual() {
      this.userLength = this.inputs.length
      let _inputs = []
      this.errorList = []
      if(this.$refs.form.validate()) {
        this.inputs.forEach((item, i) => {  
          this.saving = true
          this.addUserAction(item).then(()=>{

             if(this.inputs.length === 0) {
              setTimeout(() => {
                this.reset()
                this.$emit('close')
                this.alertMutation({
                  show: true,
                  text: 'Importing Successful',
                  type: "success"
                });
                this.saving = false
              }, 1000);
            }
            this.saving = false
          }).catch((e) => {
            _inputs.push(item)
            this.errorList.push(e)
            this.$vuetify.goTo('#error-alert', { offset: 0})
          })
        })

        this.saving = false
        this.inputs = _inputs
        this.getUsersAction()
      }
    },

    onbulk(e) {
      this.reset()
      this.form.set('file', e)
      this.file = e

      if(e) {
        this.disable = false
      } else {
        this.disable = true
      }
    },

    cancel() {
      this.reset()
      this.$emit('close')
    },
    reset() {
      this.errorList = []
      this.manual= false,
      this.inputs = [{ last_name: '', first_name: '', middle_name: '', suffix: '',  role: 'user', email: '', class_category_id: [], course_id:[], index: 0}]
    }
  },

  watch: {
    // errors(val) {
    //  if(this.manual && val.length !== 0) {
    //    if(this.errorList.findIndex(item => item.index === val.index) == -1){
    //      this.errorList.push(val)
    //   }
    //  } else if(!this.manual && val.length !== 0) {
    //    this.errorList = val
    //  }
    // },
    manual() {
      this.errorList = []
    },
    
    dialog(val) {
      if(val){
        this.errorsMutation([])
        this.file = null
        this.form.set('file', null)
      }
    }
  }
}
</script>
