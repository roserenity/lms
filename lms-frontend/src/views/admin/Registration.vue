<template>
  <section id="registration" class="mx-1" v-if="!loading">
    
    <section class="d-flex align-center justify-end">
      <v-btn dense color="primary" outlined class="mb-3 text-capitalize f12 poppins ml-auto" @click="addPage">
        <v-icon left>mdi-plus</v-icon>
        Add Page
      </v-btn>
    </section>
    <v-expansion-panels flat accordion>
      <v-expansion-panel class="custom-border border my-2 py-2">
        <v-expansion-panel-header class="d-flex flex-wrap align-center">
          <div class="col-xl-6 col-lg-7 d-flex align-center justify-space-between">
            <div class="d-flex align-center">
              <div class="poppins f15 secondary-2--text fw600 mr-4">
                Page 1 - Account Info
              </div>
              <v-badge offset-x="12" content="6" class="mt-3" color="primary"/>
            </div>
          </div>
          <v-spacer/>
          <div class="d-flex align-center justify-end mx-1">
            <v-btn v-if="$vuetify.breakpoint.smAndUp" color="primary" dense outlined class="text-capitalize f12 poppins mx-2" @click.stop="()=>setClasses()">
              <v-icon left>mdi-eye-outline</v-icon>
              Preview
            </v-btn>
            <v-btn v-if="!$vuetify.breakpoint.smAndUp" icon color="primary" dense @click.stop="()=>setClasses()">
              <v-icon>mdi-eye-outline</v-icon>
            </v-btn>
            <v-tooltip bottom right color="transparent">
              <template v-slot:activator="{ on, attrs }">
                <v-icon color="secondary" v-bind="attrs" v-on="on" class="mx-2">mdi-information</v-icon>
              </template>
              <v-sheet class="custom-border pa-1">
                <span class="roboto f12">This page is default and uneditable.</span>
              </v-sheet>
            </v-tooltip>
          </div>
        </v-expansion-panel-header>
        <v-expansion-panel-content class="" eager >
          <v-card flat class="col-xl-6 col-lg-8">
            <v-card outlined class="px-2 mx-7 mb-5">
              <div class="col-6 mb-1">
                <FormTextFieldOptional
                    :label="'FIRST NAME *'"
                    :placeholder="'Text'"
                    :readonly="true"
                  />
              </div>
            </v-card>

            <v-card outlined class="px-2 mx-7 mb-5">
              <div class="col-6 mb-1">
                <FormTextFieldOptional
                    :label="'MIDDLE NAME'"
                    :placeholder="'Text'"
                    :readonly="true"
                  />
              </div>
            </v-card>

            <v-card outlined class="px-2 mx-7 mb-5">
              <div class="col-6 mb-1">
                <FormTextFieldOptional
                  :label="'LAST NAME *'"
                  :placeholder="'Text'"
                  :readonly="true"
                />
              </div>
            </v-card>

            <v-card outlined class="px-2 mx-7 mb-5">
              <div class="col-6 mb-1">
                <FormTextFieldOptional
                  :label="'SUFFIX (e.g. Jr. II, Sr.)'"
                  :placeholder="'Text'"
                  :readonly="true"
                />
              </div>
            </v-card>

            <!-- <v-card outlined class="px-2 mx-7 mb-5">  
              <div class="col-6 mb-1">
                <label class="poppins fw500 f12 secondary--text text-uppercase">* Class Category</label>
                <v-text-field 
                  placeholder="Text"
                  outlined
                  dense
                  readonly
                  class="general-custom-field roboto f14 fw500 secondary--text"
                  hide-details
                />
              </div>
            </v-card> -->

            <v-card outlined class="px-2 mx-7 mb-5">
              <div class="col-6 mb-1">
                <FormTextFieldOptional
                  :label="'EMAIL *'"
                  :placeholder="'Text'"
                  :readonly="true"
                />
              </div>
            </v-card>

            <v-card outlined class="px-2 mx-7 mb-5">
              <div class="col-6 mb-1">
                <FormTextFieldOptional
                  :label="'PASSWORD *'"
                  :placeholder="'Text'"
                  :readonly="true"
                />
              </div>
            </v-card>
          </v-card>
        </v-expansion-panel-content>
      </v-expansion-panel>
    </v-expansion-panels>
    <draggable v-model="tables" ghost-class="ghost"  @end="sortTables(tables)" class="sortable" 
        :disabled="!$vuetify.breakpoint.smAndUp" >
      <transition-group type="transition" name="flip-list" tag="div">
        <v-expansion-panels flat accordion v-for="(item, i) in tables" :key="item.id">
          <v-expansion-panel class="custom-border border my-2 py-2">
            <v-expansion-panel-header	class="d-flex flex-wrap align-center">
              <div class="d-flex align-center col-xl-6 col-lg-7">
                <div class="d-flex align-start">
                  <div class="poppins f16 secondary-2--text fw600 mr-4 text-capitalize">
                    Page {{i+2}} - {{item.title}}
                  </div>
                  <v-badge offset-x="12" :content="item.custom_registration_fields.length > 0 ? item.custom_registration_fields.length : '0'" class="mt-3" color="primary"/>
                </div>
              </div>
              <div class=" hidden-md-and-down d-flex justify-end align-center">
                <v-btn v-if="$vuetify.breakpoint.smAndUp" color="primary" dense outlined class="text-capitalize f12 poppins mx-2"  @click.stop="preview(item)">
                  <v-icon left>mdi-eye-outline</v-icon>
                  Preview
                </v-btn>
                <v-btn v-if="!$vuetify.breakpoint.smAndUp" icon color="primary" dense @click="preview(item)">
                  <v-icon>mdi-eye-outline</v-icon>
                </v-btn>

                <v-btn v-if="$vuetify.breakpoint.smAndUp" color="primary" dense outlined class="text-capitalize f12 poppins mx-2" @click="newField(item)">
                  <v-icon left>mdi-plus</v-icon>
                  Add field
                </v-btn>

                <v-btn v-if="!$vuetify.breakpoint.smAndUp" icon color="primary" dense @click="newField(item)">
                  <v-icon>mdi-plus</v-icon>
                </v-btn>

                <v-tooltip bottom right color="transparent">
                  <template v-slot:activator="{ on, attrs }">
                    <v-btn icon small v-bind="attrs" v-on="on" @click.stop="deleteTable(item.id)" class="mx-2">
                      <v-icon size="20" color="danger-1" >mdi-trash-can-outline</v-icon>
                    </v-btn>
                  </template>
                  <v-sheet class="custom-border pa-1">
                    <span class="roboto f12">Delete page</span>
                  </v-sheet>
                </v-tooltip>
                <v-icon size="20" color="secondary" class="mx-2" v-if="$vuetify.breakpoint.smAndUp" >
                  mdi-cursor-move
                </v-icon>
              </div>
            </v-expansion-panel-header>
            <v-expansion-panel-content class="" eager >
              <v-card flat class="col-md-6 col-xs-12 my-1" v-for="(column, i) in item.custom_registration_fields" :key="column.id" >
                <ExpansionContent :columns="item.custom_registration_fields" :column="column" :index="i" :table="item" @editField="editField" @deleteField="deleteField"/>
              </v-card>
            </v-expansion-panel-content>
          </v-expansion-panel>
        </v-expansion-panels>
      </transition-group>
    </draggable>
    <AddPage :dialog="add" @close="add=false" />
    <FieldDialog :type="type" :dialog="dialog" :table="table" @close="closeDialog"/>
    <Delete :dialog="remove" :data="itemToRemove" @close="remove=false"/>
    <PreviewDialog :modal="previewDialog" :item="itemToPreview" @close="previewDialog=false" />
  </section>
  <circular v-else class="ma-auto" />
