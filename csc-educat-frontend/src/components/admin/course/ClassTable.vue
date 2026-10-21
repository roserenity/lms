<template>
  <section >
    <section class="d-flex flex-wrap justify-space-between">
      <SearchBar :placeholder="'Search class'" @clear="$emit('clear')" :value.sync="search" @search="$emit('search', search)" class="mb-5" />
      <!-- <v-text-field
        solo
        dense
        flat
        class="col-xl-3 col-lg-4 custom-border mb-5 general-custom-field f14 poppins fw500"
        hide-details
        prepend-inner-icon="mdi-magnify"
        placeholder="Search class"
        v-model="search"
      /> -->
      <v-menu :value="shown" offset-y transition="slide-y-transition" 
        :close-on-content-click="false" 
        content-class="elevation-2">
        <template v-slot:activator="{ on, attrs }">
          <v-btn class="mb-3 text-capitalize poppins f12 ml-auto" 
            outlined 
            color="primary"
            v-bind="attrs"
            v-on="on"
            dense
          >
            <v-icon left>mdi-plus</v-icon>
            Add Class
          </v-btn>
        </template>
        <v-card >
          <v-card-text>
            <v-form ref="form">
              <label class="caption">GENERATE CLASS CODE *</label>
              <v-tooltip v-model="show" top >
                <template v-slot:activator="{ on, attrs }">
                  <v-btn small icon v-bind="attrs" v-on="on" >
                    <v-icon color="grey lighten-1" small>
                      mdi-information
                    </v-icon>
                  </v-btn>
                </template>
                <span>This is an auto generated and cannot be customized.</span>
              </v-tooltip>
              <div class="d-flex mb-1">
                <div class="flex-grow-1">
                  <FormTextFieldRequired
                    :value.sync="form.class_code"
                    :error="errs.class_code"
                    :readonly="true"
                  />
                </div>
                <div class="">
                  <v-btn color="primary" flat elevation="0" class="poppins fw500 f12" width="100" style="height: 40px;border-radius: 0px 5px 5px 0px; margin-left: -5px"
                  @click="random">Get Code</v-btn>
                </div>
              </div>
              <FormTextFieldRequired
                :label="'CREATE CLASS NAME *'"
                :value.sync="form.class_name"
                :error="errs.class_name"
              />
            </v-form>
          </v-card-text>
          <v-divider/>
          <v-card-actions>
            <v-btn :loading="saving" color="primary" block class="text-capitalize poppins" @click="generate">
              Generate
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-menu>
    </section>
    <section v-if="totalCount !== 0" class="text-right">
      <FormLabel :label="`${totalCount} result/s`"/>
    </section>
    <v-data-table
      :headers="headers"
      :items="classes"
      class="elevation-1 poppins"
      :loading="loading"
      hide-default-footer
      :items-per-page.sync="itemsPerPage"
    >
      <template v-slot:item.action="{ item }">
        <v-btn icon color="primary" @click="open(item)">
          <v-icon small>mdi-eye-outline</v-icon>
        </v-btn>
        <v-btn icon color="primary" @click="selectedClass = item, deletedialog=true">
          <v-icon small>mdi-trash-can-outline</v-icon>
        </v-btn>
      </template>
    </v-data-table>
    <FormPagination 
      :pageCount="pageCount" 
      :page="page"
      :paginate="paginate"
      @page="(e) => $emit('page', e)" 
      @paginate="(e) => $emit('paginate', e)"/>
    <!-- <v-simple-table class="mx-1 custom-border">
      <template v-slot:default>
        <thead>
          <tr style="">
            <th v-for="(item, i) in headers" :key="i" :class="[i==0 ? 'd-flex align-center' : 'text-center']" class="f12 poppins fw600 text-uppercase secondary-2--text">
              {{item}}
              <v-menu :value="shown" offset-y transition="slide-y-transition" v-if="i==0" 
              :close-on-content-click="false" 
              content-class="elevation-2">
                <template v-slot:activator="{ on, attrs }">
                  <v-btn class="ml-5 text-capitalize roboto fw500 f14" 
                    outlined 
                    small 
                    color="primary"
                    v-bind="attrs"
                    v-on="on"
                  >
                    <v-icon left>mdi-plus</v-icon>
                    Add
                  </v-btn>
                </template>
                <v-card >
                  <v-card-text>
                    <label class="secondary-2--text f12 poppins">Generate class code *</label>
                    <v-tooltip v-model="show" top >
                      <template v-slot:activator="{ on, attrs }">
                        <v-btn small icon v-bind="attrs" v-on="on" >
                          <v-icon color="grey lighten-1" small>
                            mdi-information
                          </v-icon>
                        </v-btn>
                      </template>
                      <span>This is an auto generated and cannot be customized.</span>
                    </v-tooltip>
                    <div class="d-flex">
                      <div class="flex-grow-1">
                        <v-text-field :error-messages="errs.class_code" 
                          dense 
                          outlined 
                          readonly 
                          class="general-custom-field roboto f14 secondary-1--text fw500"
                          v-model="form.class_code"
                        />
                      </div>
                      <div class="">
                        <v-btn color="primary" class="roboto fw500 f12" width="100" style="height: 40px;border-radius: 0px 5px 5px 0px; margin-left: -5px"
                        @click="random">Get Code</v-btn>
                      </div>
                    </div>
                    <label class="secondary-2--text f12 poppins">Create class name *</label>
                    <v-text-field :error-messages="errs.class_name" dense 
                      :hide-details="!errs.class_name"
                      outlined v-model="form.class_name"
                      class="general-custom-field roboto f14 secondary-1--text fw500"/>
                  </v-card-text>
                  <v-divider/>
                  <v-card-actions>
                    <v-btn v-if="!saving" color="primary" block class="text-capitalize" @click="generate">
                      Generate
                    </v-btn>
                    <v-btn v-else  color="primary" block>
                      <v-progress-circular
                      indeterminate
                      width="3"
                      size="22"
                      color="#fff"/>
                    </v-btn>
                  </v-card-actions>
                </v-card>
              </v-menu>
            </th>
          </tr>
        </thead>
        <tbody >
          <tr v-for="(item, i) in classes" :key="i" class="roboto f14 secondary-1--text">
            <td class="roboto fw500 secondary-1--text">{{item.class_name}}</td>
            <td class="roboto fw500 secondary-1--text text-center">{{item.class_code}}</td>
            <td class="roboto fw500 secondary-1--text text-center">{{item.registered_users}}</td>
            <td class="text-center">
              <v-btn text class="text-capitalize primary--text f12 fw600" @click="open(item)">
                <v-icon left>mdi-eye</v-icon>
                View Courses
              </v-btn>
            </td>
          </tr>
        </tbody>
      </template>
    </v-simple-table> -->
    <ClassCourseModalVue :modal="modal" @close="close" :selectedClass="selectedClass"/>
    <delete-toast 
      :dialog="deletedialog"
      type="Class"
      @cancel="deletedialog=false"
      @delete="deleteClass"
    />
  </section>
</template>

<script>
import { mapActions, mapState, mapMutations } from 'vuex';
import ClassCourseModalVue from './ClassCourseModal.vue';

export default {
  props: {
    classes: {
      type: Array,
      default: []
    },
    pageCount: {
      type: Number,
      default: 1
    },
    totalCount: {
      type: Number,
      default: 0
    },
    loading: {
      type: Boolean,
      default: false,
    },
    search: {
      type: String,
      default: ''
    },
    page: {
      type: Number,
      default: 1
    },
    paginate: {
      type: String,
      default: '10'
    },
  },
  components: {
    ClassCourseModalVue
  },
  data: () => ({
    headers: [
      {
        text: 'Class Name',
        value: 'class_name',
        align: 'start'
      },
      {
        text: 'Class Code',
        value: 'class_code',
        align: 'center'
      },
      {
        text: 'Registered Users',
        value: 'registered_users',
        align: 'center'
      },
      {
        text: 'Action',
        value: 'action',
        align: 'center'
      },
    ],
    show: false,
    form: { class_code: '', class_name: '' },
    chars: '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
    errs: [],
    shown: null,
    saving: false,
    modal: false,
    selectedClass: {
      class_code: '',
      courses: []
    },
    deletedialog: false,
  }),
  computed: {
    ...mapState({
      errors: (state) => state.errors
    }),

    itemsPerPage(){
      return Number(this.paginate)
    }
  },

  methods: {
    ...mapActions('admin', [
      'addClassAction',
      'deleteClassAction'
    ]),

    ...mapMutations(['alertMutation']),

    random() {
      this.form.class_code = this.randomGenerator(5, this.chars)
    },

    randomGenerator(length, chars) {
      let res = ''
      for (var i = length; i > 0; --i) res += chars[Math.round(Math.random() * (chars.length - 1))];
      return res;
    },

    generate() {
      this.errs = []
      if(this.$refs.form.validate()){
        this.saving=true
        this.addClassAction(this.form).then(() => {
          this.saving=false
          this.shown = false
          this.errs=[]
          this.form.class_code=''
          this.form.class_name=''
          this.$emit('save')

          setTimeout(() => {
            this.shown = null
          }, 1000);
        }).catch(() => {
          this.saving = false
          if(this.$errors()) return this.errs = this.errors
        })
      }
    },

    open(item) {
      this.selectedClass = item
      this.modal = true
    },
    
    close(){
      this.modal = false
    },

    deleteClass(){
      this.deletedialog = false
      this.deleteClassAction(this.selectedClass)
      .then(()=>{
        this.selectedClass = null
        this.alertMutation({
            show: true,
            text: 'Class successfully deleted',
            type: "success"
        })
        this.$emit('save')

      }).catch(() => {
        this.alertMutation({
            show: true,
            text: 'Something went wrong.',
            type: "error"
        })
      })
    }
  },
}
</script>