</template>

<script>
import draggable from "vuedraggable";
import { mapActions, mapState } from 'vuex'
import Delete from '../../components/admin/registration/Delete.vue'
import AddPage from '../../components/admin/registration/AddPage.vue'
import FieldDialog from '../../components/admin/registration/FieldDialog.vue'
import PreviewDialog from '../../components/admin/registration/PreviewDialog.vue'
import ExpansionContent from '../../components/admin/registration/ExpansionContent.vue'

export default {
  components: {
    Delete,
    AddPage,
    draggable,
    FieldDialog,
    PreviewDialog,
    ExpansionContent,
},
  computed: {
    ...mapState('admin', {
      // tables: (state) => state.registration_tables,
      // columns:(state) => state.columns, 
    }),
    ...mapState('admin', {
      classes: (state) => state.classes
    }),
    tables: {
      get() {
        return this.$store.state["admin"].registration_tables;
      },
      set(val) {
        return this.$store.commit("admin/registrationTablesMutation", val);
      },
    }
  },
  data: () => ({
    add: false,
    dialog: false,
    table: [],
    loading: true,
    remove: false,
    itemToRemove: {
      id: '',
      type: ''
    },
    itemToPreview: {
      title: 'Untitled',
      custom_registration_fields: []
    },
    previewDialog: false,
    type: 'add',
    defaultFields: {
      title: 'Account Info',
      custom_registration_fields: [
        {
          "input_field_label": "First Name",
          "input_field_name": "first_name",
          "input_field_type": "text",
          "is_required": 1,
          "is_multiple": 0,
          "data": [],
        },
        {
          "input_field_label": "Middle Name",
          "input_field_name": "middle_name",
          "input_field_type": "text",
          "is_required": 0,
          "is_multiple": 0,
          "data": [],
        },
        {
          "input_field_label": "Last Name",
          "input_field_name": "last_name",
          "input_field_type": "text",
          "is_required": 1,
          "is_multiple": 0,
          "data": [],
        },
        {
          "input_field_label": "Suffix",
          "input_field_name": "suffix",
          "input_field_type": "text",
          "is_required": 0,
          "is_multiple": 0,
          "data": [],
        },
        // {
        //   "input_field_label": "Class Categories",
        //   "input_field_name": "class_categories",
        //   "input_field_type": "dropdown",
        //   "is_required": 1,
        //   "is_multiple": 0,
        //   "data": [],
        // },
        {
          "input_field_label": "Email",
          "input_field_name": "email",
          "input_field_type": "email",
          "is_required": 1,
          "is_multiple": 0,
          "data": [],
        },
        {
          "input_field_label": "Password",
          "input_field_name": "password",
          "input_field_type": "password",
          "is_required": 1,
          "is_multiple": 0,
          "data": [],
        },
      ]
    }
  }),
  created() {
    this.getRegistrationTablesAction().then(() => {
      this.loading=false
    })
  },
  methods: {
    ...mapActions('admin', [
      'getRegistrationTablesAction',
      'sortTableAction'
    ]),

    addPage() {
      this.add=true
    },

    newField(item) {
      this.type = 'add'
      this.dialog=true
      this.table=item
    },

    editField(item) {
      this.type = 'edit'
      this.table=item
      this.dialog=true
    },

    spread(item) {
      return [...{item}]
    },

	  sortTables(item) {
			// console.log(item);
      let _custom_registration_page_id = [];
      item.forEach(el => {
        _custom_registration_page_id.push(el.id)      
      });

      this.sortTableAction({
        custom_registration_page_id: _custom_registration_page_id
      })
		},

    deleteTable(id) {
      // console.log(id);
      this.itemToRemove.id=id
      this.itemToRemove.type='Page'
      this.remove=true
    },

    deleteField(e) {
      // console.log(e);
      this.itemToRemove.id=e
      this.itemToRemove.type='Field'
      this.remove=true
    },

    preview(item) {
      this.itemToPreview = item
      this.previewDialog = true
    },

    closeDialog(){
      this.dialog=false
    },

    setClasses(){
      let _classes = []
      this.classes.forEach(_class => {
        _classes.push(`${_class.class_code} - ${_class.class_name}`)
      })
      this.defaultFields.custom_registration_fields[2].data = _classes
      this.preview(this.defaultFields)
    }
  },
}
</script>
